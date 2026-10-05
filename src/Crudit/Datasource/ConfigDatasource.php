<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Crudit\Datasource;

use Doctrine\ORM\EntityManagerInterface;
use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Contracts\TenantInterface;
use Lle\CruditBundle\Datasource\AbstractDoctrineDatasource;
use Lle\CruditBundle\Datasource\DatasourceParams;
use Lle\CruditBundle\Filter\FilterState;
use Lle\ConfigBundle\Service\CacheManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ConfigDatasource extends AbstractDoctrineDatasource
{
    public function __construct(
        EntityManagerInterface $entityManager,
        FilterState $filterState,
        protected CacheManager $cache,
        protected ParameterBagInterface $parameterBag,
        protected ?TenantInterface $tenantService = null,
    ) {
        parent::__construct($entityManager, $filterState);
    }

    public function getClassName(): string
    {
        $metadata = $this->entityManager->getClassMetadata(ConfigInterface::class);

        return $metadata->name;
    }

    public function list(?DatasourceParams $requestParams): iterable
    {
        /** @var ConfigInterface[] $list */
        $list = parent::list($requestParams);

        $resultWithTenant = [];
        foreach ($list as $item) {
            if ($item->getTenantId()) {
                $resultWithTenant[] = $item->getLabel() . '-' . $item->getGroup();
            }
        }

        $result = [];
        foreach ($list as $item) {
            if ($item->getTenantId() || !in_array($item->getLabel() . '-' . $item->getGroup(), $resultWithTenant)) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @param ConfigInterface $resource
     */
    public function save(object $resource): bool
    {
        // an empty text field arrives as null: store an empty string ("0" is a value, not an empty field)
        if (in_array($resource->getValueType(), [ConfigInterface::PASSWORD, ConfigInterface::STRING]) && $resource->getValueString() === null) {
            $resource->setValueString('');
        }
        if (in_array($resource->getValueType(), [ConfigInterface::TEXT]) && $resource->getValueText() === null) {
            $resource->setValueText('');
        }
        // an empty number is ignored (0 is a value): reload the entity so that a later flush() cannot write it
        if (
            ($resource->getValueType() === ConfigInterface::INT && $resource->getValueInt() === null)
            || ($resource->getValueType() === ConfigInterface::FLOAT && $resource->getValueFloat() === null)
        ) {
            $this->entityManager->refresh($resource);

            return true;
        }

        /** @var bool $usingTenant */
        $usingTenant = $this->parameterBag->get('lle_config.using_tenant');
        if (!$usingTenant || ($this->tenantService && $resource->getTenantId() === $this->tenantService->getTenantId())) {
            parent::save($resource);

            /** @var ConfigInterface $resource */
            $this->cache->set($resource);
        } else {
            $configWithTenant = $this->entityManager->getRepository(ConfigInterface::class)->findOneBy([
                'label' => $resource->getLabel(),
                'group' => $resource->getGroup(),
                'tenantId' => $this->tenantService ? $this->tenantService->getTenantId() : null
            ]);

            if ($configWithTenant) {
                $configWithTenant
                    ->setValueType((string) $resource->getValueType())
                    ->setValueBool($resource->getValueBool())
                    ->setValueString($resource->getValueString())
                    ->setValueText($resource->getValueText())
                    ->setValueInt($resource->getValueInt())
                    ->setValueFloat($resource->getValueFloat());

                $this->entityManager->refresh($resource);

                parent::save($configWithTenant);

                /** @var ConfigInterface $resource */
                $this->cache->set($configWithTenant);
            } else {
                /** @var ConfigInterface $config */
                $config = $this->newInstance();
                $config
                    ->setLabel((string) $resource->getLabel())
                    ->setGroup((string) $resource->getGroup())
                    ->setValueType((string) $resource->getValueType())
                    ->setValueBool($resource->getValueBool())
                    ->setValueString($resource->getValueString())
                    ->setValueText($resource->getValueText())
                    ->setValueInt($resource->getValueInt())
                    ->setValueFloat($resource->getValueFloat())
                    ->setTri($resource->getTri())
                    ->setTenantId($this->tenantService?->getTenantId());

                $this->entityManager->refresh($resource);

                parent::save($config);

                /** @var ConfigInterface $resource */
                $this->cache->set($config);
            }
        }

        return true;
    }
}

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
        // input type password or string set null on empty field
        if (in_array($resource->getValueType(), [ConfigInterface::PASSWORD, ConfigInterface::STRING]) && !$resource->getValueString()) {
            $resource->setValueString('');
        }
        if (in_array($resource->getValueType(), [ConfigInterface::TEXT]) && !$resource->getValueText()) {
            $resource->setValueText('');
        }
        if (in_array($resource->getValueType(), [ConfigInterface::INT]) && !$resource->getValueInt()) {
            return true;
        }
        if (in_array($resource->getValueType(), [ConfigInterface::FLOAT]) && !$resource->getValueFloat()) {
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
                    ->setValueBool((bool) $resource->getValueBool())
                    ->setValueString((string) $resource->getValueString())
                    ->setValueText((string) $resource->getValueText())
                    ->setValueInt((int) $resource->getValueInt())
                    ->setValueFloat((float) $resource->getValueFloat());

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
                    ->setValueBool((bool) $resource->getValueBool())
                    ->setValueString((string) $resource->getValueString())
                    ->setValueText((string) $resource->getValueText())
                    ->setValueInt((int) $resource->getValueInt())
                    ->setValueFloat((float) $resource->getValueFloat())
                    ->setTri((int) $resource->getTri())
                    ->setTenantId((int) ($this->tenantService ? $this->tenantService->getTenantId() : null));

                $this->entityManager->refresh($resource);

                parent::save($config);

                /** @var ConfigInterface $resource */
                $this->cache->set($config);
            }
        }

        return true;
    }
}

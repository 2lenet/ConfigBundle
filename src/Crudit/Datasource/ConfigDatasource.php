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
        private CacheManager $cache,
        private ParameterBagInterface $parameterBag,
        private TenantInterface $tenantService,
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
    public function save(object $resource): void
    {
        /** @var bool $usingTenant */
        $usingTenant = $this->parameterBag->get('lle_config.using_tenant');
        if (!$usingTenant || ($usingTenant && $resource->getTenantId() === $this->tenantService->getTenantId())) {
            parent::save($resource);

            /** @var ConfigInterface $resource */
            $this->cache->set($resource);
        } else {
            $configWithTenant = $this->entityManager->getRepository(ConfigInterface::class)
                ->findConfigByTenant($resource, $this->tenantService->getTenantId());

            if ($configWithTenant) {
                $configWithTenant
                    ->setValueType($resource->getValueType())
                    ->setValueBool($resource->getValueBool())
                    ->setValueString($resource->getValueString())
                    ->setValueText($resource->getValueText())
                    ->setValueInt($resource->getValueInt());

                $this->entityManager->refresh($resource);

                parent::save($configWithTenant);

                /** @var ConfigInterface $resource */
                $this->cache->set($configWithTenant);
            } else {
                /** @var ConfigInterface $config */
                $config = $this->newInstance();
                $config
                    ->setLabel($resource->getLabel())
                    ->setGroup($resource->getGroup())
                    ->setValueType($resource->getValueType())
                    ->setValueBool($resource->getValueBool())
                    ->setValueString($resource->getValueString())
                    ->setValueText($resource->getValueText())
                    ->setValueInt($resource->getValueInt())
                    ->setTri($resource->getTri())
                    ->setTenantId($this->tenantService->getTenantId());

                $this->entityManager->refresh($resource);

                parent::save($config);

                /** @var ConfigInterface $resource */
                $this->cache->set($config);
            }
        }
    }
}

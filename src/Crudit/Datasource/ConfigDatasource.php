<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Crudit\Datasource;

use Doctrine\ORM\EntityManagerInterface;
use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Service\CacheManager;
use Lle\CruditBundle\Datasource\AbstractDoctrineDatasource;
use Lle\CruditBundle\Filter\FilterState;

class ConfigDatasource extends AbstractDoctrineDatasource
{
    public function __construct(
        EntityManagerInterface $entityManager,
        FilterState $filterState,
        private CacheManager $cache,
    ) {
        parent::__construct($entityManager, $filterState);
    }

    public function getClassName(): string
    {
        $metadata = $this->entityManager->getClassMetadata(ConfigInterface::class);

        return $metadata->name;
    }

    public function save(object $resource): void
    {
        parent::save($resource);

        /** @var ConfigInterface $resource */
        $this->cache->set($resource);
    }
}

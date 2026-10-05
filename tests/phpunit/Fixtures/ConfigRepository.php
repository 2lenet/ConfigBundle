<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Fixtures;

use Doctrine\Persistence\ManagerRegistry;
use Lle\ConfigBundle\Repository\AbstractConfigRepository;
use Lle\ConfigBundle\Service\CacheManager;

class ConfigRepository extends AbstractConfigRepository
{
    public function __construct(CacheManager $cache, ManagerRegistry $registry)
    {
        parent::__construct($cache, $registry, Config::class);
    }
}

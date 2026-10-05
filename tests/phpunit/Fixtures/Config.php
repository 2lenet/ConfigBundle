<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Fixtures;

use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Traits\ConfigTrait;

class Config implements ConfigInterface
{
    use ConfigTrait;

    public static function create(string $group, string $label, ?int $tenantId = null): self
    {
        $config = new self();
        $config
            ->setGroup($group)
            ->setLabel($label)
            ->setValueType(ConfigInterface::STRING)
            ->setTenantId($tenantId);

        return $config;
    }
}

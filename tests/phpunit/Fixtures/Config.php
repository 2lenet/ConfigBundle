<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Fixtures;

use Doctrine\ORM\Mapping as ORM;
use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Traits\ConfigTrait;

// no ORM\Table, as in the projects: the table is named by ConfigTableNameListener
#[ORM\Entity(repositoryClass: ConfigRepository::class)]
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

<?php

namespace Lle\ConfigBundle\Contracts;

use Lle\ConfigBundle\Repository\AbstractConfigRepository;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('lle.config.warmup')]
interface WarmupInterface
{
    public function warmup(AbstractConfigRepository $configRepository): void;
}

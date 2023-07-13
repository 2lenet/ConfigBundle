<?php

namespace Lle\ConfigBundle\Contracts;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('lle_config.add_config')]
interface AddConfigInterface
{
    public function getConfigs(): void;
}
<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Controller\Crudit;

use Lle\ConfigBundle\Crudit\Config\ConfigCrudConfig;
use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/config")
 */
class ConfigController extends AbstractCrudController
{
    use TraitCrudController;

    public function __construct(ConfigCrudConfig $config)
    {
        $this->config = $config;
    }
}

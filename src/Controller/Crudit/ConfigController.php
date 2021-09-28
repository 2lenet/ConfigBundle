<?php

declare(strict_types=1);

namespace App\Controller\Crudit;

use App\Crudit\Config\ConfigCrudConfig;
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

<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Controller\Crudit;

use Doctrine\ORM\EntityManagerInterface;
use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Crudit\Config\ConfigCrudConfig;
use Lle\ConfigBundle\Service\CacheManager;
use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/config')]
class ConfigController extends AbstractCrudController
{
    use TraitCrudController;

    public function __construct(
        ConfigCrudConfig $config,
        private CacheManager $cache,
        private EntityManagerInterface $em,
    ) {
        $this->config = $config;
    }

    #[Route('/refresh-cache')]
    public function refreshCache(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CONFIG_REFRESH_CACHE');
        
        $configs = $this->em->getRepository(ConfigInterface::class)
            ->findAll();
        foreach ($configs as $config) {
            $this->cache->set($config);
        }

        return $this->redirectToRoute('lle_config_crudit_config_index');
    }
}

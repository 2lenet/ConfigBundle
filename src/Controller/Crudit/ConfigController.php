<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Controller\Crudit;

use Lle\ConfigBundle\Crudit\Config\ConfigCrudConfig;
use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Controller\AbstractCrudController;
use Lle\CruditBundle\Controller\TraitCrudController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

 #[Route('/config')]
class ConfigController extends AbstractCrudController
{
    use TraitCrudController;

    public function __construct(ConfigCrudConfig $config, #[TaggedIterator('lle_config.add_config')] private iterable $configs)
    {
        $this->config = $config;
    }

    #[Route('/')]
    public function index(Request $request): Response
    {
        foreach ($this->configs as $configuration) {
            $configuration->getConfigs();
        }
        $this->denyAccessUnlessGranted('ROLE_' . $this->config->getName() . '_INDEX');

        $views = $this->getBrickBuilder()->build($this->config, CrudConfigInterface::INDEX);
        $response = $this->render('@LleCrudit/crud/index.html.twig', ['views' => $views]);

        return $this->getBrickResponseCollector()->handle($request, $response);
    }
}

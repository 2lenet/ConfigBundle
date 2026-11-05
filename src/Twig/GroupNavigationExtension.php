<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Twig;

use Lle\ConfigBundle\Service\GroupNavigation;
use Lle\ConfigBundle\Service\LabelFormatter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class GroupNavigationExtension extends AbstractExtension
{
    public function __construct(
        protected GroupNavigation $groupNavigation,
        protected LabelFormatter $labelFormatter,
        #[Autowire(param: 'lle_config.using_tenant')]
        protected bool $usingTenant,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('lle_config_groups', [$this->groupNavigation, 'getGroups']),
            new TwigFunction('lle_config_active_group', [$this->groupNavigation, 'getActiveGroup']),
            new TwigFunction('lle_config_group_configs', [$this->groupNavigation, 'filterByGroup']),
            new TwigFunction('lle_config_using_tenant', [$this, 'isUsingTenant']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('lle_config_humanize', [$this->labelFormatter, 'humanize']),
        ];
    }

    public function isUsingTenant(): bool
    {
        return $this->usingTenant;
    }
}

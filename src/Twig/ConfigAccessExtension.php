<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Twig;

use Lle\ConfigBundle\Security\ConfigAccessChecker;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Exposes the group roles decisions to the templates (no is_granted() in the templates).
 */
class ConfigAccessExtension extends AbstractExtension
{
    public function __construct(
        protected ConfigAccessChecker $accessChecker,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('lle_config_can_see_value', [$this->accessChecker, 'canSeeValue']),
            new TwigFunction('lle_config_can_edit_value', [$this->accessChecker, 'canEditValue']),
        ];
    }
}

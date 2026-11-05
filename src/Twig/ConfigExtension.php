<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Repository\AbstractConfigRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ConfigExtension extends AbstractExtension
{
    public function __construct(protected EntityManagerInterface $em)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_config_value', [$this, 'getConfigValue']),
        ];
    }

    /**
     * The default is converted to the requested type: a missing default is the empty value of that type.
     */
    public function getConfigValue(string $type, string $group, string $name, mixed $default = null): bool|string|int|null|float
    {
        /** @var AbstractConfigRepository $configRepository */
        $configRepository = $this->em->getRepository(ConfigInterface::class);

        return match ($type) {
            ConfigInterface::BOOL => $configRepository->getBool($group, $name, (bool) $default),
            ConfigInterface::STRING => $configRepository->getString($group, $name, (string) $default),
            ConfigInterface::TEXT => $configRepository->getText($group, $name, (string) $default),
            ConfigInterface::INT => $configRepository->getInt($group, $name, (int) $default),
            ConfigInterface::FLOAT => $configRepository->getFloat($group, $name, (float) $default),
            ConfigInterface::PASSWORD => $configRepository->getPassword($group, $name, (string) $default),
            default => null,
        };
    }
}

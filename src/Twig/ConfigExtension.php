<?php

namespace Lle\ConfigBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Repository\AbstractConfigRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ConfigExtension extends AbstractExtension
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_config_value', [$this, 'getConfigValue']),
        ];
    }

    public function getConfigValue(string $type, string $group, string $name, mixed $default = null): bool|string|int|null|float
    {
        /** @var AbstractConfigRepository $configRepository */
        $configRepository = $this->em->getRepository(ConfigInterface::class);

        switch ($type) {
            case ConfigInterface::BOOL:
                return $configRepository->getBool($group, $name, $default);
            case ConfigInterface::STRING:
                return $configRepository->getString($group, $name, $default);
            case ConfigInterface::TEXT:
                return $configRepository->getText($group, $name, $default);
            case ConfigInterface::INT:
                return $configRepository->getInt($group, $name, $default);
            case ConfigInterface::FLOAT:
                return $configRepository->getFloat($group, $name, $default);
            default:
                return null;
        }
    }
}

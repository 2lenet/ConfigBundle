<?php

namespace Lle\ConfigBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Lle\ConfigBundle\Contracts\ConfigInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ConfigExtension extends AbstractExtension
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_config_value', [$this, 'getConfigValue']),
        ];
    }

    public function getConfigValue(string $type, string $group, string $name, mixed $default): bool|string|int
    {
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
        }
    }
}

<?php

namespace Lle\ConfigBundle\Service;

use Lle\ConfigBundle\Contracts\ConfigInterface;
use Psr\Cache\CacheItemPoolInterface;

class CacheManager
{
    public function __construct(
        private CacheItemPoolInterface $cache,
    ) {
    }

    public function set(ConfigInterface $config): void
    {
        $cacheKey = sprintf(
            'lle_config_cache_%s_%s_%s',
            $config->getGroup(),
            $config->getLabel(),
            $config->getValueType(),
        );

        $item = $this->cache->getItem($cacheKey);

        switch ($config->getValueType()) {
            case ConfigInterface::INT:
                $value = $config->getValueInt();
                break;
            case ConfigInterface::BOOL:
                $value = $config->getValueBool();
                break;
            case ConfigInterface::TEXT:
                $value = $config->getValueText();
                break;
            case ConfigInterface::STRING:
                $value = $config->getValueString();
                break;
            default:
                $value = null;
        }

        if ($value !== null) {
            $item->set($value);
            $this->cache->save($item);
        } else {
            $this->cache->deleteItem($item);
        }
    }

    public function get(string $group, string $label, string $valueType): int|string|bool|null
    {
        $cacheKey = sprintf(
            'lle_config_cache_%s_%s_%s',
            $group,
            $label,
            $valueType,
        );

        $item = $this->cache->getItem($cacheKey);
        if ($item->isHit()) {
            return $item->get();
        }

        return null;
    }
}

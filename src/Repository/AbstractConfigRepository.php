<?php

namespace Lle\ConfigBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lle\ConfigBundle\Contracts\ConfigInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @method ConfigInterface|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConfigInterface|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConfigInterface[]    findAll()
 * @method ConfigInterface[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
abstract class AbstractConfigRepository extends ServiceEntityRepository
{
    private CacheItemPoolInterface $cache;

    public function __construct(ManagerRegistry $registry, $entityClass = ConfigInterface::class)
    {
        parent::__construct($registry, $entityClass);
    }

    public function getBool(string $group, string $label, bool $default): bool
    {
        $cached = $this->getCached($group, $label, ConfigInterface::BOOL);
        if ($cached) {
            return $cached;
        }

        $item = $this->findOneBy(["group" => $group, "label" => $label]);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::BOOL);
            $item->setValueBool($default);
            $this->_em->persist($item);
            $this->_em->flush();

            $this->updateCache($item, ConfigInterface::BOOL);
        }

        return $item->getValueBool();
    }

    public function setBool(string $group, string $label, bool $value): void
    {
        $item = $this->findOneBy(["group" => $group, "label" => $label]);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::BOOL);
            $this->_em->persist($item);
        }
        $item->setValueBool($value);
        $this->_em->flush();

        $this->updateCache($item, ConfigInterface::BOOL);
    }

    public function getString(string $group, string $label, string $default): string
    {
        $cached = $this->getCached($group, $label, ConfigInterface::STRING);
        if ($cached) {
            return $cached;
        }

        $item = $this->findOneBy(["group" => $group, "label" => $label]);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::STRING);
            $item->setValueString($default);
            $this->_em->persist($item);
            $this->_em->flush();

            $this->updateCache($item, ConfigInterface::STRING);
        }

        return $item->getValueString();
    }

    public function setString(string $group, string $label, string $value): void
    {
        $item = $this->findOneBy(["group" => $group, "label" => $label]);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::STRING);
            $this->_em->persist($item);
        }
        $item->setValueString($value);
        $this->_em->flush();

        $this->updateCache($item, ConfigInterface::STRING);
    }

    public function getText(string $group, string $label, string $default): string
    {
        $cached = $this->getCached($group, $label, ConfigInterface::TEXT);
        if ($cached) {
            return $cached;
        }

        $item = $this->findOneBy(["group" => $group, "label" => $label]);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::TEXT);
            $item->setValueText($default);
            $this->_em->persist($item);
            $this->_em->flush();

            $this->updateCache($item, ConfigInterface::TEXT);
        }

        return $item->getValueText();
    }

    public function setText(string $group, string $label, string $value): void
    {
        $item = $this->findOneBy(["group" => $group, "label" => $label]);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::TEXT);
            $this->_em->persist($item);
        }
        $item->setValueText($value);
        $this->_em->flush();

        $this->updateCache($item, ConfigInterface::TEXT);
    }

    public function getInt(string $group, string $label, int $default): int
    {
        $cached = $this->getCached($group, $label, ConfigInterface::INT);
        if ($cached) {
            return $cached;
        }

        $item = $this->findOneBy(["group" => $group, "label" => $label]);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::INT);
            $item->setValueInt($default);
            $this->_em->persist($item);
            $this->_em->flush();

            $this->updateCache($item, ConfigInterface::INT);
        }

        return $item->getValueInt();
    }

    public function setInt(string $group, string $label, int $value): void
    {
        $item = $this->findOneBy(["group" => $group, "label" => $label]);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::INT);
            $this->_em->persist($item);
        }
        $item->setValueInt($value);
        $this->_em->flush();

        $this->updateCache($item, ConfigInterface::INT);
    }

    private function createConfig(string $group, string $label, string $valueType): ConfigInterface
    {
        $configClass = $this->_em->getClassMetadata(ConfigInterface::class)->getName();
        /** @var ConfigInterface $item */
        $item = new $configClass();
        $item->setGroup($group);
        $item->setLabel($label);
        $item->setValueType($valueType);

        return $item;
    }

    private function updateCache(ConfigInterface $config, string $valueType): void
    {
        $cacheKey = sprintf(
            'lle_config_cache_%s_%s_%s',
            $config->getGroup(),
            $config->getLabel(),
            $valueType,
        );

        $item = $this->cache->getItem($cacheKey);
        $item->set($config->getValue());
        $this->cache->save($item);
    }

    private function getCached(string $group, string $label, string $valueType): int|string|bool|null
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

    #[Required]
    public function setCache(CacheItemPoolInterface $cache): void
    {
        $this->cache = $cache;
    }
}

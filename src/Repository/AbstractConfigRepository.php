<?php

namespace Lle\ConfigBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Service\CacheManager;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @method ConfigInterface|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConfigInterface|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConfigInterface[]    findAll()
 * @method ConfigInterface[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
abstract class AbstractConfigRepository extends ServiceEntityRepository
{
    private CacheManager $cache;

    public function __construct(ManagerRegistry $registry, $entityClass = ConfigInterface::class)
    {
        parent::__construct($registry, $entityClass);
    }

    public function getBool(string $group, string $label, bool $default): bool
    {
        $cached = $this->cache->get($group, $label, ConfigInterface::BOOL);
        if ($cached !== null) {
            return $cached;
        }

        $item = $this->findOneBy(["group" => $group, "label" => $label]);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::BOOL);
            $item->setValueBool($default);
            $this->_em->persist($item);
            $this->_em->flush();
        }

        $this->cache->set($item);

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

        $this->cache->set($item);
    }

    public function getString(string $group, string $label, string $default): string
    {
        $cached = $this->cache->get($group, $label, ConfigInterface::STRING);
        if ($cached !== null) {
            return $cached;
        }

        $item = $this->findOneBy(["group" => $group, "label" => $label]);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::STRING);
            $item->setValueString($default);
            $this->_em->persist($item);
            $this->_em->flush();
        }

        $this->cache->set($item);

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

        $this->cache->set($item);
    }

    public function getText(string $group, string $label, string $default): string
    {
        $cached = $this->cache->get($group, $label, ConfigInterface::TEXT);
        if ($cached !== null) {
            return $cached;
        }

        $item = $this->findOneBy(["group" => $group, "label" => $label]);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::TEXT);
            $item->setValueText($default);
            $this->_em->persist($item);
            $this->_em->flush();
        }

        $this->cache->set($item);

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

        $this->cache->set($item);
    }

    public function getInt(string $group, string $label, int $default): int
    {
        $cached = $this->cache->get($group, $label, ConfigInterface::INT);
        if ($cached !== null) {
            return $cached;
        }

        $item = $this->findOneBy(["group" => $group, "label" => $label]);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::INT);
            $item->setValueInt($default);
            $this->_em->persist($item);
            $this->_em->flush();
        }

        $this->cache->set($item);

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

        $this->cache->set($item);
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

    #[Required]
    public function setCache(CacheManager $cache): void
    {
        $this->cache = $cache;
    }
}

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
    protected CacheManager $cache;

    public function __construct(CacheManager $cache, ManagerRegistry $registry, $entityClass = ConfigInterface::class)
    {
        $this->cache = $cache;
        parent::__construct($registry, $entityClass);
    }

    public function getBool(string $group, string $label, bool $default, ?int $tenantId = null): bool
    {
        $cached = $this->cache->get($group, $label, ConfigInterface::BOOL, $tenantId);
        if ($cached !== null) {
            return $cached;
        }

        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::BOOL, $tenantId);
            $item->setValueBool($default);
            $this->_em->persist($item);
            $this->_em->flush();
        }

        $this->cache->set($item);

        return $item->getValueBool();
    }

    public function setBool(string $group, string $label, bool $value, ?int $tenantId = null): void
    {
        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::BOOL, $tenantId);
            $this->_em->persist($item);
        }
        $item->setValueBool($value);
        $this->_em->flush();

        $this->cache->set($item);
    }

    public function initBool(string $group, string $label, bool $default, ?int $tenantId = null): bool
    {
        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::BOOL, $tenantId);
            $item->setValueBool($default);
            $this->_em->persist($item);
            $this->_em->flush();
        }

        return $item->getValueBool();
    }

    public function getString(string $group, string $label, string $default, ?int $tenantId = null): string
    {
        $cached = $this->cache->get($group, $label, ConfigInterface::STRING, $tenantId);
        if ($cached !== null) {
            return $cached;
        }

        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::STRING, $tenantId);
            $item->setValueString($default);
            $this->_em->persist($item);
            $this->_em->flush();
        }

        $this->cache->set($item);

        return $item->getValueString();
    }

    public function setString(string $group, string $label, string $value, ?int $tenantId = null): void
    {
        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::STRING, $tenantId);
            $this->_em->persist($item);
        }
        $item->setValueString($value);
        $this->_em->flush();

        $this->cache->set($item);
    }

    public function initString(string $group, string $label, string $default, ?int $tenantId = null): string
    {
        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::STRING, $tenantId);
            $item->setValueString($default);
            $this->_em->persist($item);
            $this->_em->flush();
        }

        return $item->getValueString();
    }

    public function getText(string $group, string $label, string $default, ?int $tenantId = null): string
    {
        $cached = $this->cache->get($group, $label, ConfigInterface::TEXT, $tenantId);
        if ($cached !== null) {
            return $cached;
        }

        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::TEXT, $tenantId);
            $item->setValueText($default);
            $this->_em->persist($item);
            $this->_em->flush();
        }

        $this->cache->set($item);

        return $item->getValueText();
    }

    public function setText(string $group, string $label, string $value, ?int $tenantId = null): void
    {
        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::TEXT, $tenantId);
            $this->_em->persist($item);
        }
        $item->setValueText($value);
        $this->_em->flush();

        $this->cache->set($item);
    }

    public function initText(string $group, string $label, string $default, ?int $tenantId = null): string
    {
        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::TEXT, $tenantId);
            $item->setValueText($default);
            $this->_em->persist($item);
            $this->_em->flush();
        }

        return $item->getValueText();
    }

    public function getInt(string $group, string $label, int $default, ?int $tenantId = null): int
    {
        $cached = $this->cache->get($group, $label, ConfigInterface::INT, $tenantId);
        if ($cached !== null) {
            return $cached;
        }

        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::INT, $tenantId);
            $item->setValueInt($default);
            $this->_em->persist($item);
            $this->_em->flush();
        }

        $this->cache->set($item);

        return $item->getValueInt();
    }

    public function setInt(string $group, string $label, int $value, ?int $tenantId = null): void
    {
        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::INT, $tenantId);
            $this->_em->persist($item);
        }
        $item->setValueInt($value);
        $this->_em->flush();

        $this->cache->set($item);
    }

    public function initInt(string $group, string $label, int $default, ?int $tenantId = null): int
    {
        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::INT, $tenantId);
            $item->setValueInt($default);
            $this->_em->persist($item);
            $this->_em->flush();
        }

        return $item->getValueInt();
    }

    public function getPassword(string $group, string $label, string $default, ?int $tenantId = null): string
    {
        $cached = $this->cache->get($group, $label, ConfigInterface::STRING, $tenantId);
        if ($cached !== null) {
            return $cached;
        }

        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::PASSWORD, $tenantId);
            $item->setValueString($default);
            $this->_em->persist($item);
            $this->_em->flush();
        }

        $this->cache->set($item);

        return $item->getValueString();
    }

    public function setPassword(string $group, string $label, string $value, ?int $tenantId = null): void
    {
        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::PASSWORD, $tenantId);
            $this->_em->persist($item);
        }
        $item->setValueString($value);
        $this->_em->flush();

        $this->cache->set($item);
    }

    private function createConfig(string $group, string $label, string $valueType, ?int $tenantId = null): ConfigInterface
    {
        $configClass = $this->_em->getClassMetadata(ConfigInterface::class)->getName();

        /** @var ConfigInterface $item */
        $item = new $configClass();
        $item
            ->setGroup($group)
            ->setLabel($label)
            ->setValueType($valueType)
            ->setTenantId($tenantId);

        return $item;
    }

    #[Required]
    public function setCache(CacheManager $cache): void
    {
        $this->cache = $cache;
    }
}

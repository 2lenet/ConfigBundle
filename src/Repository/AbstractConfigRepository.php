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
            return (bool) $cached;
        }

        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::BOOL, $tenantId);
            $item->setValueBool($default);
            $this->getEntityManager()->persist($item);
            $this->getEntityManager()->flush();
        }

        $this->cache->set($item);

        return $item->getValueBool() ?? $default;
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
            $this->getEntityManager()->persist($item);
        }
        $item->setValueBool($value);
        $this->getEntityManager()->flush();

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
            $this->getEntityManager()->persist($item);
            $this->getEntityManager()->flush();
        }

        return $item->getValueBool() ?? $default;
    }

    public function getString(string $group, string $label, string $default, ?int $tenantId = null): string
    {
        $cached = $this->cache->get($group, $label, ConfigInterface::STRING, $tenantId);
        if ($cached !== null) {
            return (string) $cached;
        }

        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::STRING, $tenantId);
            $item->setValueString($default);
            $this->getEntityManager()->persist($item);
            $this->getEntityManager()->flush();
        }

        $this->cache->set($item);

        return $item->getValueString() ?? $default;
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
            $this->getEntityManager()->persist($item);
        }
        $item->setValueString($value);
        $this->getEntityManager()->flush();

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
            $this->getEntityManager()->persist($item);
            $this->getEntityManager()->flush();
        }

        return $item->getValueString() ?? $default;
    }

    public function getText(string $group, string $label, string $default, ?int $tenantId = null): string
    {
        $cached = $this->cache->get($group, $label, ConfigInterface::TEXT, $tenantId);
        if ($cached !== null) {
            return (string) $cached;
        }

        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::TEXT, $tenantId);
            $item->setValueText($default);
            $this->getEntityManager()->persist($item);
            $this->getEntityManager()->flush();
        }

        $this->cache->set($item);

        return $item->getValueText() ?? $default;
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
            $this->getEntityManager()->persist($item);
        }
        $item->setValueText($value);
        $this->getEntityManager()->flush();

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
            $this->getEntityManager()->persist($item);
            $this->getEntityManager()->flush();
        }

        return $item->getValueText() ?? $default;
    }

    public function getInt(string $group, string $label, int $default, ?int $tenantId = null): int
    {
        $cached = $this->cache->get($group, $label, ConfigInterface::INT, $tenantId);
        if ($cached !== null) {
            return (int) $cached;
        }

        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::INT, $tenantId);
            $item->setValueInt($default);
            $this->getEntityManager()->persist($item);
            $this->getEntityManager()->flush();
        }

        $this->cache->set($item);

        return $item->getValueInt() ?? $default;
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
            $this->getEntityManager()->persist($item);
        }
        $item->setValueInt($value);
        $this->getEntityManager()->flush();

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
            $this->getEntityManager()->persist($item);
            $this->getEntityManager()->flush();
        }

        return $item->getValueInt() ?? $default;
    }

    public function getPassword(string $group, string $label, string $default, ?int $tenantId = null): string
    {
        $cached = $this->cache->get($group, $label, ConfigInterface::STRING, $tenantId);
        if ($cached !== null) {
            return (string) $cached;
        }

        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::PASSWORD, $tenantId);
            $item->setValueString($default);
            $this->getEntityManager()->persist($item);
            $this->getEntityManager()->flush();
        }

        $this->cache->set($item);

        return $item->getValueString() ?? $default;
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
            $this->getEntityManager()->persist($item);
        }
        $item->setValueString($value);
        $this->getEntityManager()->flush();

        $this->cache->set($item);
    }

    public function initPassword(string $group, string $label, string $default, ?int $tenantId = null): string
    {
        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::PASSWORD, $tenantId);
            $item->setValueString($default);
            $this->getEntityManager()->persist($item);
            $this->getEntityManager()->flush();
        }

        return $item->getValueString() ?? $default;
    }

    public function getFloat(string $group, string $label, float $default=0, ?int $tenantId = null): float
    {
        $cached = $this->cache->get($group, $label, ConfigInterface::FLOAT, $tenantId);
        if ($cached !== null) {
            return (float) $cached;
        }

        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::FLOAT, $tenantId);
            $item->setValueFloat($default);
            $this->getEntityManager()->persist($item);
            $this->getEntityManager()->flush();
        }

        $this->cache->set($item);

        return $item->getValueFloat() ?? $default;
    }

    public function setFloat(string $group, string $label, float $value, ?int $tenantId = null): void
    {
        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::FLOAT, $tenantId);
            $this->getEntityManager()->persist($item);
        }
        $item->setValueFloat($value);
        $this->getEntityManager()->flush();

        $this->cache->set($item);
    }

    public function initFloat(string $group, string $label, float $default, ?int $tenantId = null): float
    {
        if (!$tenantId) {
            $item = $this->findOneBy(['group' => $group, 'label' => $label]);
        } else {
            $item = $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId]);
        }
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::FLOAT, $tenantId);
            $item->setValueFloat($default);
            $this->getEntityManager()->persist($item);
            $this->getEntityManager()->flush();
        }

        return $item->getValueFloat() ?? $default;
    }

    private function createConfig(string $group, string $label, string $valueType, ?int $tenantId = null): ConfigInterface
    {
        $configClass = $this->getEntityManager()->getClassMetadata(ConfigInterface::class)->getName();

        /** @var ConfigInterface $item */
        $item = new $configClass();
        $item
            ->setGroup($group)
            ->setLabel($label)
            ->setValueType($valueType)
            ->setTenantId((int) $tenantId);

        return $item;
    }

    #[Required]
    public function setCache(CacheManager $cache): void
    {
        $this->cache = $cache;
    }
}

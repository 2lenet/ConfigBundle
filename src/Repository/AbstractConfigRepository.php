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

        $item = $this->findConfig($group, $label, $tenantId);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::BOOL, $tenantId);
            $item->setValueBool($default);
            $item = $this->insertConfig($item);
        }

        $this->cache->set($item);

        return $item->getValueBool() ?? $default;
    }

    public function setBool(string $group, string $label, bool $value, ?int $tenantId = null): void
    {
        $item = $this->findConfig($group, $label, $tenantId);
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
        $item = $this->findConfig($group, $label, $tenantId);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::BOOL, $tenantId);
            $item->setValueBool($default);
            $item = $this->insertConfig($item);
        }

        return $item->getValueBool() ?? $default;
    }

    public function getString(string $group, string $label, string $default, ?int $tenantId = null): string
    {
        $cached = $this->cache->get($group, $label, ConfigInterface::STRING, $tenantId);
        if ($cached !== null) {
            return (string) $cached;
        }

        $item = $this->findConfig($group, $label, $tenantId);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::STRING, $tenantId);
            $item->setValueString($default);
            $item = $this->insertConfig($item);
        }

        $this->cache->set($item);

        return $item->getValueString() ?? $default;
    }

    public function setString(string $group, string $label, string $value, ?int $tenantId = null): void
    {
        $item = $this->findConfig($group, $label, $tenantId);
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
        $item = $this->findConfig($group, $label, $tenantId);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::STRING, $tenantId);
            $item->setValueString($default);
            $item = $this->insertConfig($item);
        }

        return $item->getValueString() ?? $default;
    }

    public function getText(string $group, string $label, string $default, ?int $tenantId = null): string
    {
        $cached = $this->cache->get($group, $label, ConfigInterface::TEXT, $tenantId);
        if ($cached !== null) {
            return (string) $cached;
        }

        $item = $this->findConfig($group, $label, $tenantId);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::TEXT, $tenantId);
            $item->setValueText($default);
            $item = $this->insertConfig($item);
        }

        $this->cache->set($item);

        return $item->getValueText() ?? $default;
    }

    public function setText(string $group, string $label, string $value, ?int $tenantId = null): void
    {
        $item = $this->findConfig($group, $label, $tenantId);
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
        $item = $this->findConfig($group, $label, $tenantId);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::TEXT, $tenantId);
            $item->setValueText($default);
            $item = $this->insertConfig($item);
        }

        return $item->getValueText() ?? $default;
    }

    public function getInt(string $group, string $label, int $default, ?int $tenantId = null): int
    {
        $cached = $this->cache->get($group, $label, ConfigInterface::INT, $tenantId);
        if ($cached !== null) {
            return (int) $cached;
        }

        $item = $this->findConfig($group, $label, $tenantId);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::INT, $tenantId);
            $item->setValueInt($default);
            $item = $this->insertConfig($item);
        }

        $this->cache->set($item);

        return $item->getValueInt() ?? $default;
    }

    public function setInt(string $group, string $label, int $value, ?int $tenantId = null): void
    {
        $item = $this->findConfig($group, $label, $tenantId);
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
        $item = $this->findConfig($group, $label, $tenantId);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::INT, $tenantId);
            $item->setValueInt($default);
            $item = $this->insertConfig($item);
        }

        return $item->getValueInt() ?? $default;
    }

    public function getPassword(string $group, string $label, string $default, ?int $tenantId = null): string
    {
        $cached = $this->cache->get($group, $label, ConfigInterface::STRING, $tenantId);
        if ($cached !== null) {
            return (string) $cached;
        }

        $item = $this->findConfig($group, $label, $tenantId);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::PASSWORD, $tenantId);
            $item->setValueString($default);
            $item = $this->insertConfig($item);
        }

        $this->cache->set($item);

        return $item->getValueString() ?? $default;
    }

    public function setPassword(string $group, string $label, string $value, ?int $tenantId = null): void
    {
        $item = $this->findConfig($group, $label, $tenantId);
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
        $item = $this->findConfig($group, $label, $tenantId);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::PASSWORD, $tenantId);
            $item->setValueString($default);
            $item = $this->insertConfig($item);
        }

        return $item->getValueString() ?? $default;
    }

    public function getFloat(string $group, string $label, float $default=0, ?int $tenantId = null): float
    {
        $cached = $this->cache->get($group, $label, ConfigInterface::FLOAT, $tenantId);
        if ($cached !== null) {
            return (float) $cached;
        }

        $item = $this->findConfig($group, $label, $tenantId);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::FLOAT, $tenantId);
            $item->setValueFloat($default);
            $item = $this->insertConfig($item);
        }

        $this->cache->set($item);

        return $item->getValueFloat() ?? $default;
    }

    public function setFloat(string $group, string $label, float $value, ?int $tenantId = null): void
    {
        $item = $this->findConfig($group, $label, $tenantId);
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
        $item = $this->findConfig($group, $label, $tenantId);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::FLOAT, $tenantId);
            $item->setValueFloat($default);
            $item = $this->insertConfig($item);
        }

        return $item->getValueFloat() ?? $default;
    }

    /**
     * Without a tenant, only the global row (tenantId NULL) matches: never another tenant's value.
     */
    protected function findConfig(string $group, string $label, ?int $tenantId): ?ConfigInterface
    {
        return $this->findOneBy(['group' => $group, 'label' => $label, 'tenantId' => $tenantId ?: null]);
    }

    /**
     * Stores a missing key with a plain INSERT on its table: a read never flushes the EntityManager,
     * so the pending changes of the caller are neither saved nor lost.
     */
    protected function insertConfig(ConfigInterface $config): ConfigInterface
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();
        $quoteStrategy = $entityManager->getConfiguration()->getQuoteStrategy();
        $metadata = $entityManager->getClassMetadata($config::class);

        $data = [];
        $types = [];
        foreach ($metadata->getFieldNames() as $fieldName) {
            if ($metadata->isIdentifier($fieldName)) {
                continue;
            }

            $column = $quoteStrategy->getColumnName($fieldName, $metadata, $platform);
            $data[$column] = $metadata->getFieldValue($config, $fieldName);
            $type = $metadata->getTypeOfField($fieldName);
            if ($type !== null) {
                $types[$column] = $type;
            }
        }

        $connection->insert($quoteStrategy->getTableName($metadata, $platform), $data, $types);

        return $this->findConfig((string) $config->getGroup(), (string) $config->getLabel(), $config->getTenantId()) ?? $config;
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
            ->setTenantId($tenantId);

        return $item;
    }

    #[Required]
    public function setCache(CacheManager $cache): void
    {
        $this->cache = $cache;
    }
}

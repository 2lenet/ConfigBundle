<?php

namespace Lle\ConfigBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lle\ConfigBundle\Contracts\ConfigInterface;

/**
 * @method ConfigInterface|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConfigInterface|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConfigInterface[]    findAll()
 * @method ConfigInterface[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
abstract class AbstractConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, $entityClass = ConfigInterface::class)
    {
        parent::__construct($registry, $entityClass);
    }

    public function getBool(string $group, string $label, bool $default): bool
    {
        $item = $this->findOneBy(["group" => $group, "label" => $label]);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::BOOL);
            $item->setValueBool($default);
            $this->_em->persist($item);
            $this->_em->flush();
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
    }

    public function getString(string $group, string $label, string $default): string
    {
        $item = $this->findOneBy(["group" => $group, "label" => $label]);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::STRING);
            $item->setValueString($default);
            $this->_em->persist($item);
            $this->_em->flush();
        }

        return $item->getValueString();
    }

    public function setString(string $group, string $label, string $value): void
    {
        $item = $this->findOneBy(["group" => $group, "label" => $label]);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::STRING);
            $this->_em->persist($item);
            $this->_em->flush();
        }
        $item->setValueString($value);
    }

    public function getText(string $group, string $label, string $default): string
    {
        $item = $this->findOneBy(["group" => $group, "label" => $label]);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::TEXT);
            $item->setValueText($default);
            $this->_em->persist($item);
            $this->_em->flush();
        }

        return $item->getValueText();
    }

    public function setText(string $group, string $label, string $value): void
    {
        $item = $this->findOneBy(["group" => $group, "label" => $label]);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::TEXT);
            $this->_em->persist($item);
            $this->_em->flush();
        }
        $item->setValueText($value);
    }

    public function getInt(string $group, string $label, int $default): int
    {
        $item = $this->findOneBy(["group" => $group, "label" => $label]);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::INT);
            $item->setValueInt($default);
            $this->_em->persist($item);
            $this->_em->flush();
        }

        return $item->getValueInt();
    }

    public function setInt(string $group, string $label, int $value): void
    {
        $item = $this->findOneBy(["group" => $group, "label" => $label]);
        if (!$item) {
            $item = $this->createConfig($group, $label, ConfigInterface::INT);
            $this->_em->persist($item);
            $this->_em->flush();
        }
        $item->setValueInt($value);
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
}

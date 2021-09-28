<?php

namespace App\Repository;

use App\Entity\Config;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Config|null find($id, $lockMode = null, $lockVersion = null)
 * @method Config|null findOneBy(array $criteria, array $orderBy = null)
 * @method Config[]    findAll()
 * @method Config[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Config::class);
    }

    public function getBool($group, $code, bool $default): bool
    {
        $item = $this->findOneBy(["groupe" => $group, "libelle" => $code]);
        if (!$item) {
            $item = new Config();
            $item->setGroupe($group);
            $item->setLibelle($code);
            $item->setTri(0);
            $item->setValueBool($default);
            $item->setValueType('Bool');
            $this->_em->persist($item);
            $this->_em->flush();
        }
        return $item->getValueBool();
    }

    public function getString($group, $code, string $default): string
    {
        $item = $this->findOneBy(["groupe" => $group, "libelle" => $code]);
        if (!$item) {
            $item = new Config();
            $item->setGroupe($group);
            $item->setLibelle($code);
            $item->setTri(0);
            $item->setValueString($default);
            $item->setValueType('String');
            $this->_em->persist($item);
            $this->_em->flush();
        }
        return $item->getValueString();
    }

    public function setString($group, $code, string $value): void
    {
        $item = $this->findOneBy(["groupe" => $group, "libelle" => $code]);
        if (!$item) {
            $item = new Config();
            $item->setGroupe($group);
            $item->setLibelle($code);
            $item->setTri(0);
            $item->setValueType('String');
            $this->_em->persist($item);
            $this->_em->flush();
        }
        $item->setValueString($value);
    }
}

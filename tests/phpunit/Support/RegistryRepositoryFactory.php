<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Support;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Repository\RepositoryFactory;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;

/**
 * Builds the service repositories (constructor taking the ManagerRegistry) the way Symfony does,
 * so that third-party code calling $entityManager->getRepository() works without a container.
 */
class RegistryRepositoryFactory implements RepositoryFactory
{
    /** @var array<string, ObjectRepository<object>> */
    protected array $repositories = [];

    public function __construct(
        protected ManagerRegistry $registry,
    ) {
    }

    /**
     * Repository built by the test itself (other constructor arguments than the registry).
     *
     * @param class-string $entityName
     * @param ObjectRepository<object> $repository
     */
    public function setRepository(string $entityName, ObjectRepository $repository): void
    {
        $this->repositories[$entityName] = $repository;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $entityName
     *
     * @return EntityRepository<T>
     */
    public function getRepository(EntityManagerInterface $entityManager, string $entityName): EntityRepository
    {
        $metadata = $entityManager->getClassMetadata($entityName);
        $repositoryClass = $metadata->customRepositoryClassName ?? EntityRepository::class;

        if (!isset($this->repositories[$metadata->name])) {
            $this->repositories[$metadata->name] = is_a($repositoryClass, ServiceEntityRepositoryInterface::class, true)
                ? $this->createServiceRepository($repositoryClass)
                : new $repositoryClass($entityManager, $metadata);
        }

        /** @var EntityRepository<T> */
        return $this->repositories[$metadata->name];
    }

    /**
     * @param class-string $repositoryClass a ServiceEntityRepository, built with the registry only
     *
     * @return ObjectRepository<object>
     */
    protected function createServiceRepository(string $repositoryClass): ObjectRepository
    {
        /** @var ObjectRepository<object> */
        return (new \ReflectionClass($repositoryClass))->newInstance($this->registry);
    }
}

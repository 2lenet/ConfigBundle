<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Crudit\Datasource;

use Doctrine\ORM\EntityManagerInterface;
use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Contracts\TenantInterface;
use Lle\ConfigBundle\Security\ConfigAccessChecker;
use Lle\ConfigBundle\Service\ValueFieldResolver;
use Lle\CruditBundle\Datasource\AbstractDoctrineDatasource;
use Lle\CruditBundle\Datasource\DatasourceParams;
use Lle\CruditBundle\Filter\FilterState;
use Lle\ConfigBundle\Service\CacheManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ConfigDatasource extends AbstractDoctrineDatasource
{
    public const VALUE_TYPE_FIELD = 'valueType';
    public const ACCESS_DENIED_MESSAGE = 'Configuration change not allowed for this group.';

    public function __construct(
        EntityManagerInterface $entityManager,
        FilterState $filterState,
        protected CacheManager $cache,
        protected ParameterBagInterface $parameterBag,
        protected ConfigAccessChecker $accessChecker,
        protected ?TenantInterface $tenantService = null,
        protected ValueFieldResolver $valueFieldResolver = new ValueFieldResolver(),
    ) {
        parent::__construct($entityManager, $filterState);
    }

    public function getClassName(): string
    {
        $metadata = $this->entityManager->getClassMetadata(ConfigInterface::class);

        return $metadata->name;
    }

    /**
     * A configuration of a group the user cannot see is "not found" (show page, in-place edit).
     */
    public function get($id): ?object
    {
        /** @var ConfigInterface|null $config */
        $config = parent::get($id);
        if ($config === null || !$this->canView($config)) {
            return null;
        }

        return $config;
    }

    public function list(?DatasourceParams $requestParams): iterable
    {
        /** @var ConfigInterface[] $list */
        $list = array_filter(
            [...parent::list($requestParams)],
            fn (ConfigInterface $config): bool => $this->canView($config),
        );

        $resultWithTenant = [];
        foreach ($list as $item) {
            if ($item->getTenantId()) {
                $resultWithTenant[] = $item->getLabel() . '-' . $item->getGroup();
            }
        }

        $result = [];
        foreach ($list as $item) {
            if ($item->getTenantId() || !in_array($item->getLabel() . '-' . $item->getGroup(), $resultWithTenant)) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * Same configurations as list() (visible groups, tenant line instead of the global one), whatever the page:
     * the pager compares it with the page size.
     */
    public function count(?DatasourceParams $requestParams): int
    {
        $allPagesParams = $requestParams === null ? null : (clone $requestParams)->setLimit(0)->setOffset(0);

        return count([...$this->list($allPagesParams)]);
    }

    /**
     * @param ConfigInterface $resource
     */
    public function save(object $resource): bool
    {
        if (!$this->canSave($resource)) {
            // the in-place edit has already changed the entity: reload it so that a later flush() cannot write it
            if ($this->entityManager->contains($resource)) {
                $this->entityManager->refresh($resource);
            }

            throw new AccessDeniedException(self::ACCESS_DENIED_MESSAGE);
        }

        // an empty text field arrives as null: store an empty string ("0" is a value, not an empty field)
        if (in_array($resource->getValueType(), [ConfigInterface::PASSWORD, ConfigInterface::STRING]) && $resource->getValueString() === null) {
            $resource->setValueString('');
        }
        if (in_array($resource->getValueType(), [ConfigInterface::TEXT]) && $resource->getValueText() === null) {
            $resource->setValueText('');
        }
        // an empty number is ignored (0 is a value): reload the entity so that a later flush() cannot write it
        if (
            ($resource->getValueType() === ConfigInterface::INT && $resource->getValueInt() === null)
            || ($resource->getValueType() === ConfigInterface::FLOAT && $resource->getValueFloat() === null)
        ) {
            $this->entityManager->refresh($resource);

            return true;
        }

        /** @var bool $usingTenant */
        $usingTenant = $this->parameterBag->get('lle_config.using_tenant');
        if (!$usingTenant || ($this->tenantService && $resource->getTenantId() === $this->tenantService->getTenantId())) {
            parent::save($resource);

            /** @var ConfigInterface $resource */
            $this->cache->set($resource);
        } else {
            $configWithTenant = $this->entityManager->getRepository(ConfigInterface::class)->findOneBy([
                'label' => $resource->getLabel(),
                'group' => $resource->getGroup(),
                'tenantId' => $this->tenantService ? $this->tenantService->getTenantId() : null
            ]);

            if ($configWithTenant) {
                $configWithTenant
                    ->setValueType((string) $resource->getValueType())
                    ->setValueBool($resource->getValueBool())
                    ->setValueString($resource->getValueString())
                    ->setValueText($resource->getValueText())
                    ->setValueInt($resource->getValueInt())
                    ->setValueFloat($resource->getValueFloat());

                $this->entityManager->refresh($resource);

                parent::save($configWithTenant);

                /** @var ConfigInterface $resource */
                $this->cache->set($configWithTenant);
            } else {
                /** @var ConfigInterface $config */
                $config = $this->newInstance();
                $config
                    ->setLabel((string) $resource->getLabel())
                    ->setGroup((string) $resource->getGroup())
                    ->setValueType((string) $resource->getValueType())
                    ->setValueBool($resource->getValueBool())
                    ->setValueString($resource->getValueString())
                    ->setValueText($resource->getValueText())
                    ->setValueInt($resource->getValueInt())
                    ->setValueFloat($resource->getValueFloat())
                    ->setTri($resource->getTri())
                    ->setTenantId($this->tenantService?->getTenantId());

                $this->entityManager->refresh($resource);

                parent::save($config);

                /** @var ConfigInterface $resource */
                $this->cache->set($config);
            }
        }

        return true;
    }

    protected function canView(ConfigInterface $config): bool
    {
        return $this->accessChecker->canView($config->getGroup());
    }

    /**
     * Defence in depth behind the editdata checks of Crudit (getEipFields(), lle_crudit.eip_strict), rights checked
     * on the stored configuration: only the value field of the stored type may change. Any other change (group,
     * valueType, tri, another value field, a field added later to the entity…) is refused, otherwise a secret could
     * be turned into a plain value or a configuration moved to a group the user can edit.
     */
    protected function canSave(ConfigInterface $config): bool
    {
        if (!$this->accessChecker->isEnabled()) {
            return true;
        }

        $storedData = $this->entityManager->getUnitOfWork()->getOriginalEntityData($config);
        $storedValueType = $storedData[self::VALUE_TYPE_FIELD] ?? $config->getValueType();
        $editableField = $this->valueFieldResolver->getFieldFor(is_string($storedValueType) ? $storedValueType : null);
        $metadata = $this->entityManager->getClassMetadata($config::class);
        foreach ($storedData as $field => $storedValue) {
            if ($field !== $editableField && $metadata->hasField($field) && $metadata->getFieldValue($config, $field) !== $storedValue) {
                return false;
            }
        }

        return $this->accessChecker->canEditValue($config);
    }
}

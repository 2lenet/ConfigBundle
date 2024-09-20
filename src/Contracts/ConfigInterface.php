<?php

namespace Lle\ConfigBundle\Contracts;

interface ConfigInterface
{
    public const BOOL = 'Bool';
    public const STRING = 'String';
    public const TEXT = 'Text';
    public const INT = 'Int';

    public function __toString();

    public function getId(): ?int;

    public function getLabel(): ?string;

    public function setLabel(string $label): self;

    public function getGroup(): ?string;

    public function setGroup(string $group): self;

    public function getValueType(): ?string;

    public function setValueType(string $valueType): self;

    public function getValueBool(): ?bool;

    public function setValueBool(?bool $valueBool): self;

    public function getValueString(): ?string;

    public function setValueString(?string $valueString): self;

    public function getValueText(): ?string;

    public function setValueText(?string $valueText): self;

    public function getValueInt(): ?int;

    public function setValueInt(?int $valueInt): self;

    public function getTri(): ?int;

    public function setTri(int $tri): self;

    public function getTenantId(): ?int;

    public function setTenantId(int $tenantId): self;
}

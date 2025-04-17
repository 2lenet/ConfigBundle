<?php

namespace Lle\ConfigBundle\Traits;

use Doctrine\ORM\Mapping as ORM;

trait ConfigTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $label = null;

    #[ORM\Column(type: 'string', length: 255, name: '`group`')]
    private ?string $group = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $valueType = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $valueBool = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $valueString = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $valueText = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $valueInt = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $valueFloat = null;

    #[ORM\Column(type: 'integer')]
    private int $tri = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $tenantId = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $transLabel = null;

    public function __toString(): string
    {
        return $this->group . '/' . $this->label;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function setGroup(string $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getValueType(): ?string
    {
        return $this->valueType;
    }

    public function setValueType(string $valueType): self
    {
        $this->valueType = $valueType;

        return $this;
    }

    public function getValueBool(): ?bool
    {
        return $this->valueBool;
    }

    public function setValueBool(?bool $valueBool): self
    {
        $this->valueBool = $valueBool;

        return $this;
    }

    public function getValueString(): ?string
    {
        return $this->valueString;
    }

    public function setValueString(?string $valueString): self
    {
        $this->valueString = $valueString;

        return $this;
    }

    public function getValueText(): ?string
    {
        return $this->valueText;
    }

    public function setValueText(?string $valueText): self
    {
        $this->valueText = $valueText;

        return $this;
    }

    public function getValueInt(): ?int
    {
        return $this->valueInt;
    }

    public function setValueInt(?int $valueInt): self
    {
        $this->valueInt = $valueInt;

        return $this;
    }

    public function getValueFloat(): ?float
    {
        return $this->valueFloat;
    }

    public function setValueFloat(?float $valueFloat): self
    {
        $this->valueFloat = $valueFloat;

        return $this;
    }

    public function getTri(): ?int
    {
        return $this->tri;
    }

    public function setTri(int $tri): self
    {
        $this->tri = $tri;

        return $this;
    }

    public function getTenantId(): ?int
    {
        return $this->tenantId;
    }

    public function setTenantId(?int $tenantId): self
    {
        $this->tenantId = $tenantId;

        return $this;
    }

    public function getTransLabel(): ?string
    {
        return $this->transLabel;
    }

    public function setTransLabel(string $transLabel): self
    {
        $this->transLabel = $transLabel;

        return $this;
    }
}

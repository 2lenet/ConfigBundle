<?php

namespace Lle\ConfigBundle\Traits;

use Doctrine\ORM\Mapping as ORM;

trait ConfigTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;
    #[ORM\Column(type: 'string', length: 255)]
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $label;
    #[ORM\Column(type: 'string', length: 255, name: '`group`')]
    /**
     * @ORM\Column(type="string", length=255, name="`group`")
     */
    private $group;
    #[ORM\Column(type: 'string', length: 255)]
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $valueType;
    #[ORM\Column(type: "boolean", nullable: true)]
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $valueBool;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $valueString;
    #[ORM\Column(type: "text", nullable: true)]
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $valueText;
    #[ORM\Column(type: "integer", nullable: true)]
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $valueInt;
    #[ORM\Column(type: "integer")]
    /**
     * @ORM\Column(type="integer")
     */
    private $tri = 0;

    public function __toString()
    {
        return $this->group . "/" . $this->label;
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

    public function getTri(): ?int
    {
        return $this->tri;
    }

    public function setTri(int $tri): self
    {
        $this->tri = $tri;

        return $this;
    }
}

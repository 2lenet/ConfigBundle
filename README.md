# ConfigBundle

[![Validate](https://github.com/2lenet/ConfigBundle/actions/workflows/validate.yml/badge.svg)](https://github.com/2lenet/ConfigBundle/actions/workflows/validate.yml)
[![.github/workflows/test.yml](https://github.com/2lenet/ConfigBundle/actions/workflows/test.yml/badge.svg)](https://github.com/2lenet/ConfigBundle/actions/workflows/test.yml)
[![SymfonyInsight](https://insight.symfony.com/projects/79583c27-dbb5-4610-accd-1ee16b92008d/mini.svg)](https://insight.symfony.com/projects/79583c27-dbb5-4610-accd-1ee16b92008d)


Symfony bundle that gives you an easy configuration for your app. Perfect to use with the famous
CRUD [Crudit](https://github.com/2lenet/CruditBundle)

- [Installation](#Installation)
- [Customization](#Customization)
- [Usage](#Usage)

## Installation

The bundle is not yet on packagist make sure to add the following to your `composer.json` file:

```json
{
    "url": "https://github.com/2lenet/ConfigBundle",
    "type": "git"
}
```

Install with composer:

```shell
composer require 2lenet/config-bundle
```

The bundle is flexible and built to suit your project it is shiped only with trait to use in your own config entity.

You will also get a Symfony Repository ready to use.

Create in your entity directory the class `Config` it has to implements the ConfigInterface if no customization is
needed you can use:

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Lle\ConfigBundle\Traits\ConfigTrait;
use App\Repository\ConfigRepository;
use Lle\ConfigBundle\Contracts\ConfigInterface;

/**
 * @ORM\Entity(repositoryClass=ConfigRepository::class)
 */
class Config implements ConfigInterface
{
    use ConfigTrait;
}

```

Your repository file has to extend the ConfigRepository from the bundle:

```php
<?php

namespace App\Repository;

use App\Entity\Config;
use Doctrine\Persistence\ManagerRegistry;
use Lle\ConfigBundle\Repository\AbstractConfigRepository;

/**
 * @method Config|null find($id, $lockMode = null, $lockVersion = null)
 * @method Config|null findOneBy(array $criteria, array $orderBy = null)
 * @method Config[]    findAll()
 * @method Config[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigRepository extends AbstractConfigRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Config::class);
    }
...
}

```

In your project add the folowing to the config file: `/config/packages/doctrine.yaml`

```yaml
doctrine:
    orm:
        resolve_target_entities:
            Lle\ConfigBundle\Contracts\ConfigInterface: App\Entity\Config
```

In `/config/routes.yaml` add:

```yaml
lle_config:
    resource: "@LleConfigBundle/Resources/config/routes.yaml"
```

You can then create a migration

```shell
bin/console make:migration
```

Check the migration file created and ask doctrine to execute the migration :

```shell
bin/console doctrine:migrations:migrate
```

You are ready to go!

## Customization

If you need more options or entity fields you can add them in your entity class:

```php
<?php

namespace App\Entity;

use App\Repository\ConfigRepository;
use Doctrine\ORM\Mapping as ORM;
use Lle\ConfigBundle\Trait\ConfigTrait;

/**
 * @ORM\Entity(repositoryClass=ConfigRepository::class)
 */
class Config
{
    use ConfigTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Establishment::class, inversedBy="configs")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Establishment $establishment;

    public function getEstablishment(): ?Establishment
    {
        return $this->establishment;
    }

    public function setEstablishment(?Establishment $establishment): self
    {
        $this->establishment = $establishment;

        return $this;
    }
}

```

You may also need more options than the ones in the Repository file, in that case create a new repository class in your
project. Don't forget to update the namspace used in your entity (see previous exemple).

## Usage

### General overview

To use the bundle inject in your services the config repository an use one of
the [available methods](#Available-methods).
The bundle will check if the configuration exist if not a new configuration will be created.

### Supported configurations

The bundle offer support for configuration in the following formats :

- boolean
- string
- text
- integer

### Available methods

```php
    public function getBool($group, $label, bool $default): bool
   
    public function setBool(string $group, string $label, bool $value): void
    
    public function getString($group, $label, string $default): string
    
    public function setString($group, $label, string $value): void
   
    public function getText($group, $label, string $default): string
    
    public function setText($group, $label, string $value): void
   
    public function getInt($group, $label, string $default): int
```

### Twig available function

```twig
{{ get_config_value('type', 'group', 'label', 'default') }}
```

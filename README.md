# ConfigBundle

[![Validate](https://github.com/2lenet/ConfigBundle/actions/workflows/validate.yml/badge.svg)](https://github.com/2lenet/ConfigBundle/actions/workflows/validate.yml)
[![.github/workflows/test.yml](https://github.com/2lenet/ConfigBundle/actions/workflows/test.yml/badge.svg)](https://github.com/2lenet/ConfigBundle/actions/workflows/test.yml)
[![SymfonyInsight](https://insight.symfony.com/projects/79583c27-dbb5-4610-accd-1ee16b92008d/mini.svg)](https://insight.symfony.com/projects/79583c27-dbb5-4610-accd-1ee16b92008d)


Symfony bundle that gives you an easy configuration for your app. Perfect to use with the famous
CRUD [Crudit](https://github.com/2lenet/CruditBundle)

- [Installation](#Installation)
- [Customization](#Customization)
- [Usage](#Usage)
- [Initialise new configurations (Warm-up)](#initialise-new-configurations-warm-up)
- [Restrict access by group (roles)](#restrict-access-by-group-roles)

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
use Lle\ConfigBundle\Service\CacheManager;

/**
 * @method Config|null find($id, $lockMode = null, $lockVersion = null)
 * @method Config|null findOneBy(array $criteria, array $orderBy = null)
 * @method Config[]    findAll()
 * @method Config[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigRepository extends AbstractConfigRepository
{
    public function __construct(ManagerRegistry $registry, CacheManager $cacheManager)
    {
        parent::__construct($cacheManager, $registry, Config::class);
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
use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Traits\ConfigTrait;

/**
 * @ORM\Entity(repositoryClass=ConfigRepository::class)
 */
class Config implements ConfigInterface
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

To use the bundle, inject in your services the config repository and use one of
the [available methods](#Available-methods).
The bundle will check if the configuration exist otherwise a new configuration will be created.

### Supported configurations

The bundle offers support for configuration in the following formats:

| Type       | Constant                      | Stored in      | Notes                                                                          |
|------------|-------------------------------|----------------|--------------------------------------------------------------------------------|
| `Bool`     | `ConfigInterface::BOOL`       | `valueBool`    | Switch.                                                                        |
| `String`   | `ConfigInterface::STRING`     | `valueString`  | Short text.                                                                    |
| `Text`     | `ConfigInterface::TEXT`       | `valueText`    | Long text (textarea).                                                          |
| `Int`      | `ConfigInterface::INT`        | `valueInt`     | Integer.                                                                       |
| `Float`    | `ConfigInterface::FLOAT`      | `valueFloat`   | Decimal number.                                                                |
| `Password` | `ConfigInterface::PASSWORD`   | `valueString`  | Secret: masked on the screen. See [group roles](#restrict-access-by-group-roles). |

A `Password` is stored like a `String` (it is **not encrypted** in the database). The difference is on the screen: the value is
masked, never sent to the browser without the SECRET role of its group (when the `group_roles` option is enabled)
and it is edited in a `password` input.

### Available methods

Every method belongs to your config repository (`AbstractConfigRepository`). The optional `$tenantId` is explained in
[Reading and writing](#reading-and-writing).

| Type       | Read                                                                     | Write                                                                  | Initialise (keeps an existing value)                                      |
|------------|--------------------------------------------------------------------------|------------------------------------------------------------------------|---------------------------------------------------------------------------|
| `Bool`     | `getBool($group, $label, bool $default, ?int $tenantId = null): bool`    | `setBool($group, $label, bool $value, ?int $tenantId = null): void`    | `initBool($group, $label, bool $default, ?int $tenantId = null): bool`    |
| `String`   | `getString($group, $label, string $default, ?int $tenantId = null): string` | `setString($group, $label, string $value, ?int $tenantId = null): void` | `initString($group, $label, string $default, ?int $tenantId = null): string` |
| `Text`     | `getText($group, $label, string $default, ?int $tenantId = null): string` | `setText($group, $label, string $value, ?int $tenantId = null): void`  | `initText($group, $label, string $default, ?int $tenantId = null): string` |
| `Int`      | `getInt($group, $label, int $default, ?int $tenantId = null): int`       | `setInt($group, $label, int $value, ?int $tenantId = null): void`      | `initInt($group, $label, int $default, ?int $tenantId = null): int`       |
| `Float`    | `getFloat($group, $label, float $default = 0, ?int $tenantId = null): float` | `setFloat($group, $label, float $value, ?int $tenantId = null): void` | `initFloat($group, $label, float $default, ?int $tenantId = null): float` |
| `Password` | `getPassword($group, $label, string $default, ?int $tenantId = null): string` | `setPassword($group, $label, string $value, ?int $tenantId = null): void` | `initPassword($group, $label, string $default, ?int $tenantId = null): string` |

```php
$apiUrl = $configRepository->getString('Saferpay', 'url', 'https://example.test');
$secret = $configRepository->getPassword('Saferpay', 'password', '');
$configRepository->setBool('Mails', 'copy', true, $tenantId);
```

`getPassword()` returns the real value: the masking only concerns the configuration screen, not your code.

### Twig integration

```twig
{{ get_config_value('type', 'group', 'label', 'default') }}
```

`type` is one of `Bool`, `String`, `Text`, `Int`, `Float` or `Password`. The default is converted to that type, and an unknown
type returns `null`.

> Avoid printing a `Password` in a template.

### Initialise new configurations (Warm-up)

A command allows you to initialise new configurations. We suggest to execute it everytime your app starts.

```
bin/console lle:config:warmup
```

To configure the default values, create a class that implements WarmupInterface.

```php
<?php

namespace App\Warmup;

use Lle\ConfigBundle\Contracts\WarmupInterface;
use Lle\ConfigBundle\Repository\AbstractConfigRepository;

class ConfigWarmup implements WarmupInterface
{
    public function warmup(AbstractConfigRepository $configRepository): void
    {
        $configRepository->initBool('CONFIG', 'active', true);
        $configRepository->initPassword('CONFIG', 'api_secret', '');
    }
}
```

Do not use set*(), because it will overwrite defined configurations.

### Reading and writing

- Without a `$tenantId`, the methods read and write the global value only (`tenantId IS NULL`), never the value of a tenant.
- `get*()` and `init*()` create a missing key with its default by a plain `INSERT` on the config table: they never flush
  the EntityManager, so the pending changes of the calling code are left untouched.
- `set*()` is a write: it flushes the EntityManager, as before.

## Restrict access by group (roles)

By default everybody who can open the configuration screen sees and edits every configuration. With the `group_roles`
option, access is decided **group by group** (the `group` field of a configuration) with roles managed by the
[credential bundle](https://github.com/2lenet/CredentialBundle).

### Enabling

```yaml
# config/packages/lle_config.yaml
lle_config:
    group_roles: true # default: false (everything allowed, as before)
```

The bundle works without the credential bundle while the option is disabled. To use the option, install it:

```shell
composer require 2lenet/credential-bundle
```

### Roles

| Role                                      | Effect                                                              |
|-------------------------------------------|---------------------------------------------------------------------|
| `ROLE_CONFIG_ACTION_SHOW_GROUP_<GROUP>`   | The group is visible, in read only.                                 |
| `ROLE_CONFIG_ACTION_EDIT_GROUP_<GROUP>`   | The values of the group can be edited (implies SHOW).               |
| `ROLE_CONFIG_ACTION_SECRET_GROUP_<GROUP>` | Passwords of the group can be read (SHOW) or edited (with EDIT).    |
| `ROLE_CONFIG_ACTION_SHOW_ALL`             | Same as SHOW, for every group.                                      |
| `ROLE_CONFIG_ACTION_EDIT_ALL`             | Same as EDIT, for every group.                                      |
| `ROLE_CONFIG_ACTION_SECRET_ALL`           | Same as SECRET, for every group.                                    |

`<GROUP>` is the group name in upper case, slugged with `_` as separator: `Saferpay` becomes `SAFERPAY`,
`Payment Service` becomes `PAYMENT_SERVICE`.

Rules:

- `EDIT` implies `SHOW`.
- `SECRET` alone does **not** make a group visible: the group also needs `SHOW` or `EDIT`.
- A **Password** configuration is masked (`••••••••`) and never sent in the HTML without `SECRET`.
  With `SECRET` and `SHOW` it is revealed on click, in read only. Editing it requires `EDIT` **and** `SECRET`.
- A group without any role is not listed, and its configurations answer "not found" on a direct URL.
- Writes are checked on the server too: a refused change is not saved, a configuration cannot be moved to another
  group and a Password cannot be turned into another type without the right to edit it.

Do not add these roles to `role_hierarchy`: the credential bundle manages them.

### Declaring the roles

The roles are created in the credential bundle by the warm-up, one set per group found in the configuration table:

```shell
bin/console lle:credential:warmup
```

Run it again when a new group appears. The warm-up creates nothing while the option is disabled. Then grant the roles to
your groups in the credential screen. `ROLE_SUPER_ADMIN` gets everything.

The screen itself still needs the usual `ROLE_CONFIG_INDEX`, `ROLE_CONFIG_SHOW` and `ROLE_CONFIG_EDIT` credentials.

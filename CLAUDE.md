# ConfigBundle

Bundle Symfony (2lenet/config-bundle) fournissant un systeme de configuration key/value persistee en BDD avec cache PSR-6.
Dependance forte sur [CruditBundle](https://github.com/2lenet/CruditBundle) pour l'interface CRUD.

## Architecture

```
src/
  Contracts/
    ConfigInterface.php      # Interface de l'entite config (constantes BOOL, STRING, TEXT, INT, PASSWORD, FLOAT)
    TenantInterface.php      # Interface optionnelle pour le multi-tenant (getTenantId)
    WarmupInterface.php      # Interface pour initialiser des configs au warmup (tag lle.config.warmup)
  Traits/
    ConfigTrait.php          # Trait Doctrine ORM avec tous les champs (id, label, group, valueType, value*, tri, tenantId)
  Repository/
    AbstractConfigRepository.php  # ServiceEntityRepository avec get/set/init pour chaque type (Bool, String, Text, Int, Password, Float)
  Service/
    CacheManager.php         # Gestion du cache PSR-6 (CacheItemPoolInterface)
  Command/
    WarmupCommand.php        # Commande `lle:config:warmup` - execute les WarmupInterface enregistres
  Controller/Crudit/
    ConfigController.php     # CRUD controller + action refreshCache
  Crudit/
    Config/ConfigCrudConfig.php       # Configuration CRUD (champs, actions, route root)
    Datasource/ConfigDatasource.php   # Datasource Doctrine avec logique multi-tenant
  DependencyInjection/
    LleConfigExtension.php   # Chargement services.yaml + parametre lle_config.using_tenant
    Configuration.php        # Configuration semantique (using_tenant, tenant_service)
  Twig/
    ConfigExtension.php      # Fonction Twig `get_config_value(type, group, name, default)`
  LleConfigBundle.php        # Classe bundle (extends Bundle)
  Resources/config/
    services.yaml            # Autowiring + config specifique ConfigExtension et ConfigDatasource
    routes.yaml              # Route attribute sur ConfigController
```

## Fonctionnement

- L'app consommatrice cree une entite `Config` implementant `ConfigInterface` (via `ConfigTrait`) et un repository etendant `AbstractConfigRepository`.
- Le mapping Doctrine `resolve_target_entities` lie `ConfigInterface` a l'entite concrete.
- Les valeurs sont lues/ecrites via le repository (get/set/initBool, getString, etc.) avec cache PSR-6 automatique.
- Le multi-tenant est optionnel : active via `lle_config.using_tenant` + un service implementant `TenantInterface`.

## Compatibilite

- **Symfony** : ^6.0 | ^7.0 | ^8.0
- **Twig** : ^3.4 | ^4.0
- **PHP** : >=8.1
- Pattern DI classique (Bundle + Extension + Configuration), pas AbstractBundle, pour garder la retro-compat SF6.

## Conventions

- Les methodes du repository suivent le pattern `get{Type}`, `set{Type}`, `init{Type}` (init = cree seulement si absent).
- Cle de cache : `lle_config_cache_{group}_{label}_{valueType}[_{tenantId}]`
- Route root Crudit : `lle_config_crudit_config`

## Commandes utiles

```bash
# Analyse statique
vendor/bin/phpstan analyse
# Code style
vendor/bin/phpcs
```

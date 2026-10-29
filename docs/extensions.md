---
title: Extensions
order: 20
---

# Extensions

Three final classes extending Twig's `AbstractExtension`, in `Omnibridge\Twig\`.

## ContractExtension

```php
new ContractExtension(Urls $urls, Translations $translations)
```

| Twig | Answered by | Notes |
|---|---|---|
| `path(name, parameters = {})` | `Urls::path()` | twig-bridge's name; the contract has no third argument (`relative`) |
| `url(name, parameters = {})` | `Urls::url()` | the contract has no third argument (`schemeRelative`) |
| `asset(path)` | `Urls::asset()` | the contract has no package name argument |
| `\|trans(parameters = {}, domain = null, locale = null)` | `Translations::trans()` | the parameters as the catalogue writes them (`{title}` for ICU) |

Not marked safe: their output is escaped like any string (an address is safe to escape).

## AccessExtension

```php
new AccessExtension(?Access $access = null, ?Tokens $tokens = null)
```

| Twig | Answered by | Registered when |
|---|---|---|
| `is_granted(attribute, subject = null)` | `Access::isGranted()` | an `Access` is given |
| `csrf_token(intent)` | `Tokens::csrf()` | a `Tokens` is given |

Registered after twig-bridge's `SecurityExtension` and `CsrfExtension` under Symfony, so the same
names answer through the contracts (and the same Symfony services). A contract not given leaves its
function out: a template that calls it fails to compile ("Unknown function"), as it would under
Symfony without SecurityBundle.

## MapExtension

```php
new MapExtension(
    MapPrinter $printer,          // omnimap's
    Urls $urls,
    ?Access $access = null,
    ?Tokens $tokens = null,
    string $control = 'ROLE_ADMIN',
    array $routes = [],           // ['export' => route name, 'suppress' => route name]
)
```

| Twig | Output |
|---|---|
| `omnimap(view)` | `MapPrinter::map($view, $mayChange)`: the picture kept in omnimap's cache when there is one (made now by the server when it can), else the live map - for whoever may change the map only when the printer is `cache_only` (an empty frame for a visitor), for everyone otherwise; `''` for a null view. HTML, marked safe |
| `omnimap_export(view, attributes = {})` | the button that sends the live map's picture to the cache, for whoever may change the map when the live map is what they see; posts to `Urls::path(routes.export ?? 'omnimap_export', {signature})` with `Tokens::csrf('omnimap-export')`; `attributes.text` is its content (default `O`), the rest its HTML attributes; `''` otherwise |
| `omnimap_suppress(view, attributes = {})` | the button that deletes the picture kept, for the same, when there is a picture; route `routes.suppress ?? 'omnimap_suppress'`, intent `omnimap-suppress`, default text `X` |
| `omnimap_signature(view)` | the view's signature, the picture's name |

"May change the map" is `Access::isGranted($control)`; false without an `Access`. Without `Tokens`
the buttons carry an empty token. The constants `MapExtension::EXPORT` and `MapExtension::SUPPRESS`
are the two intents, which the hosts' controllers check. The buttons post to the routes named by
the host: under Symfony omnibridge/symfony's map cache routes; under Laravel routes the application
declares itself (omnibridge/laravel routes the tile only, and names the others for the buttons).

```twig
{{ omnimap(view) }}
{{ omnimap_export(view, {class: 'item', text: 'Capture'}) }}
{{ omnimap_suppress(view, {text: 'Delete'}) }}
```


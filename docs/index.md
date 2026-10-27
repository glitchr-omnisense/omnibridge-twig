# omnibridge/twig

The small contract a page rendered with Twig asks of any framework, so the same template renders
under Symfony, Laravel or plain PHP (plan 0, rule 13: a screen is described once, a view bridge
renders it).

```sh
composer require omnibridge/twig
```

- The contract is [omnisense/omnibridge](https://github.com/glitchr-omnisense/omnibridge)'s, the
  family's core: `Omnibridge\Contract\Urls` (`path(name, parameters)`, `url(name, parameters)`,
  `asset(path)`) and `Omnibridge\Contract\Translations` (`trans(id, parameters, domain, locale)` on
  ICU catalogues, `exists(id, domain, locale)`).
- `ContractExtension`: registers `path()`, `url()`, `asset()` and the `trans` filter under the names
  Symfony gives them. Symfony already has them and does not register it; another framework registers
  it with its own answers to the contract.
- `AccessExtension`: `is_granted(attribute, subject)` on `Omnibridge\Contract\Access` and
  `csrf_token(intent)` on `Omnibridge\Contract\Tokens`, under Symfony's names. Every host registers
  it, Symfony included (omnibridge/symfony, after twig-bridge's own, which it then answers with the
  same services): a page asks the same code under either framework. A contract the host does not
  bind leaves its function out.
- `MapExtension` (with omnisense/omnimap, suggested): `omnimap(view)` - the picture of a map kept in
  omnimap's cache, or the live map, by omnimap's `MapPrinter` rule -, `omnimap_export(view,
  attributes)` and `omnimap_suppress(view, attributes)` - the buttons that change the cache, for
  whoever the host's `Access` grants the control attribute (`ROLE_ADMIN`), posting a `Tokens` token to
  the host's map cache routes -, `omnimap_signature(view)`. omnibridge/symfony and omnibridge/laravel
  register it when omnimap is configured.
- `HelperExtension`: the filters omnibase's templates use that are not Twig's own.

The former names `Omnibridge\Twig\Contract\Urls` and `Omnibridge\Twig\Contract\Translations`,
aliases of omnisense/omnibridge's interfaces since its step A2, were removed at omnibase 4's final
pass (commit 976de23): name `Omnibridge\Contract\Urls` and `Omnibridge\Contract\Translations`.

Pilot: lesringostars' article screen, rendered by Symfony and by Laravel from one template.

## Who registers what

| Extension | Under Symfony (omnibridge/symfony) | Under Laravel (omnibridge/laravel's `View\Twig`) | Plain PHP |
|---|---|---|---|
| `ContractExtension` | not registered: twig-bridge's `path()`, `url()`, `asset()`, `\|trans` answer | registered with the container's `Urls` and `Translations` | by hand |
| `AccessExtension` | registered after twig-bridge's, with the container's `Access` and `Tokens` (each null when absent) | registered with the container's `Access` and `Tokens` when bound | by hand |
| `MapExtension` | registered when `omnimap.yaml` is configured and TwigBundle is there | registered when `config/omnimap.php` names a provider | by hand |
| `HelperExtension` | registered first by omnisense/omnibase; omnibase's own filters of the same names win | registered with the page's locale and time zone | by hand |

## Requirements

PHP 8.2 or later, `omnisense/omnibridge` 1.x, `twig/twig` 3.10 or later; `omnisense/omnimap` for
`MapExtension` (suggested). `HelperExtension`'s `lang_name` and `datetime` use PHP's intl
(`\Locale`, `\IntlDateFormatter`), its text filters mbstring - neither declared by composer.json
([Helpers](helpers.md)).

## How this manual is organised

- [Installation](installation.md): a page rendered in plain PHP, and its output.
- [Extensions](extensions.md): `ContractExtension`, `AccessExtension`, `MapExtension` - every
  function and filter, their arguments, what answers them.
- [Helpers](helpers.md): `HelperExtension`'s function and thirteen filters, with a run.
- [Testing](testing.md), [Verified, and not](verified.md).

## Licence

MIT.

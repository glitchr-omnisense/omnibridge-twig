# omnibridge/twig

The small contract a page rendered with Twig asks of any framework, so the same
template renders under Symfony, Laravel or plain PHP (plan 0, rule 13: a screen is
described once, a view bridge renders it).

- The contract is omniscient/omnibridge's, the family's core:
  `Omnibridge\Contract\Urls` (`path(name, parameters)`, `url(name, parameters)`,
  `asset(path)`) and `Omnibridge\Contract\Translations` (`trans(id, parameters,
  domain, locale)` on ICU catalogues, `exists(id, domain, locale)`).
- `ContractExtension`: registers `path()`, `url()`, `asset()` and the `trans` filter
  under the names Symfony gives them. Symfony already has them and does not
  register it; another framework registers it with its own answers to the contract.
- `AccessExtension`: `is_granted(attribute, subject)` on `Omnibridge\Contract\Access` and
  `csrf_token(intent)` on `Omnibridge\Contract\Tokens`, under Symfony's names. Every host
  registers it, Symfony included (omnibridge/symfony, after twig-bridge's own, which it then
  answers with the same services): a page asks the same code under either framework. A
  contract the host does not bind leaves its function out.
- `MapExtension` (with omniscient/omnimap, suggested): `omnimap(view)` - the picture of a map
  kept in omnimap's cache, or the live map, by omnimap's `MapPrinter` rule -, `omnimap_export(view,
  attributes)` and `omnimap_suppress(view, attributes)` - the buttons that change the cache, for
  whoever the host's `Access` grants the control attribute (ROLE_ADMIN), posting a `Tokens`
  token to the host's map cache routes -, `omnimap_signature(view)`. omnibridge/symfony and
  omnibridge/laravel register it when omnimap is configured.
- `HelperExtension`: the filters omnibase's templates use that are not Twig's own.

The former names `Omnibridge\Twig\Contract\Urls` and `Omnibridge\Twig\Contract\Translations`,
aliases of omniscient/omnibridge's interfaces since its step A2, were removed at omnibase 4's
final pass: name `Omnibridge\Contract\Urls` and `Omnibridge\Contract\Translations`.

Pilot: lesringostars' article screen (`~/.claude/plans/lesringostars-deux-cadres.md`).

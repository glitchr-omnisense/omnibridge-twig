# omnibridge/twig

The small contract a page rendered with Twig asks of any framework, so the same
template renders under Symfony, Laravel or plain PHP (plan 0, rule 13: a screen is
described once, a view bridge renders it).

- `Contract\Urls`: `path(name, parameters)`, `asset(path)`
- `Contract\Translations`: `trans(id, parameters, domain, locale)` on ICU catalogues
- `ContractExtension`: registers `path()`, `asset()` and the `trans` filter under the
  names Symfony gives them. Symfony already has them and does not register it; another
  framework registers it with its own answers to the contract.

Pilot: lesringostars' article screen (`~/.claude/plans/lesringostars-deux-cadres.md`).

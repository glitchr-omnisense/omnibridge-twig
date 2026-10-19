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
- `HelperExtension`: the filters omnibase's templates use that are not Twig's own.

Former names: `Omnibridge\Twig\Contract\Urls` and `Omnibridge\Twig\Contract\Translations`
remain aliases of omniscient/omnibridge's interfaces (declared with the autoloader,
`autoload.files`) until omnibase 4's final pass. The former Translations asked
`trans()` alone; the family's asks `exists()` too.

Pilot: lesringostars' article screen (`~/.claude/plans/lesringostars-deux-cadres.md`).

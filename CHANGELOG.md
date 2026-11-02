# Changelog

## [Unreleased]

### Added

- **`FormThemeExtension`** and `templates/form/theme.html.twig` (plan "omnibase agnostic", step F5b): the generic form theme, `form()`,
  `form_start()`, `form_widget()`, `form_row()`, `form_label()`, `form_help()`, `form_errors()`, `form_rest()`, `form_end()` on a neutral
  form view (omnibase's `Base\Form\Definition\FormView`, any object with `$vars`, `$children` and `$rendered`), in omnibase's markup, for a
  host without Symfony Form. Registered by `FormThemeExtension::register($twig, $translations)`.
- **`TwigViews`**: omnisense/omnibridge's `Views` and `ViewSources` answered by Twig's `Environment`
  (given, or a closure asked when first needed) - render a template by its name, whether it exists, a
  file's path and contents (plan "omnibase agnostic", step F5a: omnibase's services render through
  `Views`, not Twig's environment). Needs omnisense/omnibridge 648a843 or later (`ViewSources`).

### Fixed

- `|str_shorten` (`HelperExtension::shorten()`) measures the text in characters (`mb_strlen`), as it
  cuts it (`mb_substr`): a multibyte text was cut short, and at the front or in the middle the cut
  started past its end (41 `é` to 10 at the front gave `[..]` alone).
- `MapExtension`'s docblock: under Laravel the buttons' routes are the application's (omnibridge/laravel
  routes the tile only); it named omnibridge/laravel's map cache routes.
- composer.json requires `ext-mbstring` and `ext-intl`, which HelperExtension uses (the text filters, `\Locale`, `\IntlDateFormatter`).

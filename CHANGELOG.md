# Changelog

## [Unreleased]

### Fixed

- `|str_shorten` (`HelperExtension::shorten()`) measures the text in characters (`mb_strlen`), as it
  cuts it (`mb_substr`): a multibyte text was cut short, and at the front or in the middle the cut
  started past its end (41 `é` to 10 at the front gave `[..]` alone).
- `MapExtension`'s docblock: under Laravel the buttons' routes are the application's (omnibridge/laravel
  routes the tile only); it named omnibridge/laravel's map cache routes.
- composer.json requires `ext-mbstring` and `ext-intl`, which HelperExtension uses (the text filters, `\Locale`, `\IntlDateFormatter`).

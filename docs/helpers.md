---
title: Helpers
order: 30
---

# Helpers

`HelperExtension`: the small helpers omnibase gives the templates of its sites, for a framework
rendering those templates without omnibase - the same names and results as omnibase's
(`Resources/Functions.php` and `FunctionTwigExtension`), framework-free. omnibase registers this
extension first and its own win where they do more; `|at` and `|emoji_only` are only here.

```php
new HelperExtension(string $locale = 'fr', ?string $timezone = null)
```

`$locale` is the language of `|lang_name` and `|datetime`; `$timezone` the zone `|datetime` prints
in (the date's own when null).

## Function

| Function | Result |
|---|---|
| `html_attributes(attributes, ...more)` | `key="value"` pairs joined by spaces, the arrays merged, each key and value trimmed, nothing escaped; marked safe |

## Filters

| Filter | Arguments | Result |
|---|---|---|
| `str_shorten` | `length = 100`, `position = 1`, `separator = ' [..] '` | the text without tags, cut to `length` characters with the separator where it was cut: at the back (`1`, `SHORTEN_BACK`), in the middle (`0`), at the front (`-1`); unchanged when it is at most `length` plus the separator long; `''` for a length of 0 |
| `mb_ucfirst` | | the first letter upper case, multibyte; each item of a list |
| `second` | | a list's second value, null when it has none |
| `join_if_exists` | `separator` | the non-empty items joined; null for null |
| `highlight` | `pattern` (a string or a list) | each word containing a pattern wrapped in `<mark class="markdown-word">`, the pattern itself in `<mark class="markdown-pattern">` |
| `less_than` | `diff` (`'+2 day'`, a date, seconds) | whether a date (a string, a `DateTimeInterface`, a timestamp) is more recent than the difference |
| `html_decode` | | HTML entities decoded (HTML5, quotes) |
| `lang` | | the language of a locale: `fr-FR`, `fr_FR` => `fr` |
| `country` | | its country: `en-GB` => `GB`; a bare language its own (`fr` => `FR`) |
| `lang_name` | | the language's name in the extension's locale (`ja-JP` => `japonais`) |
| `datetime` | `pattern = 'YYYY-MM-dd HH:mm:ss'`, `locale = null` | a date by an ICU pattern, lower case; an ISO 8601 string (a screen's moment) read as that moment; any other string only lowered; nothing (null, `''`, false, 0) the pattern itself; an integer a timestamp |
| `at` | `index` | a list's entry at an index, null when it has none |
| `emoji_only` | | whether a text is emoji alone, spaces aside (a comment of one emoji shown big) |

`HelperExtension::isMoment()` is the ISO 8601 test `|datetime` uses (a date, a time, a zone or
not).

## A run

```twig
{{ 'Le mont Fuji, vu depuis le lac Kawaguchi au lever du jour'|str_shorten(20) }}
{{ 'Le mont Fuji, vu depuis le lac Kawaguchi au lever du jour'|str_shorten(20, 0) }}
{{ 'été'|mb_ucfirst }} {{ ['a', 'b', 'c']|second }} {{ ['a', null, 'c']|join_if_exists(', ') }}
{{ 'le mont Fuji'|highlight('Fuji') }}
{{ 'fr-FR'|lang }} {{ 'en-GB'|country }} {{ 'fr'|country }} {{ 'ja-JP'|lang_name }}
{{ '2026-04-10T18:05:00+00:00'|datetime('d MMMM YYYY HH:mm') }} {{ 'Présent'|datetime }}
{{ ['x', 'y']|at(1) }}{{ ['x', 'y']|at(5) is null ? ' (none)' }} {{ '👍 🎉'|emoji_only ? 'emoji' : 'text' }}
<div {{ html_attributes({class: 'card big', 'data-id': 12}) }}></div>
{{ 'ééééééééééééééééééééééééééééééééééééééééé'|str_shorten(10, -1) }}
```

With `new HelperExtension('fr', 'Asia/Tokyo')`, PHP 8.4 with intl, 2026-10-27:

```text
Le mont Fuji, vu dep [..]
Le mont Fu [..] er du jour
Été b a, c
le mont &lt;mark class=&quot;markdown-word&quot;&gt;&lt;mark class=&quot;markdown-pattern&quot;&gt;Fuji&lt;/mark&gt;&lt;/mark&gt;
fr GB FR japonais
11 avril 2026 03:05 présent
y (none) emoji
<div class="card big" data-id="12"></div>
[..] éééééééééé
```

- `|str_shorten` measures and cuts in characters (`mb_strlen`, `mb_substr`): 41 `é` shortened to 10
  at the front keep their last ten.
- The moment `18:05 UTC` is printed in Tokyo's time (`03:05` the next day).
- `|highlight` is not marked safe: under autoescaping its marks are escaped, as the run shows. Write
  `|highlight(...)|raw` on a text that is already safe - the filter does not escape the text itself.


---
title: Verified, and not
order: 90
---

# Verified, and not

## What the tests prove

Run on 2026-10-27 in `omnibridge-symfony:php8.4` (PHP 8.4.26, PHPUnit 11.5.57), the checkout's own
`vendor/`, no network: 10 tests, 54 assertions, green ([Testing](testing.md#the-packages-own-suite)).

The examples of this manual were run in the same image that day and their output pasted: the plain
PHP page ([Installation](installation.md)) and the helpers ([Helpers](helpers.md#a-run)).

## Verified elsewhere

- Under Symfony: omnibridge/symfony's `TwigTest` (the functions are the contracts, answered as the
  framework's services, left out without security).
- Under Laravel: omnibridge/laravel's `TwigAccessTest` and `OmnibaseTwigTest`.
- lesringostars' article screen, one template rendered by both hosts (the pilot).

## What is not verified

- Twig versions other than the one the checkout installs (composer.json admits 3.10 and later).
- A PHP without intl or mbstring: composer refuses to install the package (it requires both; [Helpers](helpers.md)).
- `|less_than` and `|datetime` around a change of time zone offset: not covered by the tests.

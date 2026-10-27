---
title: Testing
order: 60
---

# Testing

## Testing a template

The extensions take the contracts as constructor arguments: a test gives them doubles, renders the
template with Twig's `ArrayLoader` or `FilesystemLoader`, and compares the HTML - the
[Installation](installation.md) page's script is such a test. A template shared by two hosts is
rendered by both and compared (lesringostars' parity suite does it for its pages).

## The package's suite

The package has no Docker harness of its own: its suite runs in any PHP 8.2+ image with the checkout's
`vendor/` installed. With omnibridge/symfony's image (PHP 8.4, intl, GD):

```sh
docker run --rm --network none -v "$PWD":/package:ro omnibridge-symfony:php8.4 vendor/bin/phpunit --do-not-cache-result
```

| Test class | What it checks |
|---|---|
| `ContractExtensionTest` | a page uses the contract under Symfony's names (`path`, `url`, `asset`, `\|trans`) |
| `AccessExtensionTest` | a page asks the host's contracts; it answers in place of the framework's own functions; a contract the host lacks leaves its function out |
| `MapExtensionTest` | a visitor (the frame, no button), an administrator before a picture (the live map, the export button, no suppress) and after (the picture, both buttons); skipped without GD |
| `HelperExtensionTest` | the sites' helpers; an entry and emoji alone; dates in the page's language and words as they are; recent and highlighted; an ISO 8601 moment is that moment |

The CI (GitHub Actions, glitchr-studio/ci's package workflow) runs PHPUnit and PHPStan at the highest
level against `phpstan-baseline.neon`.

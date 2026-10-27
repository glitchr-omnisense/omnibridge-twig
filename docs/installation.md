---
title: Installation
order: 10
---

# Installation

```sh
composer require omnibridge/twig
```

Under Symfony or Laravel the host bridge registers the extensions ([Who registers what](index.md#who-registers-what)):
nothing to do. Without a framework, give each extension the contracts it asks.

## A page in plain PHP

```php
<?php

require 'vendor/autoload.php';

use Omnibridge\Contract\Access;
use Omnibridge\Contract\Tokens;
use Omnibridge\Contract\Translations;
use Omnibridge\Contract\Urls;
use Omnibridge\Twig\AccessExtension;
use Omnibridge\Twig\ContractExtension;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

$urls = new class implements Urls {
    public function path(string $name, array $parameters = []): string { return '/'.$name.'/'.implode('/', $parameters); }
    public function url(string $name, array $parameters = []): string { return 'https://example.org'.$this->path($name, $parameters); }
    public function asset(string $path): string { return '/build/'.$path; }
};
$translations = new class implements Translations {
    private const MESSAGES = ['article.read' => 'Lire « {title} »'];
    public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        return strtr(self::MESSAGES[$id] ?? $id, array_combine(array_map(static fn ($k) => '{'.$k.'}', array_keys($parameters)), $parameters));
    }
    public function exists(string $id, ?string $domain = null, ?string $locale = null): bool { return isset(self::MESSAGES[$id]); }
};
$access = new class implements Access {
    public function isGranted(mixed $attribute, mixed $subject = null): bool { return 'ROLE_ADMIN' === $attribute; }
    public function getUser(): ?object { return null; }
};
$tokens = new class implements Tokens {
    public function csrf(string $intent): string { return hash('sha256', 'session'.$intent); }
    public function isValid(string $intent, ?string $token): bool { return $token === $this->csrf($intent); }
};

$twig = new Environment(new ArrayLoader(['article.html.twig' => <<<'TWIG'
<a href="{{ path('article', {slug: 'le-fuji'}) }}">{{ 'article.read'|trans({title: 'Le Fuji'}) }}</a>
<link rel="canonical" href="{{ url('article', {slug: 'le-fuji'}) }}">
<link rel="stylesheet" href="{{ asset('app.css') }}">
{% if is_granted('ROLE_ADMIN') %}<form method="post"><input type="hidden" name="_token" value="{{ csrf_token('delete_12')|slice(0, 12) }}"></form>{% endif %}
TWIG]));
$twig->addExtension(new ContractExtension($urls, $translations));
$twig->addExtension(new AccessExtension($access, $tokens));
echo $twig->render('article.html.twig');
```

Its output (PHP 8.4, Twig 3, 2026-10-27):

```text
<a href="/article/le-fuji">Lire « Le Fuji »</a>
<link rel="canonical" href="https://example.org/article/le-fuji">
<link rel="stylesheet" href="/build/app.css">
<form method="post"><input type="hidden" name="_token" value="e6a6fba63d4d"></form>
```

In a real host the contracts are the bridge's adapters (omnibridge/laravel's `RouterUrls`,
`CatalogueTranslations`, `GateAccess`, `SessionTokens`), or `Omnibridge\Runtime`'s:
`new ContractExtension(Runtime::urls(), Runtime::translations())`.

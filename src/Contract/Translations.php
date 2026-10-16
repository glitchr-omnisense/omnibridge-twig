<?php

namespace Omnibridge\Twig\Contract;

/**
 * A message in the page's language, from the application's catalogues (ICU
 * messages: `{0}` and `{name}` placeholders, plurals).
 */
interface Translations
{
    /** @param array<int|string, mixed> $parameters */
    public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string;
}

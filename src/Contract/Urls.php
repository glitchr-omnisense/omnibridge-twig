<?php

namespace Omnibridge\Twig\Contract;

/**
 * Addresses, as a page asks for them: a route by its name, a public file by
 * its path. Each framework answers with its own router and asset packages.
 */
interface Urls
{
    /** @param array<string, scalar> $parameters */
    public function path(string $name, array $parameters = []): string;

    public function asset(string $path): string;
}

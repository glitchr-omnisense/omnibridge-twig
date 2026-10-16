<?php

namespace Omnibridge\Twig;

use Omnibridge\Twig\Contract\Translations;
use Omnibridge\Twig\Contract\Urls;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * What a page may use beyond Twig itself: `path()`, `asset()` and the `trans`
 * filter, under the names Symfony gives them. A framework that already has
 * them (Symfony) does not register this extension; any other one does, with
 * its own answers to the contract.
 */
final class ContractExtension extends AbstractExtension
{
    public function __construct(
        private readonly Urls $urls,
        private readonly Translations $translations,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('path', $this->urls->path(...)),
            new TwigFunction('asset', $this->urls->asset(...)),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('trans', $this->translations->trans(...)),
        ];
    }
}

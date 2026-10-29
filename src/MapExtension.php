<?php

namespace Omnibridge\Twig;

use Omnibridge\Contract\Access;
use Omnibridge\Contract\Tokens;
use Omnibridge\Contract\Urls;
use Omnimap\MapPrinter;
use Omnimap\Model\MapView;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * omnisense/omnimap in a page, whichever framework renders it:
 *
 *   {{ omnimap(view) }}                                    the picture kept, or the live map (MapPrinter's rule)
 *   {{ omnimap_export(view, {class: 'item', text: '…'}) }}  the button that sends the live map's picture to the cache
 *   {{ omnimap_suppress(view, {text: '…'}) }}               the button that deletes it
 *
 * Who may change the map is the host's Access ($control, ROLE_ADMIN by
 * default); the buttons post to the routes named $routes with a token of
 * the host's Tokens (intents "omnimap-export", "omnimap-suppress"): under
 * Symfony omnibridge/symfony's map cache routes; under Laravel routes the
 * application declares itself (omnibridge/laravel routes the tile only).
 * Registered by omnibridge/symfony and omnibridge/laravel when omnimap is
 * configured.
 *
 * @suggested omnisense/omnimap
 */
final class MapExtension extends AbstractExtension
{
    public const EXPORT = 'omnimap-export';
    public const SUPPRESS = 'omnimap-suppress';

    /** @param array{export?: string, suppress?: string} $routes */
    public function __construct(
        private readonly MapPrinter $printer,
        private readonly Urls $urls,
        private readonly ?Access $access = null,
        private readonly ?Tokens $tokens = null,
        private readonly string $control = 'ROLE_ADMIN',
        private readonly array $routes = [],
    ) {
    }

    public function getFunctions(): array
    {
        $html = ['is_safe' => ['html']];

        return [
            new TwigFunction('omnimap', $this->map(...), $html),
            new TwigFunction('omnimap_export', $this->export(...), $html),
            new TwigFunction('omnimap_suppress', $this->suppress(...), $html),
            new TwigFunction('omnimap_signature', $this->printer->signature(...)),
        ];
    }

    public function map(?MapView $view): string
    {
        return null === $view ? '' : $this->printer->map($view, $this->mayChange());
    }

    /** @param array<string, string> $attributes the button's, and its text */
    public function export(?MapView $view, array $attributes = []): string
    {
        if (null === $view || !$this->mayChange()) {
            return '';
        }
        $text = $attributes['text'] ?? 'O';
        unset($attributes['text']);
        $url = $this->urls->path($this->routes['export'] ?? 'omnimap_export', ['signature' => $this->printer->signature($view)]);

        return $this->printer->exportButton($view, true, $url, (string) $this->tokens?->csrf(self::EXPORT), $text, $attributes);
    }

    /** @param array<string, string> $attributes the button's, and its text */
    public function suppress(?MapView $view, array $attributes = []): string
    {
        if (null === $view || !$this->mayChange()) {
            return '';
        }
        $text = $attributes['text'] ?? 'X';
        unset($attributes['text']);
        $url = $this->urls->path($this->routes['suppress'] ?? 'omnimap_suppress', ['signature' => $this->printer->signature($view)]);

        return $this->printer->suppressButton($view, true, $url, (string) $this->tokens?->csrf(self::SUPPRESS), $text, $attributes);
    }

    private function mayChange(): bool
    {
        return (bool) $this->access?->isGranted($this->control);
    }
}

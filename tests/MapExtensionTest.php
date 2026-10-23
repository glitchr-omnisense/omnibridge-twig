<?php

namespace Omnibridge\Twig\Tests;

use Omnibridge\Contract\Access;
use Omnibridge\Contract\Tokens;
use Omnibridge\Contract\Urls;
use Omnibridge\Twig\MapExtension;
use Omnimap\MapHtml;
use Omnimap\MapPrinter;
use Omnimap\Model\LatLng;
use Omnimap\Model\MapView;
use Omnimap\Signer;
use Omnimap\Snapshot\DirectoryStorage;
use Omnimap\Snapshot\Snapshots;
use Omnimap\Snapshot\SnapshotStore;
use Omnimap\Testing\PlainTiles;
use Omnimap\Testing\RecordedHttpClient;
use Omnimap\Testing\TestTiles;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class MapExtensionTest extends TestCase
{
    public function testAVisitorAndWhoeverMayChangeTheMap(): void
    {
        if (!\function_exists('imagecreatetruecolor')) {
            self::markTestSkipped('GD is not installed: omnimap cuts its pictures with it.');
        }
        $dir = sys_get_temp_dir().'/omnibridge-twig-map-'.bin2hex(random_bytes(4));
        $tiles = new TestTiles(new RecordedHttpClient(PlainTiles::answer()));
        $snapshots = new Snapshots($tiles, new Signer('c2VjcmV0'), new SnapshotStore(new DirectoryStorage($dir), 'google'));
        $printer = new MapPrinter($snapshots, new MapHtml(['class' => 'google-tilemap', 'suffix' => '_html2canvas', 'src' => '/google/{signature}/{id}.webp']));
        $view = new MapView(new LatLng(35, 135), 6, controls: false, id: 'myMap');
        $signature = $snapshots->signature($view);
        $page = '{{ omnimap(view) }}|{{ omnimap_export(view, {class: "item gm-export", text: "<i>up</i>"}) }}|{{ omnimap_suppress(view, {text: "x"}) }}|{{ omnimap(null) }}';

        $visitor = self::twig($printer, false);
        self::assertSame((new MapHtml(['class' => 'google-tilemap', 'suffix' => '_html2canvas', 'src' => '/google/{signature}/{id}.webp']))->snapshot('myMap', $signature, null, '&copy; Test tiles').'|||', $visitor->createTemplate($page)->render(['view' => $view]));

        $admin = self::twig($printer, true);
        $html = $admin->createTemplate($page)->render(['view' => $view]);
        self::assertStringStartsWith('<div id="myMap" data-omnimap="map" data-omnimap-description="', $html, 'no picture yet: the live map');
        self::assertStringContainsString('|<button class="item gm-export" type="button" data-omnimap-export="/google/maps/'.$signature.'/export" data-omnimap-target="myMap" data-omnimap-token="token-of-omnimap-export"><i>up</i></button>||', $html, 'nothing to suppress yet');

        $snapshots->save($signature, self::png());
        $html = $admin->createTemplate($page)->render(['view' => $view]);
        self::assertStringContainsString('class="google-tilemap" id="myMap_html2canvas"', $html);
        self::assertStringContainsString('data-omnimap-suppress="/google/maps/'.$signature.'/suppress"', $html);
        self::assertStringContainsString('data-omnimap-token="token-of-omnimap-suppress">x</button>', $html);
        self::assertSame($signature, $admin->createTemplate('{{ omnimap_signature(view) }}')->render(['view' => $view]));
        $snapshots->delete($signature);
        @rmdir("$dir/google");
        @rmdir($dir);
    }

    private static function twig(MapPrinter $printer, bool $admin): Environment
    {
        $twig = new Environment(new ArrayLoader());
        $twig->addExtension(new MapExtension($printer, new class implements Urls {
            public function path(string $name, array $parameters = []): string
            {
                $signature = $parameters['signature'] ?? '';

                return '/google/maps/'.(\is_string($signature) ? $signature : '').'/'.substr($name, \strlen('gm_'));
            }

            public function url(string $name, array $parameters = []): string
            {
                return 'https://example.org'.$this->path($name, $parameters);
            }

            public function asset(string $path): string
            {
                return $path;
            }
        }, new class($admin) implements Access {
            public function __construct(private readonly bool $admin)
            {
            }

            public function isGranted(mixed $attribute, mixed $subject = null): bool
            {
                return $this->admin && 'ROLE_ADMIN' === $attribute;
            }

            public function getUser(): ?object
            {
                return null;
            }
        }, new class implements Tokens {
            public function csrf(string $intent): string
            {
                return 'token-of-'.$intent;
            }

            public function isValid(string $intent, ?string $token): bool
            {
                return $token === $this->csrf($intent);
            }
        }, routes: ['export' => 'gm_export', 'suppress' => 'gm_suppress']));

        return $twig;
    }

    private static function png(): string
    {
        $image = imagecreatetruecolor(600, 400);
        ob_start();
        imagepng($image);

        return (string) ob_get_clean();
    }
}

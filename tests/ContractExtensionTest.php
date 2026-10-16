<?php

namespace Omnibridge\Twig\Tests;

use Omnibridge\Twig\Contract\Translations;
use Omnibridge\Twig\Contract\Urls;
use Omnibridge\Twig\ContractExtension;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class ContractExtensionTest extends TestCase
{
    public function testAPageUsesTheContractUnderSymfonysNames(): void
    {
        $twig = new Environment(new ArrayLoader([
            'page' => '<a href="{{ path("article", {slug: "fuji"}) }}">{{ "thread.readingTime"|trans([3], "messages") }}</a><link href="{{ asset("screen/screen.css") }}">',
        ]));
        $twig->addExtension(new ContractExtension(
            new class implements Urls {
                public function path(string $name, array $parameters = []): string { return '/'.$name.'/'.implode('/', $parameters); }
                public function asset(string $path): string { return '/'.$path; }
            },
            new class implements Translations {
                public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string { return $domain.':'.$id.':'.implode(',', $parameters); }
            },
        ));

        self::assertSame('<a href="/article/fuji">messages:thread.readingTime:3</a><link href="/screen/screen.css">', $twig->render('page'));
    }
}

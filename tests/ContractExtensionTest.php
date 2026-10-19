<?php

namespace Omnibridge\Twig\Tests;

use Omnibridge\Contract\Translations;
use Omnibridge\Contract\Urls;
use Omnibridge\Twig\ContractExtension;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class ContractExtensionTest extends TestCase
{
    public function testAPageUsesTheContractUnderSymfonysNames(): void
    {
        $twig = new Environment(new ArrayLoader([
            'page' => '<a href="{{ path("article", {slug: "fuji"}) }}">{{ "thread.readingTime"|trans([3], "messages") }}</a><link href="{{ asset("screen/screen.css") }}"><link rel="canonical" href="{{ url("article", {slug: "fuji"}) }}">',
        ]));
        $twig->addExtension(new ContractExtension(
            new class implements Urls {
                public function path(string $name, array $parameters = []): string { return '/'.$name.'/'.implode('/', $parameters); }
                public function url(string $name, array $parameters = []): string { return 'https://site.test'.$this->path($name, $parameters); }
                public function asset(string $path): string { return '/'.$path; }
            },
            new class implements Translations {
                public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string { return $domain.':'.$id.':'.implode(',', $parameters); }
                public function exists(string $id, ?string $domain = null, ?string $locale = null): bool { return true; }
            },
        ));

        self::assertSame('<a href="/article/fuji">messages:thread.readingTime:3</a><link href="/screen/screen.css"><link rel="canonical" href="https://site.test/article/fuji">', $twig->render('page'));
    }

    /**
     * The former names, Omnibridge\Twig\Contract\Urls and Translations, are
     * omniscient/omnibridge's contracts: aliases declared with the autoloader
     * (an instanceof holds before anything asks for them), the same reflection
     * name, and an object made for either name is taken by the extension.
     */
    public function testTheFormerNamesAreTheFamilysContracts(): void
    {
        self::assertTrue(interface_exists('Omnibridge\Twig\Contract\Urls', false), 'declared with the autoloader, not on demand');
        self::assertTrue(interface_exists('Omnibridge\Twig\Contract\Translations', false));
        self::assertSame(Urls::class, (new \ReflectionClass('Omnibridge\Twig\Contract\Urls'))->getName());
        self::assertSame(Translations::class, (new \ReflectionClass('Omnibridge\Twig\Contract\Translations'))->getName());

        $urls = new class implements \Omnibridge\Twig\Contract\Urls {
            public function path(string $name, array $parameters = []): string { return '/'.$name; }
            public function url(string $name, array $parameters = []): string { return 'https://site.test/'.$name; }
            public function asset(string $path): string { return '/'.$path; }
        };
        self::assertInstanceOf(Urls::class, $urls);

        $twig = new Environment(new ArrayLoader(['page' => '{{ path("blog") }}']));
        $twig->addExtension(new ContractExtension($urls, $this->createStub(\Omnibridge\Twig\Contract\Translations::class)));
        self::assertSame('/blog', $twig->render('page'));
    }
}

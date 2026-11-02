<?php

namespace Omnibridge\Twig\Tests;

use Omnibridge\Contract\Views;
use Omnibridge\Contract\ViewSources;
use Omnibridge\Twig\TwigViews;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;

final class TwigViewsTest extends TestCase
{
    public function testAViewIsATemplateByItsName(): void
    {
        $twig = new Environment(new ArrayLoader(['hello.html.twig' => 'Hello {{ name }}{{ suffix }}']));
        $twig->addGlobal('suffix', '!');
        $views = new TwigViews($twig);

        self::assertInstanceOf(Views::class, $views);
        self::assertSame('Hello Ringo!', $views->render('hello.html.twig', ['name' => 'Ringo']));
        self::assertTrue($views->exists('hello.html.twig'));
        self::assertFalse($views->exists('missing.html.twig'));
        self::assertSame($twig, $views->environment());

        $this->expectException(LoaderError::class);
        $views->render('missing.html.twig');
    }

    public function testTheEnvironmentIsAskedWhenFirstNeeded(): void
    {
        $calls = 0;
        $views = new TwigViews(static function () use (&$calls): Environment {
            ++$calls;

            return new Environment(new ArrayLoader(['a' => 'A']));
        });
        self::assertSame(0, $calls);
        self::assertSame('A', $views->render('a'));
        self::assertSame('A', $views->render('a'));
        self::assertSame(1, $calls);
    }

    public function testTheSourcesOfTheTemplatesPaths(): void
    {
        $dir = sys_get_temp_dir().'/omnibridge-twig-views-'.bin2hex(random_bytes(4));
        mkdir($dir);
        file_put_contents($dir.'/logo.svg', '<svg/>');
        try {
            $filesystem = new FilesystemLoader();
            $filesystem->addPath($dir, 'Public');
            $views = new TwigViews(new Environment(new ChainLoader([new ArrayLoader(['inline' => 'held in memory']), $filesystem])));

            self::assertInstanceOf(ViewSources::class, $views);
            self::assertSame(realpath($dir.'/logo.svg'), realpath((string) $views->sourcePath('@Public/logo.svg')));
            self::assertSame('<svg/>', $views->sourceCode('@Public/logo.svg'));
            self::assertNull($views->sourcePath('inline'));
            self::assertSame('held in memory', $views->sourceCode('inline'));
        } finally {
            unlink($dir.'/logo.svg');
            rmdir($dir);
        }
    }
}

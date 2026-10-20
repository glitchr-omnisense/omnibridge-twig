<?php

namespace Omnibridge\Twig\Tests;

use Omnibridge\Contract\Access;
use Omnibridge\Contract\Tokens;
use Omnibridge\Twig\AccessExtension;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Loader\ArrayLoader;
use Twig\TwigFunction;

final class AccessExtensionTest extends TestCase
{
    private static function access(): Access
    {
        return new class implements Access {
            public function isGranted(mixed $attribute, mixed $subject = null): bool
            {
                return 'ROLE_USER' === $attribute || ('edit' === $attribute && 'mine' === $subject);
            }

            public function getUser(): ?object
            {
                return null;
            }
        };
    }

    private static function tokens(): Tokens
    {
        return new class implements Tokens {
            public function csrf(string $intent): string
            {
                return 'token-of-'.$intent;
            }

            public function isValid(string $intent, ?string $token): bool
            {
                return $token === $this->csrf($intent);
            }
        };
    }

    public function testAPageAsksTheHostsContracts(): void
    {
        $twig = new Environment(new ArrayLoader([
            'page' => '{{ is_granted("ROLE_USER") ? "user" : "-" }} {{ is_granted("ROLE_ADMIN") ? "admin" : "-" }} {{ is_granted("edit", "mine") ? "edit" : "-" }} {{ is_granted("edit", "theirs") ? "edit" : "-" }} <a href="/logout?_csrf_token={{ csrf_token("logout") }}"><input value="{{ csrf_token("subscriber") }}">',
        ]));
        $twig->addExtension(new AccessExtension(self::access(), self::tokens()));

        self::assertSame('user - edit - <a href="/logout?_csrf_token=token-of-logout"><input value="token-of-subscriber">', $twig->render('page'));
    }

    /**
     * Registered after a framework's own functions of those names (Symfony's
     * twig-bridge), it is the one a page calls: Twig keeps the last
     * extension's function of a name.
     */
    public function testItAnswersInPlaceOfTheFrameworksOwnFunctions(): void
    {
        $twig = new Environment(new ArrayLoader(['page' => '{{ is_granted("ROLE_USER") ? "granted" : "refused" }} {{ csrf_token("x") }}']));
        $twig->addExtension(new class extends AbstractExtension {
            public function getFunctions(): array
            {
                return [new TwigFunction('is_granted', static fn () => false), new TwigFunction('csrf_token', static fn () => 'framework')];
            }
        });
        $twig->addExtension(new AccessExtension(self::access(), self::tokens()));

        self::assertSame('granted token-of-x', $twig->render('page'));
    }

    /** A contract the host does not bind leaves its function out: the framework's own, or none. */
    public function testAContractTheHostLacksLeavesItsFunctionOut(): void
    {
        self::assertSame(['csrf_token'], array_map(static fn (TwigFunction $function) => $function->getName(), (new AccessExtension(null, self::tokens()))->getFunctions()));
        self::assertSame(['is_granted'], array_map(static fn (TwigFunction $function) => $function->getName(), (new AccessExtension(self::access()))->getFunctions()));
        self::assertSame([], (new AccessExtension())->getFunctions());
    }
}

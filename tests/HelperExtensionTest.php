<?php

namespace Omnibridge\Twig\Tests;

use Omnibridge\Twig\HelperExtension;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/** omnibase's helpers, as the Symfony site's templates use them, with omnibase's results. */
final class HelperExtensionTest extends TestCase
{
    private function render(string $template, array $context = [], string $locale = 'fr'): string
    {
        $twig = new Environment(new ArrayLoader(['page' => $template]));
        $twig->addExtension(new HelperExtension($locale, 'UTC'));

        return $twig->render('page', $context);
    }

    public function testTheHelpersOfTheSitesTemplates(): void
    {
        self::assertSame('class="transparent loading" data-offline-title="Hors ligne"', $this->render('{{ html_attributes({class: " transparent loading ", "data-offline-title": "Hors ligne"}) }}'));
        self::assertSame('Ruban rose', $this->render('{{ "Ruban rose"|str_shorten(15, 1, "..") }}'), 'short enough: as it is');
        self::assertSame('Covid ou Silver..', $this->render('{{ "Covid ou Silver Week ?"|str_shorten(15, 1, "..") }}'));
        self::assertSame('Été', $this->render('{{ "été"|mb_ucfirst }}'));
        self::assertSame('b', $this->render('{{ ["a", "b", "c"]|second }}'));
        self::assertSame('Articles - Les Ringo Stars', $this->render('{{ ["Articles", null, "", "Les Ringo Stars"]|join_if_exists(" - ") }}'));
        self::assertSame('fr FR GB', $this->render('{{ "fr-FR"|lang }} {{ "fr"|country }} {{ "en_GB"|country }}'));
        self::assertSame('anglais', $this->render('{{ "en-GB"|lang_name }}'));
        self::assertSame("L'été", $this->render('{{ "L&#039;&eacute;t&eacute;"|html_decode|raw }}'));
    }

    public function testDatesInThePagesLanguageAndWordsAsTheyAre(): void
    {
        $date = new \DateTimeImmutable('2022-03-10 05:02:00', new \DateTimeZone('UTC'));

        self::assertSame('Mars 2022', $this->render('{{ date|datetime("MMMM YYYY")|title }}', ['date' => $date]));
        self::assertSame('mars 2022', $this->render('{{ date|datetime("MMMM YYYY") }}', ['date' => $date]));
        self::assertSame('March 2022', $this->render('{{ date|datetime("MMMM YYYY")|title }}', ['date' => $date], 'en'));
        self::assertSame('10 mars 2022 05:02', $this->render('{{ date|datetime("dd MMMM YYYY HH:mm") }}', ['date' => $date]));
        self::assertSame('on 10 march 2022', $this->render('on {{ date|datetime("dd MMMM YYYY") }}', ['date' => $date], 'en'), 'lower case, as omnibase gives it');
        self::assertSame('présent now', $this->render('{{ "Présent"|datetime("MMMM YYYY") }} {{ "Now"|datetime("MMMM YYYY") }}'), 'a string is not read as a date: "now" would be today');
        self::assertSame('MMMM YYYY', $this->render('{{ none|datetime("MMMM YYYY") }}', ['none' => null]), 'none: the pattern, as omnibase answers');
    }

    public function testRecentAndHighlighted(): void
    {
        self::assertSame('yes no', $this->render('{{ recent|less_than("+2 day") ? "yes" : "no" }} {{ old|less_than("+2 day") ? "yes" : "no" }}', [
            'recent' => new \DateTimeImmutable('-1 day'),
            'old' => new \DateTimeImmutable('-3 days'),
        ]));
        self::assertSame('Le <mark class="markdown-word"><mark class="markdown-pattern">Fuji</mark>-san</mark>', $this->render('{{ "Le Fuji-san"|highlight(["fuji"])|raw }}'));
        self::assertSame('Le Fuji', $this->render('{{ "Le Fuji"|highlight([]) }}'));
    }
}

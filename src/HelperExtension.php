<?php

namespace Omnibridge\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * The small helpers omnibase gives the templates of its sites, for a
 * framework rendering those templates without omnibase: the same names and
 * the same results as omnibase's (Resources/Functions.php and
 * FunctionTwigExtension), framework-free. Copied here because two frameworks
 * now need them (plan 0, rule 13); omnibase is to take them from here once it
 * depends on omnibridge/twig. A framework with omnibase does not register
 * this extension.
 */
final class HelperExtension extends AbstractExtension
{
    public const SHORTEN_FRONT = -1;
    public const SHORTEN_MIDDLE = 0;
    public const SHORTEN_BACK = 1;

    public function __construct(private readonly string $locale = 'fr', private readonly ?string $timezone = null)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('html_attributes', self::htmlAttributes(...), ['is_safe' => ['all']]),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('str_shorten', self::shorten(...)),
            new TwigFilter('mb_ucfirst', self::ucfirst(...)),
            new TwigFilter('second', self::second(...)),
            new TwigFilter('join_if_exists', self::joinIfExists(...)),
            new TwigFilter('highlight', self::highlight(...)),
            new TwigFilter('less_than', self::lessThan(...)),
            new TwigFilter('html_decode', static fn (?string $html) => html_entity_decode((string) $html, \ENT_QUOTES | \ENT_HTML5)),
            new TwigFilter('lang', self::lang(...)),
            new TwigFilter('country', self::country(...)),
            new TwigFilter('lang_name', fn (?string $locale) => \Locale::getDisplayLanguage(self::lang($locale), $this->locale)),
            new TwigFilter('datetime', $this->datetime(...)),
        ];
    }

    /** class="a b" data-x="y", the values as given (omnibase's html_attributes()). */
    public static function htmlAttributes(array ...$attributes): string
    {
        $attributes = array_merge(...$attributes);

        return trim(implode(' ', array_map(static fn ($key) => trim((string) $key).'="'.trim((string) ($attributes[$key] ?? '')).'"', array_keys($attributes))));
    }

    /** A text cut to $length characters, the separator where it was cut (omnibase's str_shorten()). */
    public static function shorten(?string $haystack, int $length = 100, int $position = self::SHORTEN_BACK, string $separator = ' [..] '): ?string
    {
        $haystack = trim(strip_tags($haystack ?? ''));
        if ('' === $haystack || 0 === $length) {
            return '' === $haystack ? $haystack : '';
        }
        $count = \strlen($haystack);
        if ($count <= $length + \strlen($separator)) {
            return $haystack;
        }

        return match ($position) {
            self::SHORTEN_FRONT => ltrim($separator).mb_substr($haystack, $count - $length, $length + 1),
            self::SHORTEN_MIDDLE => mb_substr($haystack, 0, intdiv($length, 2)).$separator.mb_substr($haystack, $count - intdiv($length, 2), intdiv($length, 2) + 1),
            default => mb_substr($haystack, 0, $length).rtrim($separator),
        };
    }

    public static function ucfirst(array|string|null $text): array|string
    {
        if (\is_array($text)) {
            return array_map(self::ucfirst(...), $text);
        }

        return mb_strtoupper(mb_substr((string) $text, 0, 1)).mb_substr((string) $text, 1);
    }

    public static function second(iterable|null $list): mixed
    {
        $values = null === $list ? [] : array_values(\is_array($list) ? $list : iterator_to_array($list));

        return $values[1] ?? null;
    }

    public static function joinIfExists(?array $list, string $separator): ?string
    {
        return null === $list ? null : implode($separator, array_filter($list));
    }

    /** The words of $pattern marked in $content (omnibase's highlight, without its excerpt mode). */
    public static function highlight(?string $content, string|array|null $pattern): ?string
    {
        if (!$pattern || !$content) {
            return $content;
        }
        $patterns = array_map(static fn ($p) => preg_quote(strip_tags((string) $p), '/'), (array) $pattern);

        return preg_replace_callback('/([^ ]*)('.implode('|', $patterns).')([^ ]*)/im', static fn (array $m) => '' === $m[2]
            ? $m[0]
            : '<mark class="markdown-word">'.$m[1].'<mark class="markdown-pattern">'.$m[2].'</mark>'.$m[3].'</mark>', $content);
    }

    /** Whether $date is more recent than $diff ("+2 day": less than two days ago). */
    public static function lessThan(mixed $date, mixed $diff): bool
    {
        if (\is_string($date)) {
            $date = new \DateTime($date);
        }
        if ($date instanceof \DateTimeInterface) {
            $date = $date->getTimestamp();
        }
        if (\is_string($diff)) {
            $diff = new \DateTime($diff);
        }
        if ($diff instanceof \DateTimeInterface) {
            $diff = $diff->getTimestamp() - time();
        }

        return time() - (int) $date < $diff;
    }

    /** fr-FR, fr_FR: fr. */
    public static function lang(?string $locale): string
    {
        return strtolower(substr((string) $locale, 0, 2));
    }

    /** en-GB: GB; a bare language, its own country (fr: FR). */
    public static function country(?string $locale): string
    {
        $parts = preg_split('/[-_]/', (string) $locale);

        return strtoupper($parts[1] ?? $parts[0]);
    }

    /** A date in the page's language, by an ICU pattern ("MMMM YYYY": mars 2022); anything else as it is. */
    public function datetime(mixed $date, ?string $pattern = null, ?string $locale = null): mixed
    {
        // A word ("Présent", "Now") stays a word, though strtotime() would read "now" as a date.
        if (\is_string($date) && (!preg_match('/^\d/', $date) || false === strtotime($date))) {
            return $date;
        }
        if (null === $date || '' === $date) {
            return $date;
        }
        $date = $date instanceof \DateTimeInterface ? $date : new \DateTimeImmutable(\is_int($date) ? '@'.$date : $date);
        $formatter = new \IntlDateFormatter($locale ?? $this->locale, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT, $this->timezone ?? $date->getTimezone(), null, $pattern);

        return $formatter->format($date);
    }
}

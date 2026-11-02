<?php

namespace Omnibridge\Twig;

use Omnibridge\Contract\Views;
use Omnibridge\Contract\ViewSources;
use Twig\Environment;
use Twig\Error\LoaderError;

/**
 * omnibridge's Views (and ViewSources) answered by Twig's environment: a
 * view is a template by its name (`@Base/notifier/email.html.twig`), found
 * by the environment's loader, rendered with its extensions and globals.
 * What an application's code asks when it needs markup it does not write
 * itself, whichever host built the environment (TwigBundle's `twig` under
 * Symfony, omnibridge/laravel's under Laravel, one made by hand in plain PHP).
 *
 * Twig's own exceptions pass through as they are (a missing template is
 * Twig's LoaderError): a caller that has a fallback asks exists() first.
 */
final class TwigViews implements Views, ViewSources
{
    /** @param Environment|\Closure(): Environment $twig the environment, or how to get it when first asked (it is built late, its extensions loaded) */
    public function __construct(private Environment|\Closure $twig)
    {
    }

    public function render(string $name, array $context = []): string
    {
        return $this->twig()->render($name, $context);
    }

    public function exists(string $name): bool
    {
        return $this->twig()->getLoader()->exists($name);
    }

    public function sourcePath(string $name): ?string
    {
        $path = $this->twig()->getLoader()->getSourceContext($name)->getPath();

        return '' === $path ? null : $path;
    }

    public function sourceCode(string $name): string
    {
        return $this->twig()->getLoader()->getSourceContext($name)->getCode();
    }

    /** The environment itself, for a caller that needs more than a view by its name. */
    public function environment(): Environment
    {
        return $this->twig();
    }

    private function twig(): Environment
    {
        if ($this->twig instanceof \Closure) {
            $twig = ($this->twig)();
            if (!$twig instanceof Environment) {
                throw new \UnexpectedValueException(sprintf('TwigViews was given a closure that answers a %s, not Twig\'s Environment.', get_debug_type($twig)));
            }
            $this->twig = $twig;
        }

        return $this->twig;
    }
}

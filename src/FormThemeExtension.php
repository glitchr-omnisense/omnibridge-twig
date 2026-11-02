<?php

namespace Omnibridge\Twig;

use Omnibridge\Contract\Translations;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * The generic form theme for a host without Symfony Form (omnibase's plan "omnibase agnostic", step
 * F5b): form(), form_start(), form_widget(), form_row(), form_label(), form_help(), form_errors(),
 * form_rest() and form_end() on a neutral form view - any object with public $vars, $children and
 * $rendered, omnisense/omnibase's Base\Form\Definition\FormView - from the blocks of
 * templates/form/theme.html.twig, omnibase's markup. Not for a Symfony site: Symfony's own functions
 * have these names, and its forms its own theme.
 *
 * The host registers this extension and the theme's folder, with register() or by hand.
 */
final class FormThemeExtension extends AbstractExtension
{
    public const THEME = '@OmnibridgeForm/theme.html.twig';

    public function __construct(private readonly ?Translations $translations = null)
    {
    }

    /** Registers the extension and the theme's folder on an environment whose loader is a filesystem one. */
    public static function register(Environment $twig, ?Translations $translations = null): void
    {
        $twig->addExtension(new self($translations));
        $loader = $twig->getLoader();
        if ($loader instanceof FilesystemLoader) {
            $loader->addPath(self::path(), 'OmnibridgeForm');
        }
    }

    public static function path(): string
    {
        return \dirname(__DIR__).'/templates/form';
    }

    public function getFunctions(): array
    {
        $functions = [];
        foreach (['form' => 'form', 'form_start' => 'form_start', 'form_widget' => 'form_widget', 'form_row' => 'form_row', 'form_label' => 'form_label', 'form_help' => 'form_help', 'form_errors' => 'form_errors', 'form_rest' => 'form_rest', 'form_end' => 'form_end'] as $name => $block) {
            $functions[] = new TwigFunction($name, fn (Environment $twig, object $form, array $options = []): string => $this->block($twig, $block, $form, $options), ['needs_environment' => true, 'is_safe' => ['html']]);
        }

        return $functions;
    }

    public function getFilters(): array
    {
        return [new TwigFilter('form_trans', $this->translate(...))];
    }

    public function translate(mixed $text, ?string $domain = null): string
    {
        $text = (string) $text;

        return null !== $this->translations && '' !== $text ? $this->translations->trans($text, [], $domain) : $text;
    }

    /** @param array<string, mixed> $options */
    private function block(Environment $twig, string $block, object $form, array $options): string
    {
        $attr = array_replace($form->vars['attr'] ?? [], $options['attr'] ?? []);
        $vars = array_replace($form->vars, array_diff_key($options, ['attr' => true]));
        $html = $twig->load(self::THEME)->renderBlock($block, ['form' => $form, 'vars' => $vars, 'attr' => $attr]);
        if ('form_widget' === $block || 'form_row' === $block) {
            $form->rendered = true;
        }

        return $html;
    }
}

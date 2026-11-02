<?php

namespace Omnibridge\Twig\Tests;

use Omnibridge\Contract\Translations;
use Omnibridge\Twig\FormThemeExtension;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;

final class FormThemeExtensionTest extends TestCase
{
    private function render(string $template, object $form): string
    {
        $files = new FilesystemLoader();
        $twig = new Environment(new ChainLoader([new ArrayLoader(['page' => $template]), $files]), ['autoescape' => 'html', 'strict_variables' => false]);
        $files->addPath(FormThemeExtension::path(), 'OmnibridgeForm');
        $twig->addExtension(new FormThemeExtension(new class implements Translations {
            public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
            {
                return 'tr:'.$id;
            }

            public function exists(string $id, ?string $domain = null, ?string $locale = null): bool
            {
                return true;
            }
        }));

        return $twig->render('page', ['form' => $form]);
    }

    private function form(): View
    {
        $form = new View('comment', ['type' => 'form', 'method' => 'POST', 'action' => '/send', 'errors' => ['Token.']]);
        $form->add('content', ['type' => 'textarea', 'value' => '<b>', 'label' => 'comment.content', 'required' => true, 'errors' => ['Too short.']]);
        $form->add('email', ['type' => 'email', 'value' => 'a@b.c', 'attr' => ['placeholder' => 'mail']]);
        $form->add('notify', ['type' => 'checkbox', 'value' => '1', 'checked' => true]);
        $form->add('room', ['type' => 'choice', 'value' => 'l', 'choices' => [['value' => 's', 'label' => 'Small'], ['value' => 'l', 'label' => 'Large']], 'placeholder' => 'pick']);
        $form->add('submit', ['type' => 'submit', 'label' => 'go']);
        $form->add('_csrf_token', ['type' => 'hidden', 'value' => 'tok']);

        return $form;
    }

    public function testAWholeForm(): void
    {
        $html = $this->render('{{ form(form) }}', $this->form());

        self::assertStringContainsString('<form name="comment" method="post" action="/send" class="needs-validation" novalidate="novalidate">', $html);
        self::assertStringContainsString('<li><span>Token.</span></li>', $html);
        self::assertStringContainsString('<label for="comment_content" class="required">tr:comment.content</label>', $html);
        self::assertStringContainsString('<textarea id="comment_content" name="comment[content]" required="required" class="form-control is-invalid">&lt;b&gt;</textarea>', $html);
        self::assertStringContainsString('<li><span>Too short.</span></li>', $html);
        self::assertStringContainsString('<input type="email" id="comment_email" name="comment[email]" value="a@b.c" placeholder="tr:mail" class="form-control" />', $html);
        self::assertStringContainsString('checked="checked"', $html);
        self::assertStringContainsString('<option value="l" selected="selected">tr:Large</option>', $html);
        self::assertStringContainsString('<option value="">tr:pick</option>', $html);
        self::assertStringContainsString('<button type="submit" id="comment_submit" name="comment[submit]">tr:go</button>', $html);
        self::assertStringContainsString('<input type="hidden" id="comment__csrf_token" name="comment[_csrf_token]" value="tok" />', $html);
        self::assertSame(1, substr_count($html, 'comment[email]'), 'each field once');
        self::assertStringEndsWith('</form>', trim($html));
    }

    public function testFieldByFieldThenTheRest(): void
    {
        $html = $this->render('{{ form_start(form) }}{{ form_row(form.children.email) }}{{ form_end(form) }}', $this->form());

        self::assertSame(1, substr_count($html, 'comment[email]'));
        self::assertStringContainsString('comment[content]', $html, 'the rest');
        self::assertStringContainsString('comment[_csrf_token]', $html);
    }
}

final class View
{
    /** @var array<string, mixed> */
    public array $vars;

    /** @var array<string, View> */
    public array $children = [];

    public bool $rendered = false;

    /** @param array<string, mixed> $vars */
    public function __construct(string $name, array $vars, public readonly ?View $parent = null)
    {
        $this->vars = $vars + ['name' => $name, 'full_name' => $parent ? $parent->vars['full_name'].'['.$name.']' : $name, 'id' => $parent ? $parent->vars['id'].'_'.$name : $name, 'attr' => [], 'errors' => [], 'required' => false, 'disabled' => false, 'label' => null, 'help' => null, 'multiple' => false, 'expanded' => false, 'translation_domain' => null];
    }

    /** @param array<string, mixed> $vars */
    public function add(string $name, array $vars): void
    {
        $this->children[$name] = new self($name, $vars, $this);
    }

    public function isSelected(string $value): bool
    {
        return (string) $this->vars['value'] === $value;
    }
}

<?php

namespace Omnibridge\Twig;

use Omnibridge\Contract\Access;
use Omnibridge\Contract\Tokens;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * What a page asks of the visitor's host: whether the visitor may do
 * something, `is_granted(attribute, subject)` (omnisense/omnibridge's
 * Access), and the token a form of theirs carries against cross-site
 * requests, `csrf_token(intent)` (Tokens) - under the names Symfony gives
 * them, answered by the host's adapters.
 *
 * Registered by every host, Symfony included (omnibridge/symfony, after
 * twig-bridge's SecurityExtension and CsrfExtension, whose functions it then
 * answers with the same services), so that a page asks the same code
 * whichever framework renders it. A contract the host does not bind leaves
 * its function out (a Symfony application without SecurityBundle or without
 * CSRF protection).
 */
final class AccessExtension extends AbstractExtension
{
    public function __construct(
        private readonly ?Access $access = null,
        private readonly ?Tokens $tokens = null,
    ) {
    }

    public function getFunctions(): array
    {
        $functions = [];
        if (null !== $this->access) {
            $functions[] = new TwigFunction('is_granted', $this->access->isGranted(...));
        }
        if (null !== $this->tokens) {
            $functions[] = new TwigFunction('csrf_token', $this->tokens->csrf(...));
        }

        return $functions;
    }
}

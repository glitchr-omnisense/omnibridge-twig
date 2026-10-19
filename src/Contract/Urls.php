<?php

/*
 * Omnibridge\Twig\Contract\Urls is omniscient/omnibridge's Omnibridge\Contract\Urls
 * since the contracts moved to the family's core (plan "omnibase agnostique", A2):
 * the same interface under its former name, an alias, not a subclass. Declared
 * when Composer's autoloader is loaded (composer.json, autoload.files), so a type
 * or an instanceof naming it holds before anything asks for it. To be removed at
 * omnibase 4's final pass.
 */

Omnibridge\Runtime::alias(Omnibridge\Contract\Urls::class, 'Omnibridge\Twig\Contract\Urls');

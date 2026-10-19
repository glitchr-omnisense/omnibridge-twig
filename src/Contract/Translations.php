<?php

/*
 * Omnibridge\Twig\Contract\Translations is omniscient/omnibridge's
 * Omnibridge\Contract\Translations since the contracts moved to the family's core
 * (plan "omnibase agnostique", A2): the same interface under its former name, an
 * alias, not a subclass - which asks exists() besides trans(). Declared when
 * Composer's autoloader is loaded (composer.json, autoload.files). To be removed
 * at omnibase 4's final pass.
 */

class_alias(Omnibridge\Contract\Translations::class, 'Omnibridge\Twig\Contract\Translations');

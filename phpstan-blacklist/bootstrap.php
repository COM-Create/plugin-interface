<?php

declare(strict_types=1);

# Loads the blacklist rule classes without Composer autoloading. This fork has no
# composer.json (neither does a Plenty plugin analysed without Composer), so a plain
# bootstrapFiles require is the only option PHPStan gives us. See blacklist.neon.

require_once __DIR__ . '/Blacklist.php';
require_once __DIR__ . '/rules/DisallowedFunctionCallRule.php';
require_once __DIR__ . '/rules/DisallowedVariableFunctionCallRule.php';
require_once __DIR__ . '/rules/DisallowedNewRule.php';
require_once __DIR__ . '/rules/DisallowedStaticCallRule.php';
require_once __DIR__ . '/rules/DynamicPropertyFetchRule.php';
require_once __DIR__ . '/rules/DynamicMethodCallRule.php';

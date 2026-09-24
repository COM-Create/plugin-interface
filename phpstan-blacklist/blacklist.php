<?php

declare(strict_types=1);

# Include this file from a Plenty plugin's phpstan.neon (or the machine-local
# phpstan.local.neon that includes it) to get the Plenty-build blacklist rules:
#
#     includes:
#         - /path/to/deps/plugin-interface/phpstan-blacklist/blacklist.php
#
# Why a .php config file and not a .neon one: this fork has no composer.json (a
# Plenty plugin analysed without Composer wouldn't have autoloading either), so the
# rule classes below must be loaded with a plain `require`. PHPStan builds its
# dependency-injection container - and resolves every class listed under `rules:` -
# BEFORE it runs `parameters.bootstrapFiles`, so bootstrapFiles alone is too late for
# this. A .php config file is `include`d while the config is being read, i.e. before
# that container is built, so requiring the rule classes here actually works.
#
# This file intentionally sets no `paths`, `level` or `excludePaths` - the consuming
# plugin keeps full control over what gets analysed. It only makes the rule classes
# loadable and registers them.
#
# See phpstan-blacklist/README.md for what is covered, what "Faustregel" entries
# mean, and the maintenance rule for adding a newly confirmed build error.

require_once __DIR__ . '/bootstrap.php';

return [
    'rules' => [
        ComCreate\PluginInterface\PhpstanBlacklist\Rules\DisallowedFunctionCallRule::class,
        ComCreate\PluginInterface\PhpstanBlacklist\Rules\DisallowedVariableFunctionCallRule::class,
        ComCreate\PluginInterface\PhpstanBlacklist\Rules\DisallowedNewRule::class,
        ComCreate\PluginInterface\PhpstanBlacklist\Rules\DisallowedStaticCallRule::class,
        ComCreate\PluginInterface\PhpstanBlacklist\Rules\DynamicPropertyFetchRule::class,
        ComCreate\PluginInterface\PhpstanBlacklist\Rules\DynamicMethodCallRule::class,
    ],
];

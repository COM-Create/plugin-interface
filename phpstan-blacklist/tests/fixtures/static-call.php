<?php

declare(strict_types=1);

# Own dummy class matching the "Reflection*" blacklist prefix, so this fixture does
# not depend on PHP's real Reflection API surface (which changes between PHP versions).
class ReflectionDemoStaticHelper
{
    public static function run(): void
    {
    }
}

# Fixture: static call on a blacklisted class. Must trigger
# comcreate.plentyBlacklist.class (DisallowedStaticCallRule).
function demoStaticCall(): void
{
    ReflectionDemoStaticHelper::run();
}

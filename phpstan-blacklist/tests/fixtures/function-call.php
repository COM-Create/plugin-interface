<?php

declare(strict_types=1);

# Fixture: a statically named, blacklisted function call. Must trigger
# comcreate.plentyBlacklist.function (DisallowedFunctionCallRule).

function demoFunctionCall(): string
{
    return iconv('UTF-8', 'ASCII//TRANSLIT', 'test');
}

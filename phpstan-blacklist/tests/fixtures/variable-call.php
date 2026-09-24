<?php

declare(strict_types=1);

# Fixture: calling a function via a variable. Must trigger
# comcreate.plentyBlacklist.variableCall (DisallowedVariableFunctionCallRule).

function demoVariableCall(): void
{
    $fn = 'strtoupper';
    $fn('test');
}

<?php

declare(strict_types=1);

class DemoMethodHolder
{
    public function value(): string
    {
        return 'x';
    }
}

# Fixture: dynamic method name ($obj->$name()). Must trigger
# comcreate.plentyBlacklist.dynamicMethod (DynamicMethodCallRule).
function demoDynamicMethod(): string
{
    $obj = new DemoMethodHolder();
    $name = 'value';

    return $obj->$name();
}

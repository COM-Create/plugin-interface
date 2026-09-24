<?php

declare(strict_types=1);

class DemoPropertyHolder
{
    public string $value = 'x';
}

# Fixture: dynamic property name ($obj->{$name}). Must trigger
# comcreate.plentyBlacklist.dynamicProperty (DynamicPropertyFetchRule).
function demoDynamicProperty(): string
{
    $obj = new DemoPropertyHolder();
    $name = 'value';

    return $obj->{$name};
}

<?php

declare(strict_types=1);

# This comment mentions iconv(), ReflectionClass, ctype_digit(), class_exists() and
# even $obj->{$name} on purpose. PHPStan works on the AST, not on text, so none of
# this must trigger a blacklist rule. This fixture must stay error-free.

function demoCleanFile(): string
{
    $message = 'Do not call iconv(), new ReflectionClass(), ctype_digit(), '
        . 'class_exists(), or $obj->$name() here - this is just a string.';

    return $message;
}

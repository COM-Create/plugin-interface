<?php

declare(strict_types=1);

namespace ComCreate\PluginInterface\PhpstanBlacklist\Rules;

use ComCreate\PluginInterface\PhpstanBlacklist\Blacklist;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Flags a dynamic method name, e.g. `$obj->{$name}()` or `$obj->$name()`.
 *
 * @implements Rule<MethodCall>
 */
final class DynamicMethodCallRule implements Rule
{
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof MethodCall || $node->name instanceof Node\Identifier) {
            # A static method name ($obj->method()) is fine; only a dynamic one is blacklisted.
            return [];
        }

        $entry = Blacklist::singleEntry('dynamic_method');

        if ($entry === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(Blacklist::formatMessage('Dynamischer Methodenname ($obj->$name())', $entry))
                ->identifier('comcreate.plentyBlacklist.dynamicMethod')
                ->build(),
        ];
    }
}

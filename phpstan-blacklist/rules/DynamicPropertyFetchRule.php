<?php

declare(strict_types=1);

namespace ComCreate\PluginInterface\PhpstanBlacklist\Rules;

use ComCreate\PluginInterface\PhpstanBlacklist\Blacklist;
use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Flags a dynamic property name, e.g. `$obj->{$name}` or `$obj->$name`.
 *
 * @implements Rule<PropertyFetch>
 */
final class DynamicPropertyFetchRule implements Rule
{
    public function getNodeType(): string
    {
        return PropertyFetch::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof PropertyFetch || $node->name instanceof Node\Identifier) {
            # A static property name ($obj->name) is fine; only a dynamic one is blacklisted.
            return [];
        }

        $entry = Blacklist::singleEntry('dynamic_property');

        if ($entry === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(Blacklist::formatMessage('Dynamischer Property-Name ($obj->{$name})', $entry))
                ->identifier('comcreate.plentyBlacklist.dynamicProperty')
                ->build(),
        ];
    }
}

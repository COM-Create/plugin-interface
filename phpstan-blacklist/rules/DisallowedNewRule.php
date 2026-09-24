<?php

declare(strict_types=1);

namespace ComCreate\PluginInterface\PhpstanBlacklist\Rules;

use ComCreate\PluginInterface\PhpstanBlacklist\Blacklist;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Flags `new` on a blacklisted class (e.g. `new ReflectionClass(...)`).
 *
 * @implements Rule<New_>
 */
final class DisallowedNewRule implements Rule
{
    public function getNodeType(): string
    {
        return New_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof New_ || !$node->class instanceof Node\Name) {
            return [];
        }

        $name = $node->class->toString();
        $entry = Blacklist::matchClass($name);

        if ($entry === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(Blacklist::formatMessage("Klasse {$name} (new)", $entry))
                ->identifier('comcreate.plentyBlacklist.class')
                ->build(),
        ];
    }
}

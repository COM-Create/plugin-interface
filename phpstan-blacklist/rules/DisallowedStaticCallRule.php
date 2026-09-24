<?php

declare(strict_types=1);

namespace ComCreate\PluginInterface\PhpstanBlacklist\Rules;

use ComCreate\PluginInterface\PhpstanBlacklist\Blacklist;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Flags a static call on a blacklisted class (e.g. `ReflectionMethod::export(...)`).
 *
 * @implements Rule<StaticCall>
 */
final class DisallowedStaticCallRule implements Rule
{
    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof StaticCall || !$node->class instanceof Node\Name) {
            return [];
        }

        $name = $node->class->toString();
        $entry = Blacklist::matchClass($name);

        if ($entry === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(Blacklist::formatMessage("Klasse {$name} (statischer Aufruf)", $entry))
                ->identifier('comcreate.plentyBlacklist.class')
                ->build(),
        ];
    }
}

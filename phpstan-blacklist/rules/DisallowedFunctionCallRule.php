<?php

declare(strict_types=1);

namespace ComCreate\PluginInterface\PhpstanBlacklist\Rules;

use ComCreate\PluginInterface\PhpstanBlacklist\Blacklist;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Flags calls to a statically named, blacklisted PHP function (e.g. iconv(), class_exists()).
 *
 * @implements Rule<FuncCall>
 */
final class DisallowedFunctionCallRule implements Rule
{
    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof FuncCall || !$node->name instanceof Node\Name) {
            # Dynamic calls ($fn()) are handled by DisallowedVariableFunctionCallRule.
            return [];
        }

        $name = $node->name->toString();
        $entry = Blacklist::matchFunction($name);

        if ($entry === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(Blacklist::formatMessage("Funktion {$name}()", $entry))
                ->identifier('comcreate.plentyBlacklist.function')
                ->build(),
        ];
    }
}

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
 * Flags calling a function via a variable ($fn()) or any other expression, e.g. (...)().
 * The Plenty build resolves this at runtime and rejects almost every target.
 *
 * @implements Rule<FuncCall>
 */
final class DisallowedVariableFunctionCallRule implements Rule
{
    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof FuncCall || $node->name instanceof Node\Name) {
            # Statically named calls are handled by DisallowedFunctionCallRule.
            return [];
        }

        $entry = Blacklist::singleEntry('variable_call');

        if ($entry === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(Blacklist::formatMessage('Funktionsaufruf ueber eine Variable/einen Ausdruck', $entry))
                ->identifier('comcreate.plentyBlacklist.variableCall')
                ->build(),
        ];
    }
}

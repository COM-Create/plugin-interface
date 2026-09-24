<?php

declare(strict_types=1);

# Verifies the blacklist rules against the fixtures in fixtures/, without PHPUnit or
# PHPStan's RuleTestCase (this fork has no Composer autoloading for PHPStan's own
# internal test framework classes - see phpstan-blacklist/README.md).
#
# Usage: php phpstan-blacklist/tests/run-tests.php [path-to-phpstan-binary]
# Default binary: ~/.config/composer/vendor/bin/phpstan (global PHPStan install)

$root = __DIR__;
$phpstanBin = $argv[1] ?? (getenv('HOME') . '/.config/composer/vendor/bin/phpstan');

if (!is_file($phpstanBin)) {
    fwrite(STDERR, "PHPStan binary not found: {$phpstanBin}\n");
    exit(1);
}

$cmd = escapeshellarg($phpstanBin) . ' analyse -c ' . escapeshellarg($root . '/phpstan-test.neon')
    . ' --error-format=json --no-progress 2>' . escapeshellarg($root . '/run-tests.stderr.log');

exec($cmd, $outputLines, $exitCode);
$json = json_decode(implode("\n", $outputLines), true);

if (!is_array($json) || !isset($json['files'])) {
    fwrite(STDERR, "Could not parse phpstan JSON output (exit {$exitCode}):\n" . implode("\n", $outputLines) . "\n");
    exit(1);
}

# file => expected rule identifier
$expected = [
    'function-call.php' => 'comcreate.plentyBlacklist.function',
    'variable-call.php' => 'comcreate.plentyBlacklist.variableCall',
    'new-class.php' => 'comcreate.plentyBlacklist.class',
    'static-call.php' => 'comcreate.plentyBlacklist.class',
    'dynamic-property.php' => 'comcreate.plentyBlacklist.dynamicProperty',
    'dynamic-method.php' => 'comcreate.plentyBlacklist.dynamicMethod',
];

$failures = [];

foreach ($expected as $file => $identifier) {
    $path = $root . '/fixtures/' . $file;
    $messages = array_column($json['files'][$path]['messages'] ?? [], 'identifier');
    if (!in_array($identifier, $messages, true)) {
        $failures[] = "{$file}: expected identifier '{$identifier}' not reported (got: " . implode(', ', $messages) . ')';
    }
}

$cleanPath = $root . '/fixtures/allowed-comments-and-strings.php';
$cleanMessages = $json['files'][$cleanPath]['messages'] ?? [];
$blacklistHits = array_filter(
    $cleanMessages,
    static function (array $m): bool {
        return str_starts_with($m['identifier'] ?? '', 'comcreate.plentyBlacklist.');
    }
);
if ($blacklistHits !== []) {
    $failures[] = 'allowed-comments-and-strings.php: expected no blacklist hits, got ' . json_encode(array_values($blacklistHits));
}

@unlink($root . '/run-tests.stderr.log');

if ($failures !== []) {
    fwrite(STDERR, "FAILED:\n - " . implode("\n - ", $failures) . "\n");
    exit(1);
}

echo 'OK - all ' . count($expected) . " rule fixtures reported, comments/strings fixture stayed clean.\n";
exit(0);

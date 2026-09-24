<?php

declare(strict_types=1);

namespace ComCreate\PluginInterface\PhpstanBlacklist;

# Reads phpstan-blacklist/blacklist-data.php and answers lookups for the rule classes.
# No Composer autoloading is available in this fork, so this class is loaded via a
# plain require in bootstrap.php (see phpstan-blacklist/blacklist.neon).
final class Blacklist
{
    /** @var array<int, array{type: string, pattern: ?string, message: ?string, replacement: string, evidence: string}>|null */
    private static ?array $entries = null;

    /**
     * @return array<int, array{type: string, pattern: ?string, message: ?string, replacement: string, evidence: string}>
     */
    public static function entries(): array
    {
        if (self::$entries === null) {
            self::$entries = require __DIR__ . '/blacklist-data.php';
        }

        return self::$entries;
    }

    /**
     * @return array{type: string, pattern: ?string, message: ?string, replacement: string, evidence: string}|null
     */
    public static function matchFunction(string $name): ?array
    {
        return self::matchByPattern('function', $name);
    }

    /**
     * @return array{type: string, pattern: ?string, message: ?string, replacement: string, evidence: string}|null
     */
    public static function matchClass(string $name): ?array
    {
        return self::matchByPattern('class', $name);
    }

    /**
     * The three construct-level entries (dynamic_property, dynamic_method, variable_call)
     * are not matched by name but by syntax shape, so there is exactly one per type.
     *
     * @return array{type: string, pattern: ?string, message: ?string, replacement: string, evidence: string}|null
     */
    public static function singleEntry(string $type): ?array
    {
        foreach (self::entries() as $entry) {
            if ($entry['type'] === $type) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * @return array{type: string, pattern: ?string, message: ?string, replacement: string, evidence: string}|null
     */
    private static function matchByPattern(string $type, string $name): ?array
    {
        foreach (self::entries() as $entry) {
            if ($entry['type'] !== $type || $entry['pattern'] === null) {
                continue;
            }

            $pattern = $entry['pattern'];

            if (substr($pattern, -1) === '*') {
                $prefix = substr($pattern, 0, -1);
                if (stripos($name, $prefix) === 0) {
                    return $entry;
                }
                continue;
            }

            if (strcasecmp($pattern, $name) === 0) {
                return $entry;
            }
        }

        return null;
    }

    # Shared message builder so every rule reports the same shape:
    # what is forbidden, the Plenty build wording (if one was observed), the
    # replacement, and the reminder that this list is incomplete.
    public static function formatMessage(string $what, array $entry): string
    {
        $parts = ["Plenty-Blacklist: {$what} ist verboten."];

        if ($entry['message'] !== null) {
            $parts[] = "Build-Fehler: \"{$entry['message']}\"";
        } else {
            $parts[] = '(Faustregel, nicht einzeln im Build belegt)';
        }

        $parts[] = "Stattdessen: {$entry['replacement']}.";
        $parts[] = 'Liste ist unvollstaendig, siehe phpstan-blacklist/README.md.';

        return implode(' ', $parts);
    }
}

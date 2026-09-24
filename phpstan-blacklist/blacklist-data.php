<?php

declare(strict_types=1);

# Single source of truth for the PHPStan Plenty-build blacklist rules.
#
# This is a BLACKLIST, not a whitelist: it only lists constructs that have been
# CONFIRMED forbidden by a real Plenty plugin build error. Nobody has the full,
# authoritative Plenty whitelist (Plenty does not actively maintain public docs
# of it), so rebuilding it here would fake a completeness this list does not have.
# Reference material only (not actively maintained by Plenty):
# https://developer.mirakl.com/content/product/mmp/rest/front/openapi3/platform-settings
#
# Every entry needs:
#   type:        'function' | 'class' | 'dynamic_property' | 'dynamic_method' | 'variable_call'
#   pattern:     function/class name, or a trailing '*' prefix wildcard (e.g. 'Reflection*'),
#                or null for the three construct-level types (dynamic_property/dynamic_method/variable_call)
#                which are not named by pattern but by syntax shape
#   message:     the exact wording of the Plenty build error, where one was observed
#   replacement: what to use instead
#   evidence:    a date the build error was observed, or the literal string 'Faustregel'
#                for entries that are a rule of thumb (I/O, network, process, filesystem)
#                and were not individually confirmed one-by-one in a build
#
# Maintenance rule: whoever hits a Plenty build error "... is not allowed" adds it here
# (with the exact wording and the replacement used) as its own PR against this fork's
# phpstan-stubs branch. See phpstan-blacklist/README.md for the full procedure.

return [
    # --- Confirmed via build error (belegt) -------------------------------------------
    [
        'type' => 'dynamic_property',
        'pattern' => null,
        'message' => 'dynamic property names are not allowed',
        'replacement' => 'typisierter Zugriff, notfalls fest verdrahten',
        'evidence' => 'belegt',
    ],
    [
        'type' => 'dynamic_method',
        'pattern' => null,
        'message' => 'dynamic method names are not allowed',
        'replacement' => 'explizite Aufrufe, match',
        'evidence' => 'belegt',
    ],
    [
        'type' => 'variable_call',
        'pattern' => null,
        # Observed wording for the specific case "$fn()" - the Plenty build resolves the
        # variable's value at runtime and rejects it there, so the exact wording varies
        # by target; this is the one confirmed instance.
        'message' => 'php function "fn" is not allowed',
        'replacement' => 'Aufruf ausschreiben',
        'evidence' => 'belegt',
    ],
    [
        'type' => 'function',
        'pattern' => 'get_object_vars',
        'message' => 'php function "get_object_vars" is not allowed',
        'replacement' => 'Property direkt lesen, instanceof-Guard davor',
        'evidence' => 'belegt',
    ],
    [
        'type' => 'function',
        'pattern' => 'ctype_digit',
        'message' => 'php function "ctype_digit" is not allowed',
        'replacement' => "Zeichenvergleich \$c >= '0' && \$c <= '9'",
        'evidence' => 'belegt',
    ],
    [
        'type' => 'function',
        'pattern' => 'mb_strtoupper',
        'message' => 'php function "mb_strtoupper" is not allowed',
        'replacement' => 'strtoupper() (nur ASCII) plus eigene Tabelle',
        'evidence' => 'belegt',
    ],
    [
        'type' => 'function',
        'pattern' => 'mb_strtolower',
        'message' => 'php function "mb_strtolower" is not allowed',
        'replacement' => 'strtolower() (nur ASCII) plus eigene Tabelle',
        'evidence' => 'belegt',
    ],
    [
        'type' => 'function',
        'pattern' => 'iconv',
        'message' => 'php function "iconv" is not allowed',
        'replacement' => 'eigene Umschrifttabelle',
        'evidence' => 'belegt',
    ],
    [
        'type' => 'function',
        'pattern' => 'class_exists',
        'message' => 'php function "class_exists" is not allowed',
        'replacement' => 'try/catch um den Aufruf',
        'evidence' => 'belegt',
    ],
    [
        'type' => 'function',
        'pattern' => 'get_class_methods',
        'message' => 'php function "get_class_methods" is not allowed',
        'replacement' => 'explizit aufrufen',
        'evidence' => 'belegt',
    ],
    [
        'type' => 'class',
        'pattern' => 'Reflection*',
        'message' => 'class "ReflectionClass" is not allowed',
        'replacement' => 'TypeError provozieren',
        'evidence' => 'belegt',
    ],

    # --- Rule of thumb, not individually confirmed one-by-one (Faustregel) -----------
    # I/O, network, process and filesystem access are locked down in Plenty plugins.
    [
        'type' => 'function',
        'pattern' => 'file_get_contents',
        'message' => null,
        'replacement' => 'Plenty Storage oder Guzzle',
        'evidence' => 'Faustregel',
    ],
    [
        'type' => 'function',
        'pattern' => 'file_put_contents',
        'message' => null,
        'replacement' => 'Plenty Storage oder Guzzle',
        'evidence' => 'Faustregel',
    ],
    [
        'type' => 'function',
        'pattern' => 'fopen',
        'message' => null,
        'replacement' => 'Plenty Storage oder Guzzle',
        'evidence' => 'Faustregel',
    ],
    [
        'type' => 'function',
        'pattern' => 'unlink',
        'message' => null,
        'replacement' => 'Plenty Storage',
        'evidence' => 'Faustregel',
    ],
    [
        'type' => 'function',
        'pattern' => 'exec',
        'message' => null,
        'replacement' => 'kein Shell-Zugriff im Plugin moeglich',
        'evidence' => 'Faustregel',
    ],
    [
        'type' => 'function',
        'pattern' => 'shell_exec',
        'message' => null,
        'replacement' => 'kein Shell-Zugriff im Plugin moeglich',
        'evidence' => 'Faustregel',
    ],
    [
        'type' => 'function',
        'pattern' => 'system',
        'message' => null,
        'replacement' => 'kein Shell-Zugriff im Plugin moeglich',
        'evidence' => 'Faustregel',
    ],
    [
        'type' => 'function',
        'pattern' => 'popen',
        'message' => null,
        'replacement' => 'kein Prozess-Zugriff im Plugin moeglich',
        'evidence' => 'Faustregel',
    ],
    [
        'type' => 'function',
        'pattern' => 'proc_open',
        'message' => null,
        'replacement' => 'kein Prozess-Zugriff im Plugin moeglich',
        'evidence' => 'Faustregel',
    ],
    [
        'type' => 'function',
        'pattern' => 'curl_*',
        'message' => null,
        'replacement' => 'Guzzle (Http-Facade); Legacy-Code darf den bestehenden raw-cURL Dispatcher weiter nutzen',
        'evidence' => 'Faustregel',
    ],
    [
        'type' => 'function',
        'pattern' => 'fsockopen',
        'message' => null,
        'replacement' => 'Guzzle',
        'evidence' => 'Faustregel',
    ],
];

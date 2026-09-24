# Plenty-Build-Blacklist (PHPStan)

PlentyONE lässt in Plugins nur gewhitelistete PHP-Funktionen und Sprachkonstrukte zu.
Ein Verstoß fällt **erst im serverseitigen Build** auf — lokal ist der Code fehlerfrei,
PHPStan schweigt, Tests sind grün. Jeder Treffer kostet einen kompletten Build-Zyklus.

Diese Regeln prüfen per PHPStan, ob ein Plugin eine der bereits bekannten Blacklist-
Verletzungen enthält — **bevor** der Build es tut.

**Wichtig — bewusst Blacklist, nicht Whitelist:** Hier stehen nur Dinge, die uns in
einem echten Plenty-Build **belegt** um die Ohren geflogen sind (oder die als klare
Faustregel gelten, z.B. Dateisystem-/Netzwerkzugriff). Die vollständige Plenty-
Whitelist kennt niemand — sie hier nachzubauen würde eine Vollständigkeit vortäuschen,
die nicht existiert.

**Die Liste ist unvollständig.** Jede PHPStan-Meldung dieser Regeln sagt das auch.

Offizielle Plenty-Doku (Ausgangsmaterial, wird von Plenty nicht aktiv gepflegt):
https://developer.mirakl.com/content/product/mmp/rest/front/openapi3/platform-settings

## Einbinden

Dieses Repo (`COM-Create/plugin-interface`, Branch `phpstan-stubs`) hat **kein**
`composer.json` — genau wie ein Plenty-Plugin, das ohne Composer geprüft wird. Die
Regeln laden ihre Klassen deshalb über einen plain `require` statt über Composer-
Autoloading (siehe "Warum `blacklist.php` und keine `.neon`" unten).

In der `phpstan.neon` (oder der maschinenlokalen `phpstan.local.neon`, die die
`phpstan.neon` inkludiert) des Plugins:

```neon
includes:
    - /pfad/zu/plugin-interface/phpstan-blacklist/blacklist.php
```

`blacklist.php` setzt bewusst **keine** `paths`, `level` oder `excludePaths` — das
bleibt beim einbindenden Plugin. Es registriert nur die Regeln.

In Morpheus geschieht das automatisch: `ops/deps.py stanconf` schreibt den lokalen
Entwickler-Pfad zu `plugin-interface*` auf den Server-Checkout `deps/plugin-interface`
um (Regex in `PATH_MAP`).

### Warum `blacklist.php` und keine `.neon`

PHPStan baut seinen Dependency-Injection-Container — und löst jede unter `rules:`
gelistete Klasse auf — **bevor** es `parameters.bootstrapFiles` ausführt. Ohne
Composer-Autoload wären unsere Rule-Klassen an der Stelle noch nicht geladen, und
PHPStan bricht mit `Class '...' not found.` ab.

Eine `.php`-Config-Datei wird dagegen schon beim **Einlesen** der Config per
`include` ausgeführt — also bevor der Container gebaut wird. Ein `require_once` an
dieser Stelle lädt die Klassen rechtzeitig. Deshalb ist `blacklist.php` eine PHP-
Datei, die ein Array zurückgibt (von PHPStan als Config akzeptiert), statt einer
`.neon`.

## Was geprüft wird

| Verboten | Fehlermeldung im Build | Stattdessen |
|---|---|---|
| `$obj->{$name}` (dynamische Property) | `dynamic property names are not allowed` | typisierter Zugriff, notfalls fest verdrahten |
| `$obj->$name()` (dynamischer Methodenname) | `dynamic method names are not allowed` | explizite Aufrufe, `match` |
| `$fn()` (Aufruf über Variable/Ausdruck) | `php function "fn" is not allowed` (Beispiel; Wortlaut hängt vom Ziel ab) | Aufruf ausschreiben |
| `get_object_vars()` | `php function "get_object_vars" is not allowed` | Property direkt lesen, `instanceof`-Guard davor |
| `ctype_digit()` | `php function "ctype_digit" is not allowed` | Zeichenvergleich `$c >= '0' && $c <= '9'` |
| `mb_strtoupper()` / `mb_strtolower()` | `php function "mb_strtoupper" is not allowed` | `strtoupper()`/`strtolower()` (nur ASCII) plus eigene Tabelle |
| `iconv()` | `php function "iconv" is not allowed` | eigene Umschrifttabelle |
| `class_exists()` | `php function "class_exists" is not allowed` | `try`/`catch` um den Aufruf |
| `get_class_methods()` | `php function "get_class_methods" is not allowed` | explizit aufrufen |
| `ReflectionClass`, `ReflectionMethod` (alle `Reflection*`, per `new` und statischem Aufruf) | `class "ReflectionClass" is not allowed` | `TypeError` provozieren |

**Faustregel, nicht einzeln im Build belegt:** I/O, Netzwerk, Prozesse und
Dateisystem sind gesperrt — `file_get_contents`, `file_put_contents`, `fopen`,
`unlink`, `exec`, `shell_exec`, `system`, `popen`, `proc_open`, `curl_*`,
`fsockopen`. Siehe `blacklist-data.php` für den genauen Bestand inkl. Ersatz.

Die maschinenlesbare Quelle ist ausschließlich `blacklist-data.php` — diese Tabelle
wird von Hand synchron gehalten, bei Ergänzungen also beides pflegen.

## Im Build bestätigt erlaubt

Damit niemand funktionierenden Code aus Vorsicht umbaut: `mb_substr()`,
`get_class()`, Arrow Functions (`fn($x) => …`), Spread (`...$args`), Closures als
*Argument* übergeben.

## Grenzen

PHPStan arbeitet auf dem AST — Treffer in Kommentaren und Strings lösen nichts aus
(siehe `tests/fixtures/allowed-comments-and-strings.php`). Das ist gewollt und
deckungsgleich mit dem tatsächlichen Plenty-Build-Verhalten.

## Pflege-Regel

Wer einen Plenty-Build-Fehler `"... is not allowed"` sieht, trägt ihn mit Wortlaut
der Fehlermeldung und dem verwendeten Ersatz in `blacklist-data.php` ein (Art,
Pattern, `message`, `replacement`, `evidence`) — als eigener PR gegen diesen Fork,
Branch `phpstan-stubs`. `stable7` nicht anfassen.

## Tests der Regeln

Keine PHPUnit/`RuleTestCase`-Suite — dieser Fork hat kein Composer-Autoloading für
PHPStans eigene Test-Framework-Klassen. Stattdessen: PHPStan lässt gegen Fixtures
laufen (`tests/fixtures/`, außerhalb der Stub-Pfade, damit Plugins, die diesen Fork
einbinden, sie nicht mitprüfen) und ein kleines PHP-Skript prüft per JSON-Output,
dass jede Blacklist-Art genau einen Treffer erzeugt und die Kommentar/String-Fixture
sauber bleibt.

```bash
php phpstan-blacklist/tests/run-tests.php
# optional: eigener PHPStan-Pfad
php phpstan-blacklist/tests/run-tests.php /pfad/zu/phpstan
```

Erwartete Ausgabe:

```
OK - all 6 rule fixtures reported, comments/strings fixture stayed clean.
```

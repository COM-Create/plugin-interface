# PlentyONE-Blacklist für PHPStan

PlentyONE lässt in Plugins nur gewhitelistete PHP-Funktionen und Sprachkonstrukte zu.
Ein Verstoß fällt **erst im serverseitigen Plugin-Build** auf – lokal ist der Code
fehlerfrei, PHPStan schweigt, die Tests sind grün. Jeder Treffer kostet einen kompletten
Build-Zyklus.

`plenty-blacklist.neon` holt diese Fehler nach vorn: PHPStan meldet die Verstöße schon
beim Tippen (PhpStorm) und in Kais Prüfung (Morpheus), bevor ein Build läuft.

## Blacklist, nicht Whitelist

Hier steht nur, was **belegt verboten** ist – ein echter Plenty-Build hat es abgelehnt –
plus ein paar klar markierte Faustregeln. Die vollständige Plenty-Whitelist kennt niemand
von uns, und Plenty pflegt keine aktuelle öffentliche Liste. **Die Liste ist unvollständig.**
Was fehlt, ist nicht erlaubt, sondern uns nur noch nicht begegnet.

## Einbinden

Technik: [`spaze/phpstan-disallowed-calls`](https://github.com/spaze/phpstan-disallowed-calls)
(getestet mit v4.14.0, PHPStan 2.1/2.2). Die Liste ist eine reine Parameter-Datei für diese
Erweiterung – keine eigenen Regeln, keine Scripte.

**Bei Kai (Morpheus):** automatisch. `ops/deps.py` installiert die Erweiterung global und
bindet sie samt dieser Liste für jedes Plenty-Plugin ein.

**Lokal:** Erweiterung einmal global installieren …

```bash
composer global require spaze/phpstan-disallowed-calls
```

… und in der `phpstan.local.neon` des Plugins einbinden. **Reihenfolge ist Pflicht** – die
`extension.neon` definiert das Parameter-Schema, ohne sie lehnt PHPStan die Liste ab:

```neon
includes:
	- /home/<user>/.config/composer/vendor/spaze/phpstan-disallowed-calls/extension.neon
	- ../../plugin-interface-beta7/phpstan-blacklist/plenty-blacklist.neon
```

Gilt ab Level 0. Treffer tragen den Identifier `plenty.disallowedFunction` bzw.
`plenty.disallowedClass` – so ist sofort erkennbar, dass es die Plenty-Liste war.

## Einträge

### Im Build belegt

| Verboten | Fehlermeldung im Build | Stattdessen |
|---|---|---|
| `get_object_vars()` | `php function "get_object_vars" is not allowed` | Property direkt lesen, `instanceof`-Guard davor |
| `ctype_digit()` | `php function "ctype_digit" is not allowed` | Zeichenvergleich `$c >= '0' && $c <= '9'` |
| `mb_strtoupper()` | `php function "mb_strtoupper" is not allowed` | `strtoupper()` (nur ASCII) plus eigene Tabelle |
| `mb_strtolower()` | analog zu `mb_strtoupper` | `strtolower()` (nur ASCII) plus eigene Tabelle |
| `iconv()` | `php function "iconv" is not allowed` | eigene Umschrifttabelle |
| `class_exists()` | `php function "class_exists" is not allowed` | `try`/`catch` um den Aufruf, `Throwable` auswerten |
| `get_class_methods()` | `php function "get_class_methods" is not allowed` | Methoden explizit aufrufen |
| `Reflection*` (alle Reflection-Klassen) | `class "ReflectionClass" is not allowed` | Signatur per gezielt provoziertem `TypeError` ermitteln |

### Faustregel (nicht einzeln im Build belegt)

I/O, Netzwerk, Prozesse und Dateisystem sind in Plugins gesperrt:
`file_get_contents()`, `file_put_contents()`, `fopen()`, `unlink()`, `exec()`,
`shell_exec()`, `system()`, `passthru()`, `proc_open()`, `curl_*()`.
Stattdessen: Plenty-Storage bzw. Guzzle.

### Verboten, aber von PHPStan NICHT geprüft

Diese drei lehnt der Plenty-Build ab, die Erweiterung erkennt sie aber nicht (gemessen –
sie arbeitet über Namen, ein variabler Aufruf hat zur Analysezeit keinen). Hier hilft nur
Aufpassen und Review:

| Verboten | Fehlermeldung im Build | Stattdessen |
|---|---|---|
| `$obj->{$name}` (dynamischer Property-Name) | `dynamic property names are not allowed` | typisierter Zugriff, notfalls fest verdrahten |
| `$obj->$name()` (dynamischer Methodenname) | `dynamic method names are not allowed` | explizite Aufrufe, `match` |
| `$fn()` (Aufruf über Variable) | `php function "fn" is not allowed` | Aufruf ausschreiben; Closures als *Argument* übergeben ist erlaubt |

### Im Build bestätigt erlaubt

Nicht aus Vorsicht umbauen: `mb_substr()`, `get_class()`, Arrow Functions `fn($x) => …`,
Spread `...$args`, Closures als Argument.

## Pflege

**Wer einen Plenty-Build-Fehler „… is not allowed“ sieht, trägt ihn ein** – mit dem Wortlaut
der Fehlermeldung und dem verwendeten Ersatz, in `plenty-blacklist.neon` **und** in die
Tabelle oben. PR gegen den Branch `phpstan-stubs`.

Regeln für die neon:

- **Bestehende `message`-Texte nie umformulieren.** PHPStan und Kais Prüfung schlüsseln ihre
  Baselines auf den Meldungstext. Ein geänderter Text macht jeden eingefrorenen Treffer
  schlagartig „neu“ – das Gate fällt in Repos um, an denen niemand etwas geändert hat.
  Nur ergänzen. Muss ein Text doch geändert werden, brauchen alle betroffenen Repos eine
  neue Baseline.
- **Kein `allowIn`** in dieser Liste. Ausnahmen gehören als `ignoreErrors` in die
  `phpstan.neon` des jeweiligen Plugins.
- **Nur Tabs** zum Einrücken, nie gemischt.

**Gut zu wissen:** Kai friert den Altbestand eines Repos ein, bevor er etwas ändert
(Baseline). Ein neuer Listeneintrag fängt deshalb nur neu geschriebenen Code – bestehende
Verstöße im Altcode fallen dadurch nicht auf. Altcode nachziehen wäre ein eigener Durchgang.

# plugin-interface (COM-Create fork)

Fork of `plentymarkets/plugin-interface` with PHPStan stubs for PlentyONE plugin development.

**Branch in use: `phpstan-stubs`.** Local PHPStan setups and Kai (Morpheus) read from this
branch; plugin `composer.json` files are being switched over to it. `stable7` is no longer
maintained.

- `phpstan-blacklist/` – PlentyONE build blacklist for PHPStan (functions and classes the
  Plenty plugin build rejects). See `phpstan-blacklist/README.md`.
- `docs/plenty-plugin-guide.md` – general PlentyONE plugin development guide (service
  providers, routing, migrations, crons, events, config, translations).

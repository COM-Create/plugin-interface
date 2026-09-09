<?php
namespace Plenty\Modules\Catalog\Version2\ChannelMaps\PluginServices;

/**
 * The ChannelMapTemplateContainerService registers catalog templates with the
 * Channel Map generator, so a merchant can combine several templates into one
 * shared mapping. Templates sharing a field key then only need that key mapped
 * once instead of once per template.
 *
 * NOTE — this stub is not part of the official plentymarkets plugin interface.
 * The class exists on live systems but appears in no published interface
 * version (checked against stable7 and early). It is reproduced here so PHPStan
 * can type check calls against it instead of reporting class.notFound.
 *
 * The signature below was not guessed: it was read off a live system by
 * provoking TypeErrors, since the PHP whitelist blocks reflection entirely.
 * Every parameter name and type is quoted verbatim from the resulting error
 * messages, e.g. "Argument #3 ($subtype) must be of type string, array given".
 * Verified 2026-09-09 on PID 61802.
 *
 * Two limits of that method, so nobody mistakes this for a full spec:
 * - The DEFAULT VALUES of the optional parameters are unknown — a TypeError
 *   only reveals that at least 3 arguments are required, not what the rest
 *   fall back to. The values below are placeholders chosen so the arity is
 *   correct; do not rely on them as documented behaviour.
 * - The RETURN TYPES are unknown for the same reason and are left unspecified.
 */
abstract class ChannelMapTemplateContainerService
{

	/**
	 * Registers one catalog template with the Channel Map generator.
	 *
	 * $exportType follows TemplateContainerContract::register() and determines
	 * which data is available for mappings ('vdi' for variation data).
	 */
	abstract public function add(
		string $name,
		string $type,
		string $subtype,
		string $exportType,
		?string $label = null,
		?string $marketplace = null,
		int $version = 1
	);

	/**
	 * Registers a callback invoked when the Channel Map templates are loaded.
	 *
	 * The parameter is typed plainly as callable, so PHP does not constrain what
	 * the callback itself receives — that is decided by Plenty at runtime. In
	 * practice the callback is handed this same service instance, NOT the
	 * separate ChannelMapTemplateContainer object the documentation describes.
	 */
	abstract public function registerLoadEventCallback(
		callable $callback
	);

}

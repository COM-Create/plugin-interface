<?php
namespace Plenty\Modules\Catalog\Templates\Providers;

use Plenty\Modules\Catalog\Containers\Filters\CatalogFilterBuilderContainer;
use Plenty\Modules\Catalog\Containers\TemplateGroupContainer;
use Plenty\Modules\Catalog\Contracts\CatalogDynamicConfigContract;
use Plenty\Modules\Catalog\Contracts\CatalogGroupedTemplateProviderContract;
use Plenty\Modules\Catalog\Contracts\CatalogMutatorContract;
use Plenty\Modules\Catalog\Contracts\TemplateContract;
use Plenty\Modules\Catalog\Dummy\DynamicConfig\EmptyCatalogDynamicConfig;
use Plenty\Modules\Catalog\Dummy\EmptyCatalogMutator;
use Plenty\Modules\Catalog\Services\Cache\Intervals\Defaults\DefaultIntervals;
use Plenty\Modules\Catalog\Services\Cache\Intervals\Intervals;
use Plenty\Modules\Catalog\Services\Converter\Containers\DefaultResultConverterContainer;
use Plenty\Modules\Catalog\Services\Converter\Containers\ResultConverterContainer;
use Plenty\Modules\Catalog\Services\UI\Sections\Sections;

/**
 * The AbstractGroupedTemplateProvider is the abstract class that should be used to implement a template provider.
 *
 * PHPStan-Fix: Non-essential abstract methods given default implementations.
 * The original plentymarkets/plugin-interface declares ALL methods as abstract,
 * but at runtime Plenty provides default implementations for most of them.
 * Only getTemplateGroupContainer(), getFilterContainer(), getCustomFilterContainer()
 * MUST be implemented by each concrete provider — the rest get sensible defaults here.
 *
 * Without these defaults, any non-abstract class extending this (e.g. DynamicTemplateProvider)
 * triggers non-ignorable PHPStan "method.abstract" errors for every unimplemented method.
 */
abstract class AbstractGroupedTemplateProvider implements CatalogGroupedTemplateProviderContract

{

	public function isPreviewable(
	):bool
	{
		return false;
	}

	public function allowsCustomFilter(
	):bool
	{
		return false;
	}

	public function getDynamicConfig(
	):CatalogDynamicConfigContract
	{
		return pluginApp(EmptyCatalogDynamicConfig::class);
	}

	/**
	 * Returns the Mutator instance that should be called to manipulate data before the mapping.
	 */
	public function getPreMutator(
	):CatalogMutatorContract
	{
		return pluginApp(EmptyCatalogMutatorContract::class);
	}

	/**
	 * Returns the Mutator instance that should be called to manipulate data after the mapping.
	 */
	public function getPostMutator(
	):CatalogMutatorContract
	{
		return pluginApp(EmptyCatalogMutator::class);
	}

	/**
	 * Returns a callback function that is called if a field with the specific key "sku" got mapped.
	 */
	public function getSkuCallback(
	):callable
	{
		return function ($value, array $item, $mappingType) { return $value; };
	}

	/**
	 * Returns an array of settings that will be displayed in the UI of each catalogue.
	 */
	public function getSettings(
	):array
	{
		return [];
	}

	/**
	 * Returns an array of meta information.
	 */
	public function getMetaInfo(
	):array
	{
		return [];
	}

	public function getCustomFilters(
	):array
	{
		return [];
	}

	public function getAssignments(
	):array
	{
		return [];
	}

	public function getFilter(
	):array
	{
		return [];
	}

	public function hasExtendedMappings(
	):bool
	{
		return false;
	}

	public function getResultConverterClass(
	):string
	{
		return '';
	}

	/**
	 * Returns a container which contains all result converters for a given template.
	 */
	public function getResultConverterContainer(
	):ResultConverterContainer
	{
		return pluginApp(DefaultResultConverterContainer::class);
	}

	public function getDefaultCatalogSettings(
	):array
	{
		return [];
	}

	/**
	 * Register hooks in specific event slots
	 */
	public function getHooks(
	)
	{
		return null;
	}

	/**
	 * Gets sections
	 */
	public function getSections(
	):Sections
	{
		return pluginApp(Sections::class);
	}

	/**
	 * Gets devcache intervals
	 */
	public function getDevcacheIntervals(
	):Intervals
	{
		return pluginApp(DefaultIntervals::class);
	}

	/**
	 * Gets Channel Map custom keys
	 */
	public function getMapFieldKeys(
	):array
	{
		return [];
	}

	/**
	 * Gets forced fields for channel
	 */
	public function getForcedFieldsForChannel(
	):TemplateGroupContainer
	{
		return pluginApp(TemplateGroupContainer::class);
	}

	/**
	 * Returns a container in which all TemplateGroups of this template are collected.
	 */
	abstract public function getTemplateGroupContainer(
	):TemplateGroupContainer;

	/**
	 * Returns the container that collects all filters of templates that are booted by this specific provider.
	 */
	abstract public function getFilterContainer(
	):CatalogFilterBuilderContainer;

	/**
	 * Returns the container that collects all custom filters of templates that are booted by this specific provider.
	 */
	abstract public function getCustomFilterContainer(
	):CatalogFilterBuilderContainer;

}

<?php
namespace Plenty\Modules\Plugin\DataBase\Contracts;

use Illuminate\Support\Collection;
use Plenty\Modules\Plugin\Traits\ActsAsModel;

/**
 * Database model
 *
 * PHPStan-Fix: Abstract methods removed — they are provided at runtime by Eloquent.
 * Original: https://github.com/plentymarkets/plugin-interface (beta7)
 */
abstract class Model
{

	const FIELD_TYPE_INT = 'int';

	const FIELD_TYPE_UNSIGNED_INT = 'unsignedInteger';

	const FIELD_TYPE_DECIMAL = 'decimal';

	const FIELD_TYPE_STRING = 'string';

	const FIELD_TYPE_BOOL = 'bool';

	const FIELD_TYPE_ARRAY = 'array';

	const FIELD_TYPE_TEXT = 'text';

	const FIELD_TYPES = ['int','decimal','string','bool','array','text'];

protected		$primaryKeyFieldName = "id";

protected		$primaryKeyFieldType = "int";

protected		$autoIncrementPrimaryKey = true;

protected		$textFields;

protected		$attributes;

protected		$original;

protected		$changes;

protected		$casts;

protected		$dates;

protected		$dateFormat = null;

protected static 		$mutatorCache;

	abstract public function getTableName(
	):string;

	/**
	 * Convert the model's attributes to an array.
	 */
	public function attributesToArray(
	):array
	{
		return [];
	}

	/**
	 * Get an attribute from the model.
	 */
	public function getAttribute(
		string $key
	)
	{
		return null;
	}

	/**
	 * Get a plain attribute
	 */
	public function getAttributeValue(
		string $key
	)
	{
		return null;
	}

	/**
	 * Get an attribute from the $attributes array.
	 */
	protected function getAttributeFromArray(
		string $key
	)
	{
	}

	/**
	 * Determine if a get mutator exists for an attribute.
	 */
	public function hasGetMutator(
		string $key
	):bool
	{
		return false;
	}

	/**
	 * Get the value of an attribute using its mutator.
	 */
	protected function mutateAttribute(
		string $key,
		 $value
	)
	{
	}

	/**
	 * Get the value of an attribute using its mutator for array conversion.
	 */
	protected function mutateAttributeForArray(
		string $key,
		 $value
	)
	{
	}

	/**
	 * Set a given attribute on the model.
	 */
	public function setAttribute(
		string $key,
		 $value
	):self
	{
		return $this;
	}

	/**
	 * Determine if a set mutator exists for an attribute.
	 */
	public function hasSetMutator(
		string $key
	):bool
	{
		return false;
	}

	/**
	 * Set a given JSON attribute on the model.
	 */
	public function fillJsonAttribute(
		string $key,
		 $value
	):self
	{
		return $this;
	}

	/**
	 * Decode the given JSON back into an array or object.
	 */
	public function fromJson(
		string $value,
		bool $asObject = false
	)
	{
		return null;
	}

	/**
	 * Convert a DateTime to a storable string.
	 */
	public function fromDateTime(
		 $value
	):string
	{
		return '';
	}

	/**
	 * Get the attributes that should be converted to dates.
	 */
	public function getDates(
	):array
	{
		return [];
	}

	/**
	 * Set the date format used by the model.
	 */
	public function setDateFormat(
		string $format
	):self
	{
		return $this;
	}

	/**
	 * Determine whether an attribute should be cast to a native type.
	 */
	public function hasCast(
		string $key,
		 $types = null
	):bool
	{
		return false;
	}

	/**
	 * Get the casts array.
	 */
	public function getCasts(
	):array
	{
		return [];
	}

	/**
	 * Get all of the current attributes on the model.
	 */
	public function getAttributes(
	):array
	{
		return [];
	}

	/**
	 * Set the array of model attributes. No checking is done.
	 */
	public function setRawAttributes(
		array $attributes,
		bool $sync = false
	):self
	{
		return $this;
	}

	/**
	 * Get the model's original attribute values.
	 */
	public function getOriginal(
		string $key = null,
		 $default = null
	)
	{
		return null;
	}

	/**
	 * Get a subset of the model's attributes.
	 */
	public function only(
		 $attributes
	):array
	{
		return [];
	}

	/**
	 * Sync the original attributes with the current.
	 */
	public function syncOriginal(
	):self
	{
		return $this;
	}

	/**
	 * Sync a single original attribute with its current value.
	 */
	public function syncOriginalAttribute(
		string $attribute
	):self
	{
		return $this;
	}

	/**
	 * Sync the changed attributes.
	 */
	public function syncChanges(
	):self
	{
		return $this;
	}

	/**
	 * Determine if the model or given attribute(s) have been modified.
	 */
	public function isDirty(
		 $attributes = null
	):bool
	{
		return false;
	}

	/**
	 * Determine if the model or given attribute(s) have remained the same.
	 */
	public function isClean(
		 $attributes = null
	):bool
	{
		return true;
	}

	/**
	 * Determine if the model or given attribute(s) have been modified.
	 */
	public function wasChanged(
		 $attributes = null
	):bool
	{
		return false;
	}

	/**
	 * Get the attributes that have been changed since last sync.
	 */
	public function getDirty(
	):array
	{
		return [];
	}

	/**
	 * Get the attributes that were changed.
	 */
	public function getChanges(
	):array
	{
		return [];
	}

	/**
	 * Get the mutated attributes for a given instance.
	 */
	public function getMutatedAttributes(
	):array
	{
		return [];
	}

	/**
	 * Extract and cache all the mutated attributes of a class.
	 */
	public static function cacheMutatedAttributes(
		string $class
	)
	{
	}

	public function relationLoaded(
	)
	{
	}

}

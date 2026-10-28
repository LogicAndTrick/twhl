<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class ModelWithExtraTypeInfo extends Model {
    /**
     * OpenAPI property formats. Keys should be property names and
     * values from this table: https://spec.openapis.org/registry/format/index.html
     * @var array<string, string>
     */
    protected $formats = [];

    /**
     * Nullable properties
     * @var string[]
     */
    protected $nullable = [];

    /**
     * Returns the specified format for an attribute
     */
    public function getFormat(string $property): ?string
    {
        return $this->formats[$property] ?? ($this->isDateAttribute($property) ? 'date-time' : null);
    }

    /**
     * Checks if an attribute's value should be nullable
     */
    public function isNullable(string $property): bool
    {
        return in_array($property, $this->nullable, true)
            || $this->isDateColumn($property);
    }

    /**
     * Gets `SoftDeletes`'s `deleted_at` column name
     */
    private function getDeletedAt(): null | string
    {
        return in_array(SoftDeletes::class, class_uses_recursive(static::class))
            ? $this->getDeletedAtColumn()
            : null;
    }

    /**
     * Is it one of the standard timestamps (usually `created_at` and `deleted_at`) or
     * SoftDeletes's timestamp (usually `updated_at`)? Those are nullable.
     */
    private function isDateColumn(string $property): bool
    {
        return in_array($property, $this->getDates(), true)
            || $property === $this->getDeletedAt();
    }
}

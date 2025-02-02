<?php

namespace Vanacode\Model\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Vanacode\Model\Attributes\Attribute;
use Vanacode\Model\Attributes\AttributeList;

/**
 * @method static Builder<static> simpleSearchByAttributes(AttributeList $attributes, array $search)
 */
interface AttributeInterface
{
    public function getAttributeOptions(): array;

    public function gerCoreAttributeOptions(): array;

    public function processAttributes(array $options): AttributeList;

    /**
     * Get the fillable attributes for the model.
     */
    public function getFillableWithId(): array;

    /**
     * Get the fillable attributes for the model.
     */
    public function getFillableAttributes(): array;

    /**
     * Get the fillable attributes for the model.
     */
    public function getFillableWithIdAttributes(): array;

    public function scopeCallback(Builder $q, ?callable $callback = null): void;

    public function scopeAttributeOptions(Builder $q, AttributeList $attributes, array $data = []): void;

    public function scopeSimpleSearchByAttributes(Builder $q, AttributeList $attributes, array $search): void;

    public function scopeAdvancedSearchByAttributes(Builder $q, AttributeList $attributes, array $data, bool $advancedSearch): void;

    public function scopeSimpleSearchByAllAttributes(Builder $q, Collection $searchable, array $search): void;

    public function scopeAdvancedSearchByAllAttributes(Builder $q, Collection $searchAttributes, array $data): void;

    public function scopeSearchByAttribute(Builder $q, Attribute $attribute, array $search, bool $strict = false): void;
}

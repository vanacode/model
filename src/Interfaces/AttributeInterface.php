<?php

namespace Vanacode\Model\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Vanacode\Model\Attributes\AttributeList;

/**
 * @mixin Model
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

    public function scopeCallback(Builder $q, ?callable $callback = null): Builder;

    public function scopeAttributeOptions(Builder $q, AttributeList $attributes, array $data = []): Builder;

    public function scopeSimpleSearchByAttributes(Builder $q, AttributeList $attributes, array $search): void;

    public function scopeAdvancedSearchByAttributes(Builder $q, AttributeList $attributes, array $data, bool $advancedSearch): void;
}

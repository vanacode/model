<?php

namespace Vanacode\Model\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Vanacode\Model\Attributes\Attribute;
use Vanacode\Model\Attributes\AttributeList;
use Vanacode\Model\Attributes\SearchAttribute;
use Vanacode\Model\Interfaces\AttributeInterface;
use Vanacode\Resource\RequestHelper;

/**
 * @mixin AttributeInterface
 */
trait AttributeTrait
{
    /**
     * see Attribute __construct
     */
    protected array $attributeOptions = [];

    public function getAttributeOptions(): array
    {
        return $this->attributeOptions;
    }

    public function gerCoreAttributeOptions(): array
    {
        return [
            $this->getKeyName() => [
                'searchable' => false,
            ],
            Attribute::SERIAL_NUMBER => [
                'label' => 'common.serial_number',
                'virtual' => true,
            ],
            Attribute::ACTIONS => [
                'virtual' => true,
                'component' => 'actions.item',
                'label' => 'common.actions',
                //                'actions' => [], set by default $actions property,
            ],
            $this->getCreatedAtColumn() => [
                'order' => 'desc',
            ],
        ];
    }

    public function processAttributes(array $options): AttributeList
    {
        return new AttributeList($this, $this->getResource(), $options);
    }

    /**
     * Get the fillable attributes for the model.
     */
    public function getFillableWithId(): array
    {
        return array_merge([$this->getKeyName()], $this->getFillable());
    }

    /**
     * Get the fillable attributes for the model.
     */
    public function getFillableAttributes(): array
    {
        return $this->only($this->getFillable());
    }

    /**
     * Get the fillable attributes for the model.
     */
    public function getFillableWithIdAttributes(): array
    {
        return $this->only($this->getFillableWithId());
    }

    public function scopeCallback(Builder $q, ?callable $callback = null): Builder
    {
        return $q->when(is_callable($callback), function ($q) use ($callback) {
            $callback($q);
        });
    }

    public function scopeAttributeOptions(Builder $q, AttributeList $attributes, array $data = []): Builder
    {
        $select = $attributes->getSelectable();
        if (! $select->contains($this->getKeyName())) {
            $select->prepend($this->getKeyName());
        }

        $orders = $data[RequestHelper::queryAlias('order_by')] ?? $attributes->getDefaultOrders();
        $search = $data[RequestHelper::queryAlias('search')] ?? null;
        $advancedSearch = $data[RequestHelper::queryAlias('search_form')] ?? false; // TODO think better name column search, row search, attribute search
        $advancedSearch = (bool) $advancedSearch;
        $withCount = $attributes->getWithCount();
        $with = $attributes->getWith();
        $search = Attribute::searchLikeArray($search);

        return $q->select($this->qualifyColumns($select->all())) // TODO option for skip qualify columns
            ->simpleSearchByAttributes($attributes, $search)
            ->advancedSearchByAttributes($attributes, $data, $advancedSearch)
            ->when($withCount, function ($query) use ($withCount) {
                $query->withCount($withCount);
            })
            ->when($with, function ($query) use ($with) {
                foreach ($with as $options) {
                    if (is_array($options)) {
                        $relations = [];
                        foreach ($options['with'] as $relation => $details) {
                            $relations[$relation] = function ($q) use ($details, $relation) {
                                if (! empty($details['attributes'])) {
                                    $related = $this->$relation()->getRelated();
                                    $attributeOptions = $related->processAttributes($details['attributes']);
                                    $q->attributeOptions($attributeOptions, []);
                                }
                                if (! empty($details['columns'])) {
                                    $q->select($details['columns']);
                                }
                                if (! empty($details['with_count'])) {
                                    $q->withCount($details['with_count']);
                                }
                            };
                        }
                        $query->with($relations);
                    } else {
                        $query->with($options);
                    }
                }
            })
            ->when($orders && is_array($orders), function ($q) use ($orders, $attributes) {
                $orderable = $attributes->getOrderable();
                // TODO later support other options by grouped order, or join order
                foreach ($orders as $column => $order) {
                    if ($orderable->has($column) || in_array($column, [$this->getCreatedAtColumn(), $this->getUpdatedAtColumn()])) {
                        $q->orderBy($column, $order);
                    } else {
                        throw new \Exception('Not permitted order');
                    }
                }
            });
    }

    public function scopeSimpleSearchByAttributes(Builder $q, AttributeList $attributes, array $search): void
    {
        if (empty($search)) {
            return;
        }

        $searchable = $attributes->getSearchable();
        $searchSelfRelationColumns = $attributes->getSearchSelfRelationColumns();
        if (! $searchSelfRelationColumns) {
            $this->simpleSearchByAllAttributes($q, $searchable, $search);

            return;
        }

        $q->where(function ($q) use ($searchable, $search, $searchSelfRelationColumns) {
            $this->simpleSearchByAllAttributes($q, $searchable, $search);
            foreach ($searchSelfRelationColumns as $relation => $columns) {
                $q->orWhereHas($relation, function ($q) use ($searchable, $search, $columns) {
                    $q->where(function ($q) use ($searchable, $search, $columns) {
                        foreach ($searchable as $attribute) {
                            if ($columns === true || in_array($attribute->searchAttribute->name, $columns)) {
                                $this->searchByAttribute($q, $attribute, $search);
                            }
                        }
                    });
                });
            }
        });
    }

    public function scopeAdvancedSearchByAttributes(Builder $q, AttributeList $attributes, array $data, bool $advancedSearch): void
    {
        if (empty($advancedSearch)) {
            return;
        }

        $searchSelfRelationColumns = $attributes->getSearchSelfRelationColumns();
        $searchAttributes = $attributes->getSearchAttributes();

        if (! $searchSelfRelationColumns) {
            $this->advancedSearchByAllAttributes($q, $searchAttributes, $data);

            return;
        }

        $q->where(function ($q) use ($searchAttributes, $data, $searchSelfRelationColumns) {
            $this->advancedSearchByAllAttributes($q, $searchAttributes, $data);
            foreach ($searchSelfRelationColumns as $relation => $columns) {
                $q->orWhereHas($relation, function ($q) use ($searchAttributes, $data, $columns) {
                    $strictSearch = $data[RequestHelper::queryAlias('strict_search')] ?? true;
                    foreach ($searchAttributes as $attribute) {
                        if ($columns !== true && ! in_array($attribute->searchAttribute->name, $columns)) {
                            continue;
                        }

                        /** @var Attribute $attribute */
                        $search = $attribute->getSearchValue($data);
                        if ($search) {
                            $this->searchByAttribute($q, $attribute, $search, $strictSearch);
                        }
                    }
                });
            }
        });
    }

    protected function simpleSearchByAllAttributes(Builder $q, Collection $searchable, array $search)
    {
        $q->where(function ($q) use ($searchable, $search) {
            foreach ($searchable as $attribute) {
                $this->searchByAttribute($q, $attribute, $search);
            }
        });
    }

    protected function advancedSearchByAllAttributes(Builder $q, Collection $searchAttributes, array $data)
    {
        $q->where(function ($q) use ($data, $searchAttributes) {
            $strictSearch = $data[RequestHelper::queryAlias('strict_search')] ?? true;
            foreach ($searchAttributes as $attribute) {
                /** @var Attribute $attribute */
                $search = $attribute->getSearchValue($data);
                if ($search) {
                    $this->searchByAttribute($q, $attribute, $search, $strictSearch);
                }
            }
        });
    }

    protected function searchByAttribute(Builder $q, Attribute $attribute, array $search, bool $strict = false): void
    {
        if (empty($search)) {
            return;
        }
        $searchAttribute = $attribute->searchAttribute;
        $operation = $searchAttribute->operation;
        $searchBy = $searchAttribute->searchBy;
        if (is_array($searchBy)) {
            // TODO later support multiple columns, or contcat columns, ..etx
            return;
        }

        if ($operation == SearchAttribute::OPERATION_WHERE_IN) {
            $method = $strict ? 'whereIn' : 'orWhereIn';
            $q->$method($searchBy, $search);

            return;
        }

        if ($operation == SearchAttribute::OPERATION_WHERE_HAS) {
            // TODO later improve
            $q->whereHas($searchAttribute->relation, function ($q) use ($search, $searchAttribute, $strict, $searchBy) {
                $method = $strict ? 'whereIn' : 'orWhereIn';
                $q->$method($searchAttribute->relation.'.' . $searchBy, $search); // TODO id also get by configs
            });

            return;
        }

        if ($operation != SearchAttribute::OPERATION_LIKE) {
            // TODO later support
            return;
        }
        $method = $strict ? 'where' : 'orWhere';
        $q->$method($searchBy, 'REGEXP', implode('|', $search));
    }
}

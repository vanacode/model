<?php

namespace Vanacode\Model\Attributes;

use Illuminate\Support\Collection;
use Vanacode\Model\Model;
use Vanacode\Support\Traits\CheckAccess;

class AttributeList
{
    use CheckAccess;

    protected collection $processed;

    public function __construct(protected readonly Model $item, protected string $resource, protected readonly array $attributes = [])
    {
        // TODO resource
        // TODO save search template, columns template ,...etc
        // which columns to show
    }

    public function all(): Collection
    {
        return $this->getProcessed();
    }

    public function getSelectable(): Collection
    {
        return $this->getProcessed()->pluck('columns')->collapse()->unique()->filter();
    }

    public function getWithCount(): array
    {
        return $this->getProcessed()->pluck('withCount')->unique()->filter()->toArray();
    }

    public function getWith(): array
    {
        return $this->getProcessed()->pluck('with')->unique()->filter()->toArray();
    }

    public function getSearchSelfRelationColumns(): array
    {
        return $this->getProcessed()->pluck('searchSelfRelationColumns', 'with')->filter()->toArray();
    }

    public function getOrderable(): Collection
    {
        return $this->getProcessed()->filter(function (Attribute $attribute) {
            return ! empty($attribute->orderable);
        })->keyBy('name');
    }

    public function getDefaultOrders()
    {
        $orderOptions = $this->getOrderable();
        $orders = $orderOptions->filter(function (Attribute $attribute) {
            return ! empty($attribute->order);
        })->map(function (Attribute $attribute) {
            return [
                'name' => $attribute->name,
                'order' => $attribute->order,
            ];
        })->pluck('order', 'name')->all();

        if (empty($orders)) {
            return [
                //                $this->item->getCreatedAtColumn() => 'desc',
            ];
        }

        return $orders;
    }

    public function getSearchable(): Collection
    {
        return $this->getProcessed()->filter(function (Attribute $attribute) {
            return ! empty($attribute->searchable) && empty($attribute->hide);
        });
    }

    public function getSearchAttributes(): Collection
    {
        $filtered = $this->getProcessed()->filter(function (Attribute $attribute) {
            return ! empty($attribute->searchAttribute);
        });
        $count = $filtered->count();

        return $filtered->sortBy(function (Attribute $attribute) use ($count) {
            return $attribute->searchAttribute->position ?: $count;
        });
    }

    protected function getProcessed(): Collection
    {
        if (! isset($this->processed)) {
            $this->processed = $this->processAttributes();
        }

        return $this->processed;
    }

    protected function processAttributes(): Collection
    {
        $attributes = $this->attributes ?: $this->getDefaultVisibleAttributes();

        $idKey = $this->item->getKeyName();
        if (! array_key_exists($idKey, $attributes) && ! in_array($idKey, $attributes)) {
            $attributes = array_merge([$idKey => [
                'hide' => true,
            ]], $attributes);
        }
        $attributeOptions = $this->getAttributeOptions();
        $processed = [];
        foreach ($attributes as $attribute => $options) {
            if (is_numeric($attribute)) {
                if (! is_array($options)) {
                    $attribute = $options;
                    $options = [];
                } else {
                    $attribute = $options['attribute']; // TODO decide is really need and maybe use name
                }
            }
            $options = $this->fillDefaultOptions($attribute, $options, $attributeOptions);
            if (! $this->checkAccess($options)) {
                continue;
            }
            $processed[] = new Attribute($this->item, $attribute, $this->resource, $options);
        }

        return collect($processed);
    }

    protected function getDefaultVisibleAttributes(): array
    {
        $attributes = $this->item->getFillableWithId();
        if ($this->item->usesTimestamps()) {
            $attributes[] = $this->item->getCreatedAtColumn();
            $attributes[] = $this->item->getUpdatedAtColumn();
        }
        if (method_exists($this->item, 'getDeletedAtColumn')) {
            $attributes[] = $this->item->getDeletedAtColumn();
        }

        $attributes[] = Attribute::ACTIONS;

        return array_diff($attributes, $this->item->getHidden());
    }

    public function getAttributeOptions(): array
    {
        return array_merge($this->getTraitAttributeOptions(), $this->item->gerCoreAttributeOptions(), $this->item->getAttributeOptions()); // issue was boolean case and getAttributeOptions
    }

    protected function getTraitAttributeOptions(): array
    {
        $attributeOptions = [];
        $methods = $this->item->getMethodsMatch('getAttributeOptionsBy');
        foreach ($methods as $method) {
            $attributeOptions = array_merge_recursive($this->item->$method(), $attributeOptions);
        }

        return $attributeOptions;
    }

    public function fillDefaultOptions(string $attribute, string|array $options, array $attributeOptions = []): array
    {
        if (is_string($options)) {
            $options = [
                'attribute' => $options,
            ];
        }
        //            TODO decide is helpfull
        //            if (!empty($options) && $this->model->skipWrapAttributes) {
        //                $processed[$column] = $options;
        //                continue;
        //            }

        if (array_key_exists($attribute, $attributeOptions)) {
            $options = array_merge($attributeOptions[$attribute], $options);
        }

        return $options;
    }
}

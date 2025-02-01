<?php

namespace Vanacode\Model\Attributes;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Vanacode\Model\Model;
use Vanacode\Resource\RequestHelper;

/**
 * @var array
 *            possible keys
 *            [
 *            virtual, set true for not existing column
 *            column,     (autodetect, no need to set it will be removed when virtual=true)
 *            attribute,  (autodetect, need to set when attribute is not same like column, it will be removed when virtual=true
 *            label,      (table heading auto-detected)
 *            hide,    (when it true then will not show in table)
 *            html,    use {!! !!}
 *            with_count, used for automatically get relation count
 *            with TODO
 *            order_by TODO
 *            relation,   used for automatically insert in query this as well
 *            searchable,     set it false for not do search
 *            ]
 */
class Attribute
{
    const SERIAL_NUMBER = '_number_';

    const ACTIONS = 'actions';

    public string $name;

    public string $attribute;

    public string $label;

    public string $column;

    public string $method;

    public string $order;

    public string $component;

    public string $include;

    public bool $virtual;

    public bool $orderable;

    public bool $searchable;

    public bool $hide;

    public bool $html;

    public int $limit;

    public array $columns;

    public array|string|null $with;

    public ?string $withCount;

    public ?SearchAttribute $searchAttribute = null;

    public array $componentData;

    public array $includeData;

    public array|bool $searchSelfRelationColumns;

    public array $class;

    public array $style;

    public array $actions;

    public function __construct(protected Model $model, string $name, protected string $resource, public array $options)
    {
        $this->name = $name;
         $this->validate();

        $this->virtual = (bool) ($this->options['virtual'] ?? false);
        $this->orderable = (bool) ($this->options['orderable'] ?? ! $this->virtual);
        $this->searchable = (bool) ($this->options['searchable'] ?? ! $this->virtual);
        $this->hide = (bool) ($this->options['hide'] ?? false);
        $this->html = (bool) ($this->options['html'] ??  false);

        $this->attribute = $this->options['attribute'] ?? $this->name;
        $this->label = $this->options['label'];
        $defaultColumn = $this->virtual ? '' : $this->name;
        $this->column = $this->options['column'] ?? $defaultColumn;

        $limit = $this->options['limit'] ?? 0;
        $this->limit = is_array($limit) ? random_int(...$limit) : $limit;
        $this->method = $this->options['method'] ?? '';
        $this->order = $this->options['order'] ?? '';
        $this->component = $this->options['component'] ?? '';
        $this->include = $this->options['include'] ?? '';
        $this->componentData = $this->getArrayBy('component_data');
        $this->includeData = $this->getArrayBy('include_data');
        $this->searchSelfRelationColumns = $this->options['search_self_relation_columns'] ?? false;
        $this->class = $this->getArrayBy('class');
        $this->style = $this->getArrayBy('style');

        $this->columns = $this->getArrayBy('columns');
        $this->actions = $this->getArrayBy('actions');
        if ($this->column) {
            $this->columns[] = $this->column;
        }
        $searchForm = $this->getArrayBy('search_form');
        if ($this->searchable || $searchForm) {
            $this->searchAttribute = $this->makeSearchAttribute($searchForm);
        }

        $this->with = $this->options['with'] ?? null;
        $this->withCount = $this->options['with_count'] ?? null;
    }

    public function validate(): void
    {
        if (array_key_exists('with_count', $this->options)) {
            $this->options['virtual'] = true;
            $this->options['orderable'] = $this->options['orderable'] ?? true; // TODO DEFAULT true or false get by config
            $this->options['searchable'] = $this->options['searchable'] ?? false; // TODO DEFAULT true or false get by config
            $relation = $this->options['relation'] ?? Str::replaceLast('_count', '', $this->name);
            $this->options['with_count'] = is_bool($this->options['with_count']) ? $relation : $this->options['with_count'];
            $this->options['label'] = $this->getWithCountAttributeLabel($this->options);
        } else {
            $this->options['label'] = $this->getAttributeLabel($this->options);
        }
    }

    public function setItem(Model $model)
    {
        // TODO decide is usefull or not
    }

    public function setOrder(string $order): self
    {
        $this->order = $order;

        return $this;
    }

    protected function getArrayBy(string $key): array
    {
        $value = $this->options[$key] ?? null;

        return Arr::wrap($value);
    }

    protected function makeSearchAttribute(array $searchForm): SearchAttribute
    {
        return new SearchAttribute($this, $searchForm);
    }

    protected function getAttributeLabel(array $details): string
    {
        return ! empty($details['label']) ? __($details['label']) : Lang::attribute($this->name, $this->resource);
    }

    protected function getWithCountAttributeLabel(array $details): string
    {
        $resource = $details['with_count'] ?? $this->name;

        return ! empty($details['label'])
            ? __($details['label'], ['resource' => $resource])
            : Lang::commonResource($resource, 'resource.with_count');
    }

    public function getSearchValue(array $data): array
    {
        if (! $this->searchable && empty($this->searchAttribute)) {
            return [];
        }

        $value = $data[$this->searchAttribute->name]
            ?? $data[RequestHelper::queryAlias('search')]
            ?? [];
        return self::searchLikeArray($value);

    }

    public static function searchLikeArray($search, bool $filter = true):array
    {
        $array = is_string($search) ? explode('|', $search) : Arr::wrap($search);
        if ($filter) {
            $array = array_filter($array);
        }

        return $array;
    }
}

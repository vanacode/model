<?php

namespace Vanacode\Model\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Vanacode\Resource\RequestHelper;

/** @phpstan-type QueryValue array|string|float|int|bool|null */
trait ScopeByArrayTrait
{
    protected array $queryAlias = [];

    protected array $mergeQueryAlias = [];

    /**
     * TODO later use attributeOptions
     */
    protected array $attributeAlias = [];

    protected array $keepColumnVisible = [];

    public function scopeByArray(Builder $query, array $data): void
    {
        $data = $this->mergeQueryAliasData($data);
        $this->implementQueryByArray($data, $query);
    }

    public function firstByArray(array $data): Model|static|null
    {
        return $this->byArray($data)->first();
    }

    /**
     * Get all items base array config data
     * Also has possibility to get single item
     * limited items
     * Can pass all chainable methods as array
     */
    public function findByArray(mixed $id, array $data): Model|Collection|static|null
    {
        return $this->byArray($data)->find($id);
    }

    /**
     * For make code shorter or custom you can define aliases
     */
    public function mergeQueryAliasData(array $data): array
    {
        $alias = RequestHelper::queryValue('alias');
        if (! empty($alias) && is_string($alias)) {
            $routeAlias = $this->mergeQueryAlias[$alias] ?? [];
            $data = array_merge($routeAlias, $data);
        }

        return $data;
    }

    /**
     * make eloquent query by array
     */
    protected function implementQueryByArray(array $data, ?Builder $query = null): Builder
    {
        $query = $query ?? $this->newQuery();
        $this->queryLimit($query, $data);
        $this->queryColumns($query, $data);
        //        $this->querySelectRaw($query, $data); TODO think, maybe can be used for sql attack
        $this->queryFilterByWhere($query, $data);
        $this->queryOrderBy($query, $data);
        $this->queryGroupBy($query, $data);
        $this->queryWithCount($query, $data);
        $this->queryWith($query, $data);

        return $query;
    }

    public function queryLimit(Builder $query, array $data): void
    {
        // @TODO improve
        $limit = $this->getDataBy($data, 'limit');
        if ($limit) {
            $query->limit($limit);
        }
    }

    protected function queryColumns(Builder $query, array $data): void
    {
        $select = $this->getDataBy($data, 'select');
        $select = $this->convertToArray($select);
        $columns = $this->getDataBy($data, 'columns');
        $columns = $this->convertToArray($columns);
        $columns = array_merge($columns, $select);

        if (empty($columns)) {
            return;
        }

        // TODO use attributeOptions and based that get select columns, and also toArray
        foreach ($this->attributeAlias as $attribute => $alias) {
            if (in_array($attribute, $columns)) {
                $columns = array_merge($columns, $this->convertToArray($alias));
            }
        }

        $mutated = $this->getMutatedAttributes();
        $pruneMutated = array_diff($mutated, $this->getFillable());
        $columns = array_diff($columns, array_keys($this->attributeAlias));
        $columns = array_diff($columns, $pruneMutated);

        array_unshift($columns, $query->getModel()->getKeyName());
        foreach ($columns as $column) {
            $columns[] = $query->qualifyColumn($column);
        }
        $columns = array_unique($columns);
        $query->select($columns);
    }

    public function querySelectRaw(Builder $query, array $data): void
    {
        // @TODO permission
        $selectRaw = $this->getDataBy($data, 'select_raw');
        if (! empty($selectRaw)) {
            $query->selectRaw($selectRaw);
        }
    }

    public function queryFilterByWhere(Builder $query, array $data): void
    {
        // @TODO improve
        $where = $this->getDataBy($data, 'where');

        if ($where) {
            $query->where($where);
        }

        $whereNested = $this->getDataBy($data, 'where_nested') ?? [];
        foreach ($whereNested as $callback) {
            $query->where($callback);
        }

        $whereDate = $this->getDataBy($data, 'where_date') ?? [];
        foreach ($whereDate as $key => $value) {
            $query->whereDate($key, $value);
        }

        $whereIn = $this->getDataBy($data, 'where_in') ?? [];

        foreach ($whereIn as $key => $values) {
            $values = is_string($values) ? [$values] : $values;
            $query->whereIn($key, $values);
        }

        $whereHas = $this->getDataBy($data, 'where_has') ?? [];

        foreach ($whereHas as $relation => $conditions) {
            $query->whereHas($relation, function ($q) use ($conditions) {
                $this->implementQueryByArray($conditions, $q);
            });
        }

        $has = $this->getDataBy($data, 'has') ?? [];
        if ($has) {
            $has = is_string($has) ? [$has] : $has;
            foreach ($has as $relation) {
                $query->has($relation);
            }
        }
    }

    public function queryOrderBy(Builder $query, array $data): void
    {
        $latest = $this->getDataBy($data, 'latest');
        if ($latest) {
            $query->latest();
        }

        //        $orderByRaw = $this->getDataBy($data, 'order_by_raw');
        //        foreach ($orderByRaw as $value) {
        //            $query->orderByRaw($value);
        //        }
        // @TODO improve
        $orderBy = $this->getDataBy($data, 'order_by');
        $orderBy = (array) $orderBy;
        if ($orderBy) {
            foreach ($orderBy as $key => $sort) {
                if (is_numeric($key)) {
                    $query->orderBy($sort);
                } else {
                    $query->orderBy($key, $sort);
                }
            }
        }
    }

    public function queryGroupBy(Builder $query, array $data): void
    {
        // @TODO improve
        $groupBy = $this->getDataBy($data, 'group_by') ?? [];
        foreach (Arr::wrap($groupBy) as $col) {
            $query->groupBy($col);
        }
    }

    public function queryWithCount(Builder $query, array $data): void
    {
        // @TODO improve
        $withCount = $this->getDataBy($data, 'with_count');
        if ($withCount) {
            $query->withCount($withCount);
        }
    }

    public function queryWith(Builder $query, array $data): void
    {
        // @TODO improve
        $_with = $this->getDataBy($data, 'with');
        if (empty($_with)) {
            return;
        }

        $_with = is_string($_with) ? [$_with] : $_with;

        $with = [];
        foreach ($_with as $relation => $options) {
            if (is_numeric($relation)) {
                $with[] = $options;
            } else {
                if (is_callable($options)) {
                    $with[$relation] = $options;

                    continue;
                }
                if (is_string($options)) {
                    $options = ['select' => $options];
                }

                $with[$relation] = function ($q) use ($options) {
                    $this->implementQueryByArray($options, $q);
                };
            }
        }
        $query->with($with);
    }

    /**
     * prefer use model level query alias
     * then by vn_resource.query alias
     * then direct key
     *
     * @return QueryValue
     */
    protected function getDataBy(array $data, string $key): mixed
    {
        $queryAlias = RequestHelper::queryAlias($key);

        return $this->getDataByQueryAlias($data, $key) ?? $data[$queryAlias] ?? $data[$key] ?? null;
    }

    /**
     * @return QueryValue
     */
    protected function getDataByQueryAlias(array $data, string $key): mixed
    {
        $queryAlias = RequestHelper::queryAlias($key);
        $preferKey = $this->queryAlias[$queryAlias] ?? $this->queryAlias[$key] ?? null;

        if (empty($preferKey)) {
            return null;
        }

        return $data[$preferKey] ?? null;
    }

    /**
     * @param  QueryValue  $data
     */
    protected function convertToArray(mixed $data): array
    {
        if (is_string($data)) {
            $data = explode(',', $data);
        }

        $result = [];
        foreach (Arr::wrap($data) as $value) {
            if (is_string($value)) {
                $value = explode(',', $value);
            }

            $value = Arr::wrap($value);
            foreach ($value as $single) {
                $result[] = trim($single);
            }
        }

        return $result;
    }

    /**
    / TODO use attributeOptions and based that get select columns, and also toArray
     * Change toArray if need make some virtual cols in include in response
     * or rename one attribute to other
     *
     * @return array
     */
    public function toArray()
    {
        if (empty($this->attributeAlias)) {
            return parent::toArray(); // TODO: Change the autogenerated stub
        }

        $hidden = [];
        $append = [];
        $mutated = $this->getMutatedAttributes();
        foreach ($this->attributeAlias as $attribute => $column) {
            if (! is_array($column)) {
                if (isset($this->attributes[$column])) {
                    continue;
                }

                if (in_array($attribute, $mutated)) {
                    $append[] = $attribute;
                } else {
                    $this->attributes[$attribute] = $this->attributes[$column];
                }
                $hidden[] = $column;

                continue;
            }
            if (! empty(array_diff($column, array_keys($this->attributes)))) {
                continue;
            }
            if (in_array($attribute, $mutated)) {
                $append[] = $attribute;
            } else {
                $value = [];
                foreach ($column as $single) {
                    $value[] = $this->attributes[$single];
                }
                $this->attributes[$attribute] = implode(',', $value);
            }
            $hidden = array_merge($hidden, $column);
        }

        $hidden = array_diff($hidden, $this->keepColumnVisible);
        $this->makeHidden($hidden);
        $this->append($append);

        return parent::toArray(); // TODO: Change the autogenerated stub
    }
}

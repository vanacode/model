<?php

namespace Vanacode\Model\Traits;

use Vanacode\Model\Model;
use Vanacode\Support\Exceptions\DynamicClassPropertyException;

trait ModelPropertyTrait
{
    protected Model $model;

    public function setModel(Model $model): self
    {
        $this->model = $model;

        return $this;
    }

    /**
     * if $model argument is null based $modelClass argument or modelClass() method dynamically make model
     * then set $model property
     *
     * @throws DynamicClassPropertyException
     */
    public function setModelBy(?Model $model, string $modelClass = '', array $data = []): self
    {
        if (is_null($model)) {
            $model = $this->makeModel($modelClass, $data);
        }

        return $model ? $this->setModel($model) : $this;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * if $model property is set. based $modelClass argument or modelClass() method dynamically make model
     * then return it
     *
     * @throws DynamicClassPropertyException
     */
    public function getModelBy(string $modelClass, array $data = []): ?Model
    {
        return $this->isSetModel() ? $this->getModel() : $this->makeModel($modelClass, $data);
    }

    public function isSetModel(): bool
    {
        return isset($this->model);
    }

    /**
     * based $modelClass argument or modelClass() method dynamically make model
     *
     * @throws DynamicClassPropertyException
     */
    public function makeModel(string $modelClass, array $data = []): ?Model
    {
        $modelClass = $modelClass ?: $this->modelClass();
        if (! array_key_exists('nullable', $data)) {
            $data['nullable'] = true;
        }

        return $this->makePropertyInstance('model', $modelClass, 'Models', '', $data);
    }

    public function modelClass(): string
    {
        return Model::class;
    }
}

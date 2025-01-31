<?php

namespace Vanacode\Model\Traits;

use Illuminate\Database\Eloquent\Model;
use Vanacode\Model\Interfaces\ModelInterface;
use Vanacode\Support\Traits\DynamicClassTrait;

/**
 * @mixin DynamicClassTrait
 */
trait ModelPropertyTrait
{
    protected ModelInterface|Model $model;

    public function setModel(ModelInterface|Model $model): self
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Set model property
     *
     * if $model argument is not null
     * otherwise make model instance dynamically based caller sub folders first match and set it
     */
    public function setModelBy(ModelInterface|Model|null $model, array $data = []): self
    {
        $model = $model ?? $this->makeModel($data);

        return $model ? $this->setModel($model) : $this;
    }

    public function getModel(): ModelInterface|Model
    {
        return $this->model;
    }

    /**
     * get model instance
     *
     * return property $model if is set,
     * otherwise make model instance dynamically based caller sub folders first match and return it
     */
    public function getModelBy(array $data = []): ModelInterface|Model|null
    {
        return $this->isSetModel() ? $this->getModel() : $this->makeModel($data);
    }

    public function isSetModel(): bool
    {
        return isset($this->model);
    }

    /**
     * make model instance dynamically based caller sub folders first match
     */
    public function makeModel(array $data = []): ModelInterface|Model|null
    {
        return $this->makeClassDynamically('Models', '', $data);
    }
}

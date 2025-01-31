<?php

namespace Vanacode\Model\Actions;

class StaticAction extends Action
{
    public function __construct(public readonly string $modelClass, string $name, string $resource, string $subResource, array $options)
    {
        parent::__construct($name, $resource, $subResource, $options);
        if (empty($this->icon)) {
            throw new \Exception($name.' Static action icon is required.');
        }
    }
}

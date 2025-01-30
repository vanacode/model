<?php

namespace Vanacode\Model\Actions;

class StaticActionList extends ActionList
{
    public function __construct(public readonly string $modelClass, string $resource, array $actions = [], string $subResource = '')
    {
        parent::__construct($resource, $actions, $subResource);
    }

    protected function canDoAction(string $action, array $options): bool
    {
        return true;
    }

    protected function defaultActions(): array
    {
        $actions = $this->modelClass::getStaticActions();
        $traitActions = $this->getTraitActions();
        $allActions = array_merge($traitActions, [$actions]);

        return $this->mergeActions($allActions);
    }

    protected function getTraitActions(): array
    {
        $methods = (new $this->modelClass)->getMethodsMatch('getStaticActionsBy');
        $actions = [];
        foreach ($methods as $method) {
            $actions[] = $this->modelClass::$method();
        }

        return $actions;
    }

    protected function getRouteDynamicParams(array $options): array
    {
        return [];
    }

    protected function makeAction(string $action, array $options): StaticAction
    {
        return new StaticAction($this->modelClass, $action, $this->resource, $options, $this->subResource);
    }

    protected function getActionOptions(): array
    {
        return config('vn_model.static_actions', []);
    }
}

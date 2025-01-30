<?php

namespace Vanacode\Model\Actions;

use Illuminate\Support\Arr;
use Vanacode\Model\Interfaces\ModelInterface;

class ItemActionList extends ActionList
{
    public function __construct(protected readonly ModelInterface $item, string $resource, array $actions = [], string $subResource = '')
    {
        parent::__construct($resource, $actions, $subResource);
    }

    protected function canDoAction(string $action, array $options): bool
    {
        return $this->item->canDoAction($action, $options);
    }

    protected function defaultActions(): array
    {
        $actions = $this->item->getActions();
        $traitActions = $this->getTraitActions();
        $allActions = array_merge([$actions], $traitActions);

        return $this->mergeActions($allActions);
    }

    protected function makeAction(string $action, array $options): ItemAction
    {
        return new ItemAction($this->item, $action, $this->resource, $options, $this->subResource);
    }

    protected function getActionOptions(): array
    {
        return config('vn_model.item_actions', []);
    }

    protected function getRouteDynamicParams(array $options): array
    {
        $itemParameters = Arr::pull($options, 'item_parameters', []);
        if (empty($itemParameters)) {
            return [$this->item->getRouteKey()];
        }
        $routeParams = [];
        foreach ($itemParameters as $parameter => $attribute) {
            if (is_numeric($parameter)) {
                $parameter = $attribute;
            }
            $routeParams[$parameter] = object_get($this->item, $attribute);
        }

        return $routeParams;
    }

    protected function getTraitActions(): array
    {
        if ($this->item->skipTraitAction) {
            return [];
        }

        $methods = $this->item->getMethodsMatch('getActionsBy');
        $actions = [];
        foreach ($methods as $method) {
            $actions[] = $this->item->$method();
        }

        return $actions;
    }
}

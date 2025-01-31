<?php

namespace Vanacode\Model\Actions;

use Vanacode\Model\Interfaces\ActionInterface;

class ItemActionList extends ActionList
{
    public function __construct(protected readonly ActionInterface $item, string $resource, string $subResource = '', array $actions = [])
    {
        parent::__construct($resource, $subResource, $actions);
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
        return new ItemAction($this->item, $action, $this->resource, $this->subResource, $options);
    }

    protected function getTraitActions(): array
    {
        if ($this->item->skipTraitActions()) {
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

<?php

namespace Vanacode\Model\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;
use Vanacode\Model\Interfaces\ActionInterface;

class ItemAction extends Action
{
    public function __construct(protected readonly ActionInterface $item, string $name, string $resource, string $subResource, array $options)
    {
        parent::__construct($name, $resource, $subResource, $options);
    }

    protected function getConfigOptions(string $name): array
    {
        return config('vn_model.item_actions.'.$name, []);
    }

    protected function canDoAction(): bool
    {
        return $this->item->canDoAction($this->action);
    }

    protected function getLocaleReplacements(): array
    {
        return [
            'item' => $this->item->getMainKey(),
            'resource' => Lang::resourceSingular($this->resource),
        ];
    }

    protected function getRouteDynamicParams(): array
    {
        $itemParameters = Arr::get($this->options, 'item_parameters', []);
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

    protected function getDynamicLabel(): ?string
    {
        $attribute = Arr::get($this->options, 'attribute_label', '');

        return $attribute ? $this->item->$attribute : null;
    }

    protected function getActionLabel(): string
    {
        return $this->icon ? '' : $this->getDefaultActionLabel();
    }

    protected function makeHtml(): string
    {
        $viewData = [
            'action' => $this,
            'resource' => $this->resource,
            'item' => $this->item,
        ];
        if ($this->component) {
            $viewData['component'] = $this->component;

            return $this->renderComponent('dynamic-component', $viewData);
        }
        if ($this->confirmation) {
            return $this->renderComponent('actions.confirmation', $viewData);
        }
        if ($this->isLink) {
            return $this->renderComponent('actions.link', $viewData);
        }

        return '';
    }
}

<?php

namespace Vanacode\Model\Actions;

class StaticAction extends Action
{
    public function __construct(public readonly string $modelClass, string $action, string $resource, string $subResource, array $options)
    {
        parent::__construct($action, $resource, $subResource, $options);
        if (empty($this->icon)) {
            throw new \Exception($action.' Static action icon is required.');
        }
    }

    protected function getConfigOptions(string $action): array
    {
        return config('vn_model.static_actions.'.$action, []);
    }

    protected function makeHtml(): string
    {
        $useLabel = $this->options['use_label'] ?? false;
        $viewData = [
            'action' => $this,
            'resource' => $this->resource,
            'modelClass' => $this->modelClass,
            'useLabel' => $useLabel,
            'class' => $useLabel ? 'btn btn-primary mr-2' : 'mx-1',
            'static' => true,
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

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

    protected function getConfigOptions($name): array
    {
        return config('vn_model.static_actions.'.$name, []);
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

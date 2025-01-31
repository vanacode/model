<?php

namespace Vanacode\Model\Actions;

use Illuminate\Support\Collection;

abstract class ActionList
{
    protected Collection $processed;

    public function __construct(protected string $resource, protected string $subResource = '', protected readonly array $actions = []) {}

    abstract protected function defaultActions(): array;

    abstract protected function makeAction(string $action, array $options): Action;

    public function all(): Collection
    {
        return $this->getProcessed();
    }

    protected function getProcessed(): Collection
    {
        if (! isset($this->processed)) {
            $this->processed = $this->processActions();
        }

        return $this->processed;
    }

    protected function processActions(): Collection
    {
        $actions = $this->actions ?: $this->defaultActions();

        $processed = [];
        foreach ($actions as $action => $options) {
            $options = $this->processActionOptions($action, $options);
            $action = $options['action'];
            $actionObj = $this->makeAction($action, $options);
            if ($actionObj->canShow()) {
                $processed[] = $actionObj;
            }
        }

        return collect($processed);
    }

    protected function mergeActions(array $actionList): array
    {
        $actions = [];
        $skipActions = [];
        foreach ($actionList as $actionData) {
            foreach ($actionData as $action => $options) {
                if ($options === false) {
                    $skipActions[] = $action;

                    continue;
                }
                $options = $this->processActionOptions($action, $options);
                $action = $options['action'];
                $actions[$action] = $actions[$action] ?? [];
                $actions[$action] = array_merge($actions[$action], $options);
            }
        }

        foreach ($skipActions as $action) {
            unset($actions[$action]);
        }

        return $actions;
    }

    protected function processActionOptions(string|int $action, array|string|bool $options): array
    {
        if (is_string($options)) {
            $options = [
                'action' => $options,
            ];
        } elseif (is_bool($options)) {
            $options = [];
        }

        $options['action'] = $options['action'] ?? $action;

        return $options;
    }
}

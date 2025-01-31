<?php

namespace Vanacode\Model\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Vanacode\Resource\ResourceRoute;
use Vanacode\Support\Traits\CheckAccess;

abstract class ActionList
{
    use CheckAccess;

    protected Collection $processed;

    public function __construct(protected string $resource, protected string $subResource = '', protected readonly array $actions = []) {}

    abstract protected function defaultActions(): array;

    abstract protected function makeAction(string $action, array $options): Action;

    abstract protected function getActionOptions(): array;

    abstract protected function canDoAction(string $action, array $options): bool;

    abstract protected function getRouteDynamicParams(array $options): array;

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
        $actionOptions = $this->getActionOptions();

        $processed = [];
        foreach ($actions as $action => $options) {
            $options = $this->processActionOptions($action, $options);
            $action = $options['action'];
            $options = array_merge($actionOptions[$action] ?? [], $options);

            if (! $this->checkAccess($options)) {
                continue;
            }

            if (! empty($options['is_link'])) {
                $processed[] = $this->makeAction($action, $options);

                continue;
            }
            $options = $this->processLinkOptions($action, $options);
            if (empty($options['href']) || $this->isSameOrInvalidLink($options) || ! $this->canDoAction($action, $options)) {
                continue;
            }

            $processed[] = $this->makeAction($action, $options);
        }

        return collect($processed);
    }

    protected function processLinkOptions(string $action, array $options): array
    {
        if (! empty($options['href'])) {
            return $options;
        }
        if (! empty($options['uri'])) {
            $options['href'] = url(Arr::pull($options, 'uri'));

            return $options;
        }
        if (! empty($options['route'])) {
            $route = Arr::pull($options, 'route');
        } else {
            $action = Arr::pull($options, 'action_route', $action);
            $fullResource = $this->resource;
            if ($this->subResource) {
                $fullResource .= '.'.$this->subResource;
            }

            $route = ResourceRoute::resourceRoute($fullResource, $action);
        }
        if ($route) {
            $options['href'] = $this->getHrefByRoute($route, $options);
        }

        return $options;
    }

    protected function getHrefByRoute(string $route, $options): string
    {
        $routeParams = $this->getRouteDynamicParams($options);
        $query = Arr::pull($options, 'query', []);
        $routeParams = array_merge($routeParams, $query);

        return ResourceRoute::routeUrl($route, $routeParams);
    }

    protected function isSameOrInvalidLink(array $options): bool
    {
        $method = $options['http_method'] ?? 'get';

        // TODO maybe later will permit same query param
        return Str::before($options['href'], '?') == URL::current() && strtolower($method) == 'get';
    }

    protected function mergeActions(array $actionList): array
    {
        // TODO improve later $skipActions
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
                if (in_array($action, $skipActions)) {
                    continue;
                }

                $actions[$action] = $actions[$action] ?? [];
                $actions[$action] = array_merge($actions[$action], $options);
            }
        }

        return $actions;
    }

    protected function processActionOptions(string|int $action, array|string $options): array
    {
        if (is_string($options)) {
            $options = [
                'action' => $options,
            ];
        }

        $options['action'] = $options['action'] ?? $action;

        return $options;
    }
}

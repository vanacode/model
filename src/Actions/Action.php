<?php

namespace Vanacode\Model\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Js;
use Illuminate\Support\Str;
use Vanacode\Resource\ResourceRoute;
use Vanacode\Support\Traits\BladeTrait;
use Vanacode\Support\VnStr;

abstract class Action
{
    use BladeTrait;

    public string $id;

    public string $component;

    public string $href;

    public string $icon;

    public string $html;

    public string $label;

    public string $title;

    public string $httpMethod;

    public string $target;

    public bool $isLink;

    public array $confirmation;

    public array $class;

    public array $style;

    public function __construct(public string $action, protected string $resource, protected string $subResource, public array $options)
    {
        $this->setConfigOptions();
        $this->component = $this->options['component'] ?? '';
        $this->icon = $this->makeIcon();
        $this->html = $this->options['html'] ?? '';
        $this->target = $this->options['target'] ?? ''; // TODO default target
        $this->label = $this->getLabel();
        $this->title = $this->getTitle();
        $this->isLink = $this->options['is_link'] ?? true;
        $this->confirmation = $this->wrapConfirmation();
        $this->httpMethod = $this->options['http_method'] ?? '';
        if (empty($this->httpMethod) && $this->confirmation) {
            $this->httpMethod = 'DELETE';
        }
        $this->href = $this->isLink ? $this->getHref() : '';
        $this->class = $this->getArrayBy('class');
        $this->style = $this->getArrayBy('style');
    }

    abstract protected function getConfigOptions(string $action): array;

    abstract protected function makeHtml(): string;

    abstract protected function canDoAction(): bool;

    public static function getDefaultConfirmationTexts(): array
    {
        return [
            'heading' => Lang::common('confirmation.default.heading'),
            'body' => Lang::common('confirmation.default.body_short'),
            'close' => Lang::common('confirmation.default.close'),
            'confirm' => Lang::common('confirmation.default.confirm'),
        ];
    }

    public function canShow(): bool
    {
        if (! $this->canDoAction()) {
            return false;
        }
        if (! $this->isLink) {
            return true;
        }

        if (! $this->href) {
            return false;
        }

        // skip when url is same
        $method = $this->httpMethod ?: 'get';

        return Str::before($this->href, '?') != URL::current() || strtoupper($method) != request()->getMethod();
    }

    public function render(): string
    {
        if (! $this->canShow()) {
            return '';
        }

        return $this->makeHtml();
    }

    public function jsEncodedConfirmation(): string
    {
        return Js::encode(Arr::only($this->confirmation, ['body', 'heading', 'confirm', 'close', 'btn_class']));
    }

    protected function setConfigOptions(): void
    {
        $key = VnStr::forceSnake($this->action);
        $config = $this->getConfigOptions($key);
        $this->options = array_merge($config, $this->options);
    }

    protected function makeIcon(): string
    {
        $icon = $this->options['icon'] ?? '';

        return $icon ? sprintf('<i class="%s"></i>', $icon) : '';
    }

    protected function getLabel(): string
    {
        $parts = [];
        if ($dynamicLabel = $this->getDynamicLabel()) {
            $parts[] = $dynamicLabel;
        }

        if (!empty($this->options['label'])) {
            $parts[] = $this->options['label'];
        } elseif (!empty($this->options['label_key'])) {
            $parts[] = __($this->options['label_key'], $this->getLocaleReplacements());
        }

        $parts = array_filter($parts);
        if (! empty($parts)) {
            return implode(' ', $parts);
        }

        return $this->detectActionLabel();
    }

    protected function getTitle(): string
    {
        if (!empty($this->options['title'])) {
            return $this->options['title'];
        }

        $titleKey = $this->options['title_key'] ?? '';

        return $titleKey ? __($titleKey, $this->getLocaleReplacements()) : $this->getDefaultActionLabel();
    }

    protected function getDynamicLabel(): ?string
    {
        return null;
    }

    protected function detectActionLabel(): string
    {
        return $this->getDefaultActionLabel();
    }

    protected function getDefaultActionLabel(): string
    {
        $key = '';
        if ($this->subResource) {
            $key = $this->subResource.'.';
        }
        $key .= $this->action;

        return Lang::actionResource($this->resource, $key);
    }

    protected function wrapConfirmation(): array
    {
        $confirmation = $this->options['confirmation'] ?? false;
        if (empty($confirmation)) {
            return [];
        }
        $confirmation = is_bool($confirmation) ? [] : $confirmation;
        $confirmation['modal'] = $confirmation['modal'] ?? 'confirmation-modal';

        $action = VnStr::forceSnake($this->action);
        $hasCustomBody = Lang::commonHas('confirmation.'.$action.'.body');
        $bodyKey = $hasCustomBody ? 'confirmation.'.$action.'.body' : 'confirmation.default.body';
        $bodyKey = $confirmation['body'] ?? $bodyKey;

        $confirmation['body'] = Lang::common($bodyKey, $this->getLocaleReplacements());
        $confirmation = $this->setConfirmationText($confirmation, 'heading');
        $confirmation = $this->setConfirmationText($confirmation, 'confirm');

        return $this->setConfirmationText($confirmation, 'close');
    }

    protected function getLocaleReplacements(): array
    {
        return [
            'resource' => Lang::resourceSingular($this->resource),
        ];
    }

    protected function setConfirmationText(array $confirmation, string $key): array
    {
        if (! empty($confirmation[$key])) {
            return __($confirmation[$key], $this->getLocaleReplacements());
        }
        $action = VnStr::forceSnake($this->action);
        if (Lang::commonHas('confirmation.'.$action.'.'.$key)) {
            $confirmation[$key] = Lang::common('confirmation.'.$action.'.'.$key);
        }

        return $confirmation;
    }

    protected function getHref(): string
    {
        if (! empty($this->options['href'])) {
            return $this->options['href'];
        }
        if (! empty($this->options['uri'])) {
            return url($this->options['uri']);
        }
        if (! empty($this->options['route'])) {
            $route = $this->options['route'];
        } else {
            $action = $this->options['action_route'] ?? $this->action;
            $fullResource = $this->resource;
            if ($this->subResource) {
                $fullResource .= '.'.$this->subResource;
            }

            $route = ResourceRoute::resourceRoute($fullResource, $action);
        }

        return $route ? $this->getHrefByRoute($route) : '';
    }

    protected function getHrefByRoute(string $route): string
    {
        $routeParams = $this->getRouteDynamicParams();
        $query = $this->options['query'] ?? [];
        $routeParams = array_merge($routeParams, $query);

        return ResourceRoute::routeUrl($route, $routeParams);
    }

    protected function getRouteDynamicParams(): array
    {
        return [];
    }

    protected function getArrayBy(string $key): array
    {
        $value = $this->options[$key] ?? null;

        return Arr::wrap($value);
    }
}

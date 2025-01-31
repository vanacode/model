<?php

namespace Vanacode\Model\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Js;
use Vanacode\Support\VnStr;

abstract class Action
{
    public string $name;

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

    public function __construct(string $name, protected string $resource, protected string $subResource, protected readonly array $options)
    {
        $this->name = $name;
        $additional = $this->options;
        $this->component = Arr::pull($additional, 'component', '');
        $this->icon = $this->makeIcon(Arr::pull($additional, 'icon', ''));
        $this->html = Arr::pull($additional, 'html', '');
        $this->target = Arr::pull($additional, 'target', ''); // TODO default target
        $this->label = $this->getLabel($additional);
        $this->title = $this->getTitle($additional);
        $this->isLink = Arr::pull($additional, 'is_link', true);
        $this->confirmation = $this->wrapConfirmation($additional);
        $this->httpMethod = Arr::pull($additional, 'http_method', '');
        if (empty($this->httpMethod) && $this->confirmation) {
            $this->httpMethod = 'DELETE';
        }
        $this->href = $this->isLink ? Arr::pull($additional, 'href') : '';
        $this->class = $this->wrapArray(Arr::pull($additional, 'class'));
        $this->style = $this->wrapArray(Arr::pull($additional, 'style'));
    }

    public static function getDefaultConfirmationTexts(): array
    {
        return [
            'heading' => Lang::common('confirmation.default.heading'),
            'body' => Lang::common('confirmation.default.body_short'),
            'close' => Lang::common('confirmation.default.close'),
            'confirm' => Lang::common('confirmation.default.confirm'),
        ];
    }

    public function jsEncodedConfirmation(): string
    {
        return Js::encode(Arr::only($this->confirmation, ['body', 'heading', 'confirm', 'close', 'btn_class']));
    }

    protected function makeIcon(string $icon): string
    {
        return $icon ? sprintf('<i class="%s"></i>', $icon) : '';
    }

    protected function getLabel(array $options): string
    {
        $parts = [];
        if ($dynamicLabel = $this->getDynamicLabel($options)) {
            $parts[] = $dynamicLabel;
        }
        if ($label = Arr::pull($options, 'label', '')) {
            $parts[] = __($label, $this->getLocaleReplacements());
        }

        $parts = array_filter($parts);
        if (! empty($parts)) {
            return implode(' ', $parts);
        }

        return $this->getActionLabel();
    }

    protected function getTitle(array $options): string
    {
        $title = Arr::pull($options, 'title');

        return $title ? __($title, $this->getLocaleReplacements()) : $this->getDefaultActionLabel();
    }

    protected function getDynamicLabel($options): ?string
    {
        return null;
    }

    protected function getActionLabel(): string
    {
        return $this->getDefaultActionLabel();
    }

    protected function getDefaultActionLabel(): string
    {
        $key = '';
        if ($this->subResource) {
            $key .= VnStr::slugToSnake($this->subResource).'.';
        }
        $key .= VnStr::slugToSnake($this->name);

        return Lang::actionResource($this->resource, $key);
    }

    protected function wrapConfirmation(array $options): array
    {
        $confirmation = Arr::pull($options, 'confirmation', []);
        if (empty($confirmation)) {
            return $confirmation;
        }
        $confirmation = is_bool($confirmation) ? [] : $confirmation;
        $confirmation['modal'] = $confirmation['modal'] ?? 'confirmation-modal';

        $name = VnStr::forceSnake($this->name);
        $hasCustomBody = Lang::commonHas('confirmation.'.$name.'.body');
        $bodyKey = $hasCustomBody ? 'confirmation.'.$name.'.body' : 'confirmation.default.body';
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
        $name = VnStr::forceSnake($this->name);
        if (Lang::commonHas('confirmation.'.$name.'.'.$key)) {
            $confirmation[$key] = Lang::common('confirmation.'.$name.'.'.$key);
        }

        return $confirmation;
    }

    protected function wrapArray(array|string|null $value): array
    {
        return Arr::wrap($value);
    }
}

<?php

namespace Vanacode\Model\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;
use Vanacode\Model\Interfaces\ModelInterface;

class ItemAction extends Action
{
    public function __construct(protected readonly ModelInterface $item, string $name, string $resource, string $subResource, array $options)
    {
        parent::__construct($name, $resource, $subResource, $options);
    }

    protected function getLocaleReplacements(): array
    {
        return [
            'item' => $this->item->getMainKey(),
            'resource' => Lang::resourceSingular($this->resource),
        ];
    }

    protected function getDynamicLabel($options): ?string
    {
        $attribute = Arr::get($options, 'attribute_label', '');

        return $attribute ? $this->item->$attribute : null;
    }

    protected function getActionLabel(): string
    {
        return $this->icon ? '' : $this->getDefaultActionLabel();
    }
}

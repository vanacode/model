<?php

namespace Vanacode\Model\Traits;

use Vanacode\Model\Interfaces\MainKeyInterface;
use Vanacode\Resource\ResourceRoute;

// TODO later support also attribute options
/**
 * @mixin MainKeyInterface
 */
trait MainKeyTrait
{
    protected string $mainKey;

    public function getMainKeyName(): string
    {
        return $this->mainKey ?? $this->getKeyName();
    }

    public function getMainKey(): string|int|null
    {
        return $this->getAttribute($this->getMainKeyName());
    }

    public function getMainKeyWithId(): string
    {
        return $this->getMainKey().': '.$this->getMainKey();
    }

    /**
     * Called dynamically by Attributes
     */
    public function getMainKeyTraitAttributeOptions(): array
    {
        $mainKey = $this->getMainKeyName();

        return [
            $mainKey => [
                'method' => 'getMainKey',
            ],
        ];
    }

    public function getSelfLinkAttribute(): string
    {
        $value = $this->getMainKey();

        return $this->getLinkByValue($value);
    }

    public function getSelfLinkBy(string $attribute): string
    {
        $value = $this->$attribute;

        return $this->getLinkByValue($value);
    }

    public function getDeletedSelfLinkAttribute(): string
    {
        $mainKey = $this->getMainKey();
        if (! $this->exists) {
            return '<span class="text-danger">'.$mainKey.'</span>';
        }

        $link = $this->getLinkByValue($this->getMainKey(), 'show-deleted', ['class' => 'text-warning']);
        $link = $mainKey == $link ? '<span class="text-warning">'.$link.'</span>' : $link;

        return $this->renderAction('restore').$link;
    }

    public function getLinkByValue(string $value, string $action = 'show', array $options = []): string
    {
        if (! $this->canDoAction($action)) {
            return $value;
        }
        $url = ResourceRoute::resourceUrl($this->getResource(), $action, $this->getRouteKey());

        return $this->renderLink($url, $value, $options);
    }
}

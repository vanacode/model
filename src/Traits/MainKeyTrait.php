<?php

namespace Vanacode\Model\Traits;

// TODO later support also attribute options
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
    public function getMainKeyTraitAttributeOptions()
    {
        $mainKey = $this->getMainKeyName();

        return [
            $mainKey => [
                'method' => 'getMainKey',
            ],
        ];
    }
}

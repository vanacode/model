<?php

namespace Vanacode\Model\Interfaces;

interface MainKeyInterface
{
    public function getMainKeyName(): string;

    public function getMainKey(): string|int|null;

    public function getMainKeyWithId(): string;

    /**
     * Called dynamically by Attributes
     */
    public function getMainKeyTraitAttributeOptions(): array;

    public function getSelfLinkAttribute(): string;

    public function getSelfLinkBy(string $attribute): string;

    public function getDeletedSelfLinkAttribute(): string;

    public function getLinkByValue(string $value, string $action = 'show', array $options = []): string;
}

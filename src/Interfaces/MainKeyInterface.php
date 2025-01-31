<?php

namespace Vanacode\Model\Interfaces;

interface MainKeyInterface
{
    public function getMainKeyName(): string;

    public function getMainKey(): string|int|null;

    public function getMainKeyWithId(): string;

    public function getMainKeyTraitAttributeOptions();
}

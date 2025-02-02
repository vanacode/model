<?php

namespace Vanacode\Model\Interfaces;

interface ResourceInterface
{
    public static function setResource(string $resource): void;

    public function getResource(): string;
}

<?php

namespace Vanacode\Model\Interfaces;

interface MethodMatchInterface
{
    public function getMethodsMatch(string $prefix, string $suffix = '', array|string $exclude = [], array|string $callFirst = []): array;

    public function getTraitMatchMethods(string $prefix, string $suffix = ''): array;
}

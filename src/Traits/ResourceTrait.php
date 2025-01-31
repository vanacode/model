<?php

namespace Vanacode\Model\Traits;

use Illuminate\Support\Str;

trait ResourceTrait
{
    protected static array $resources = [];

    public static function setResource(string $resource): void
    {
        static::$resources[static::class] = $resource;
    }

    public function getResource(): string
    {
        if (! isset(static::$resources[static::class])) {
            $resource = $this->getTable();
            static::$resources[static::class] = Str::slug($resource);
        }

        return static::$resources[static::class];
    }
}

<?php

namespace Vanacode\Model\Traits;

use Vanacode\Model\Interfaces\ModelInterface;
use Vanacode\Support\Traits\BladeTrait;
use Vanacode\Support\Traits\MethodMatchTrait;
use Vanacode\Support\Traits\RouteScopeTrait;

/**
 * @mixin ModelInterface
 */
trait ModelTrait
{
    use ActionTrait,
        AttributeTrait,
        BladeTrait,
        MainKeyTrait,
        MethodMatchTrait,
        PaginateableTrait,
        RelationshipsTrait,
        ResourceTrait,
        RouteScopeTrait,
        ShowableTrait;

    public function selfRelationResourceAndKeys(): array
    {
        return [];
    }

    /**
     * @return mixed|string
     */
    public function getMorphClass()
    {
        return $this->getResource();
    }

    public function hasMethod(string $method): bool
    {
        return method_exists($this, $method);
    }

    protected function colorView(string $color): string
    {
        return sprintf('<pre style="background-color: %s; max-width: 100px"> </pre>', $color);
    }
}

<?php

namespace Vanacode\Model\Traits;

use Vanacode\Model\Attributes\AttributeList;
use Vanacode\Model\Interfaces\PaginateableInterface;

/**
 * @mixin PaginateableInterface
 */
trait PaginateableTrait
{
    /**
     * see Attribute __construct
     */
    protected array $paginateable = [];

    /**
     * values like paginateable
     */
    protected array $routePaginateable = [];

    public function getPaginateAble(): array
    {
        return $this->getRouteScopedOptions($this->routePaginateable, $this->paginateable);
    }

    public function getPaginateableAttributes(): AttributeList
    {
        return new AttributeList($this, $this->getResource(), $this->getPaginateAble());
    }
}

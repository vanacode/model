<?php

namespace Vanacode\Model\Traits;

use Vanacode\Model\Attributes\AttributeList;

trait PaginateableTrait
{
    /**
     * see Attribute __construct
     */
    protected array $paginateable = [];

    public function getPaginateAble(): array
    {
        return $this->paginateable;
    }

    public function getPaginateableAttributes(): AttributeList
    {
        return new AttributeList($this, $this->getResource(), $this->getPaginateAble());
    }
}

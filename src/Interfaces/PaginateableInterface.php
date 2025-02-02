<?php

namespace Vanacode\Model\Interfaces;

use Vanacode\Model\Attributes\AttributeList;

interface PaginateableInterface
{
    public function getPaginateAble(): array;

    public function getPaginateableAttributes(): AttributeList;
}

<?php

namespace Vanacode\Model\Interfaces;

use Vanacode\Model\Attributes\AttributeList;

interface ShowableInterface
{
    public function getShowable(): array;

    public function getShowableOptions(): AttributeList;
}

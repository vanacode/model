<?php

namespace Vanacode\Model\Traits;

use Vanacode\Model\Attributes\AttributeList;

trait ShowableTrait
{
    /**
     * see Attribute __construct
     */
    protected array $showable = [];
    protected array $routeShowable = [];

    public function getShowable(): array
    {
        return $this->getRouteScopedOptions($this->routeShowable, $this->showable);
    }

    public function getShowableOptions(): AttributeList
    {
        return new AttributeList($this, $this->getResource(), $this->getShowable());
    }
}

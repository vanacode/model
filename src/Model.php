<?php

namespace Vanacode\Model;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Vanacode\Model\Interfaces\ActionInterface;
use Vanacode\Model\Interfaces\AttributeInterface;
use Vanacode\Model\Interfaces\ModelInterface;
use Vanacode\Model\Traits\ModelTrait;

class Model extends BaseModel implements ActionInterface, AttributeInterface, ModelInterface
{
    use ModelTrait;
}

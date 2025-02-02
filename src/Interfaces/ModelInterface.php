<?php

namespace Vanacode\Model\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Vanacode\Support\Interfaces\MethodMatchInterface;

/**
 * @mixin Model
 */
interface ModelInterface extends ActionInterface, AttributeInterface, MainKeyInterface, MethodMatchInterface, PaginateableInterface, RelationshipsInterface, ResourceInterface, ShowableInterface {}

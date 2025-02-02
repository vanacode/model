<?php

namespace Vanacode\Model\Interfaces;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
interface ModelInterface extends ActionInterface, AttributeInterface, MainKeyInterface, PaginateableInterface, RelationshipsInterface, ResourceInterface, ShowableInterface {}

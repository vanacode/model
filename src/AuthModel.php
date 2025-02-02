<?php

namespace Vanacode\Model;

use Illuminate\Contracts\Auth\Authenticatable;
use Vanacode\Model\Traits\AuthTrait;

class AuthModel extends Model implements Authenticatable
{
    use AuthTrait;
}

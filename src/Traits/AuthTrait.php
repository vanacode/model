<?php

namespace Vanacode\Model\Traits;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Vanacode\Model\Interfaces\AuthInterface;

/**
 * @mixin AuthInterface
 */
trait AuthTrait
{
    use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail;
}

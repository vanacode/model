<?php

namespace Vanacode\Model\Interfaces;

use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

interface AuthInterface extends AuthenticatableContract, AuthorizableContract, CanResetPasswordContract {}

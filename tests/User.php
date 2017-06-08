<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Marievych\Roles\Traits\HasRoleAndPermission;

class User extends Model
{
    use HasRoleAndPermission;
}

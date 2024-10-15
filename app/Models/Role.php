<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as ModelsRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends ModelsRole
{
    use HasFactory;
}

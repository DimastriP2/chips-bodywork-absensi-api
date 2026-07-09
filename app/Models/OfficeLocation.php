<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeLocation extends Model
{
    protected $fillable = [
        'office_name',
        'latitude',
        'longitude',
        'radius',
    ];
}
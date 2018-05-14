<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    protected $fillable = ['start_date', 'end_date', 'property_id'];
}

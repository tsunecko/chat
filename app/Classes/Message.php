<?php

namespace App\Classes;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['name', 'message'];

}

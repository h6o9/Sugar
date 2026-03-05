<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CiboExpress extends Model
{
    use HasFactory;

    protected $table = 'cibo_express';

    protected $fillable = [
        'title',
        'description',
        'image',
        'status'
    ];
}
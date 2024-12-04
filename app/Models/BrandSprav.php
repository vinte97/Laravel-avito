<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandSprav extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'brand_sprav';
    protected $guarded = [];
}

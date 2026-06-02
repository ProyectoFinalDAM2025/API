<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
        protected $fillable = [
        'INE',
        'name',
    ];

    // public function municipalities(): HasMany
    // {
    //     return $this->hasMany(Municipality::class);
    // }

}

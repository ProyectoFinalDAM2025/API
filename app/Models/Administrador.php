<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Administrador extends Model
{
    //
    protected $primaryKey = 'IDAdministrador';
    public $timestamps = true;
    protected $fillable = ['IDUsuario', 'Nombre', 'Apellido', 'FotoPerfil', 'Activo'];
    protected $hidden = ['created_at','updated_at'];
    protected $casts = [
        'Activo' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class, 'IDUsuario'); }
}

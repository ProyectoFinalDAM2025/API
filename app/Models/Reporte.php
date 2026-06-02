<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    protected $primaryKey = 'IDReporte';
    protected $fillable = ['TipoEntidad', 'IDEntidad', 'IDUsuario', 'Motivo', 'Descripcion', 'Estado', 'FechaReporte'];
    protected $hidden = ['created_at', 'updated_at'];

    public function user() { return $this->belongsTo(User::class, 'IDUsuario'); }
}

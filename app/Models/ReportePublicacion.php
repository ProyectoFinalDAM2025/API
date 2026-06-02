<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportePublicacion extends Model
{
    protected $primaryKey = 'IDReportePublicacion';
    protected $fillable = ['IDPublicacion', 'IDUsuario', 'Motivo', 'Descripcion', 'Estado', 'FechaReporte'];
    protected $hidden = ['created_at', 'updated_at'];

    public function publicacion() { return $this->belongsTo(Publicacion::class, 'IDPublicacion'); }
    public function user() { return $this->belongsTo(User::class, 'IDUsuario'); }
}

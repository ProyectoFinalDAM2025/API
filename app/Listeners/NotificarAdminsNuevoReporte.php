<?php

namespace App\Listeners;

use App\Events\ReporteCreado;
use App\Models\Notificacion;
use App\Models\User;

class NotificarAdminsNuevoReporte
{
    public function handle(ReporteCreado $event)
    {
        $reporte = $event->reporte;
        $tipo = $this->nombreTipo($reporte->TipoEntidad);
        $ruta = $this->rutaReporte($reporte->TipoEntidad, $reporte->IDEntidad);

        $admins = User::where('rol', 'admin')->get();

        foreach ($admins as $admin) {
            Notificacion::create([
                'IDUsuario' => $admin->IDUsuario,
                'Titulo' => "Nuevo reporte de {$tipo}",
                'Mensaje' => "Se ha creado un nuevo reporte de {$tipo}. Motivo: {$reporte->Motivo}.",
                'Leido' => false,
                'FechaNotificacion' => now(),
                'Ruta' => $ruta,
            ]);
        }
    }

    private function nombreTipo(?string $tipoEntidad): string
    {
        if ($tipoEntidad === 'Publicacion') {
            return 'publicacion';
        }

        if ($tipoEntidad === 'Oferta') {
            return 'oferta';
        }

        if ($tipoEntidad === 'Usuario') {
            return 'perfil';
        }

        return 'contenido';
    }

    private function rutaReporte(?string $tipoEntidad, ?int $idEntidad): string
    {
        if ($tipoEntidad === 'Publicacion' && $idEntidad) {
            return "/post/{$idEntidad}";
        }

        if ($tipoEntidad === 'Oferta' && $idEntidad) {
            return "/ofertaEmpleo/{$idEntidad}";
        }

        if ($tipoEntidad === 'Usuario' && $idEntidad) {
            return "/usuarios/{$idEntidad}";
        }

        return '/reportes';
    }
}

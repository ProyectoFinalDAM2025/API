<?php

namespace App\Events;

use App\Models\Reporte;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReporteCreado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reporte;

    public function __construct(Reporte $reporte)
    {
        $this->reporte = $reporte;
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ReportePublicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportePublicacionController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'IDPublicacion' => 'required|exists:publicacions,IDPublicacion',
            'Motivo' => 'required|string|max:255',
            'Descripcion' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'StatusCode' => 422,
                'ReasonPhrase' => 'validation errors.',
                'Message' => $validator->errors()->all()
            ], 422);
        }

        $reporte = ReportePublicacion::create([
            'IDPublicacion' => $request->input('IDPublicacion'),
            'IDUsuario' => auth()->id(),
            'Motivo' => $request->input('Motivo'),
            'Descripcion' => $request->input('Descripcion'),
            'FechaReporte' => now(),
        ]);

        return response()->json([
            'StatusCode' => 201,
            'ReasonPhrase' => 'Reporte creado correctamente.',
            'Message' => 'La publicacion ha sido reportada correctamente.',
            'Data' => $reporte
        ], 201);
    }
}

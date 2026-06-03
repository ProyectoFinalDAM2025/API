<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Events\ReporteCreado;
use App\Models\Documento;
use App\Models\Notificacion;
use App\Models\OfertaEmpleo;
use App\Models\Publicacion;
use App\Models\Reporte;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $query = Reporte::with('user:IDUsuario,email,rol')
            ->orderByDesc('FechaReporte');

        if ($request->filled('TipoEntidad')) {
            $query->where('TipoEntidad', $request->input('TipoEntidad'));
        }

        $reportes = $query->get()->map(function ($reporte) {
            $tituloEntidad = null;
            $entidad = null;

            if ($reporte->TipoEntidad === 'Publicacion') {
                $entidad = Publicacion::with([
                        'user:IDUsuario,email,rol',
                        'user.empresa:IDEmpresa,IDUsuario,NombreEmpresa,Foto',
                        'user.desempleado:IDDesempleado,IDUsuario,Nombre,Apellido,Foto',
                        'grupo:IDGrupo,Nombre',
                        'documentos',
                    ])
                    ->where('IDPublicacion', $reporte->IDEntidad)
                    ->first();
                $tituloEntidad = $entidad?->Contenido;
            } elseif ($reporte->TipoEntidad === 'Oferta') {
                $entidad = OfertaEmpleo::with([
                        'empresa:IDEmpresa,IDUsuario,NombreEmpresa,Foto,Ubicacion,SitioWeb,IDSector',
                        'empresa.sector:IDSector,Nombre',
                        'categoria:IDCategoria,Nombre',
                    ])
                    ->where('IDOferta', $reporte->IDEntidad)
                    ->first();
                $tituloEntidad = $entidad?->Titulo;
            } elseif ($reporte->TipoEntidad === 'Usuario') {
                $entidad = User::with([
                        'empresa:IDEmpresa,IDUsuario,NombreEmpresa,Foto,Ubicacion,SitioWeb,IDSector',
                        'empresa.sector:IDSector,Nombre',
                        'desempleado:IDDesempleado,IDUsuario,Nombre,Apellido,Foto,Ubicacion',
                    ])
                    ->where('IDUsuario', $reporte->IDEntidad)
                    ->first();
                $tituloEntidad = $entidad?->empresa?->NombreEmpresa
                    ?: trim(($entidad?->desempleado?->Nombre ?? '') . ' ' . ($entidad?->desempleado?->Apellido ?? ''))
                    ?: $entidad?->email;
            }

            return [
                'IDReporte' => $reporte->IDReporte,
                'TipoEntidad' => $reporte->TipoEntidad,
                'IDEntidad' => $reporte->IDEntidad,
                'IDUsuario' => $reporte->IDUsuario,
                'UsuarioReporta' => $reporte->user?->email,
                'TituloEntidad' => $tituloEntidad,
                'Motivo' => $reporte->Motivo,
                'Descripcion' => $reporte->Descripcion,
                'Estado' => $reporte->Estado,
                'FechaReporte' => $reporte->FechaReporte,
                'Entidad' => $entidad,
            ];
        });

        return response()->json([
            'StatusCode' => 200,
            'ReasonPhrase' => 'Reportes encontrados correctamente',
            'Message' => 'Los reportes han sido encontrados con exito.',
            'Data' => $reportes
        ], 200);
    }

    public function storePublicacion(Request $request)
    {
        return $this->storeEntityReport(
            request: $request,
            tipoEntidad: 'Publicacion',
            idField: 'IDPublicacion',
            existsRule: 'exists:publicacions,IDPublicacion',
            successMessage: 'La publicacion ha sido reportada correctamente.'
        );
    }

    public function storeOferta(Request $request)
    {
        return $this->storeEntityReport(
            request: $request,
            tipoEntidad: 'Oferta',
            idField: 'IDOferta',
            existsRule: 'exists:oferta_empleos,IDOferta',
            successMessage: 'La oferta ha sido reportada correctamente.'
        );
    }

    public function storeUsuario(Request $request)
    {
        return $this->storeEntityReport(
            request: $request,
            tipoEntidad: 'Usuario',
            idField: 'IDUsuarioReportado',
            existsRule: 'exists:users,IDUsuario',
            successMessage: 'El perfil ha sido reportado correctamente.'
        );
    }

    public function destroy(Reporte $reporte)
    {
        $reporte->delete();

        return response()->json([
            'StatusCode' => 200,
            'ReasonPhrase' => 'Reporte eliminado correctamente.',
            'Message' => 'El reporte ha sido eliminado sin afectar la entidad reportada.'
        ], 200);
    }

    public function moderate(Reporte $reporte)
    {
        if ($reporte->TipoEntidad === 'Publicacion') {
            $publicacion = Publicacion::with('documentos')
                ->where('IDPublicacion', $reporte->IDEntidad)
                ->first();

            if (!$publicacion) {
                return $this->moderationNotFound('La publicacion reportada no existe.');
            }

            $this->removePublicacionFiles($publicacion);

            $publicacion->Contenido = '[MODERADO] Esta publicacion fue retirada por incumplir las normas de la plataforma.';
            $publicacion->Archivo = null;
            $publicacion->Thumbnail = null;
            $publicacion->Preview = null;
            $publicacion->TipoArchivo = null;
            $publicacion->save();

            $publicacion->documentos()->delete();
        } elseif ($reporte->TipoEntidad === 'Oferta') {
            $oferta = OfertaEmpleo::where('IDOferta', $reporte->IDEntidad)->first();

            if (!$oferta) {
                return $this->moderationNotFound('La oferta reportada no existe.');
            }

            $oferta->Titulo = '[MODERADO] Oferta retirada';
            $oferta->Descripcion = 'Esta oferta fue retirada por incumplir las normas de la plataforma.';
            $oferta->Estado = 'Cerrada';
            $oferta->save();
        } elseif ($reporte->TipoEntidad === 'Usuario') {
            $usuario = User::with(['empresa', 'desempleado'])
                ->where('IDUsuario', $reporte->IDEntidad)
                ->first();

            if (!$usuario) {
                return $this->moderationNotFound('El perfil reportado no existe.');
            }

            $this->moderateUserProfile($usuario);
        } else {
            return response()->json([
                'StatusCode' => 422,
                'ReasonPhrase' => 'Tipo de reporte no soportado.',
                'Message' => 'Solo se pueden moderar reportes de publicaciones, ofertas o perfiles.'
            ], 422);
        }

        $reporte->Estado = 'Revisado';
        $reporte->save();

        $this->notifyReporterOfModeration($reporte);
        $this->notifyReportedUserOfModeration($reporte);
        $this->notifyAdminsOfModeration($reporte);

        return response()->json([
            'StatusCode' => 200,
            'ReasonPhrase' => 'Contenido moderado correctamente.',
            'Message' => 'El contenido reportado fue ocultado y el reporte quedo marcado como revisado.'
        ], 200);
    }

    public function destroyEntity(Reporte $reporte)
    {
        if ($reporte->TipoEntidad !== 'Usuario') {
            return response()->json([
                'StatusCode' => 422,
                'ReasonPhrase' => 'Accion no soportada.',
                'Message' => 'Desde esta accion solo se eliminan perfiles reportados.'
            ], 422);
        }

        $usuario = User::with(['empresa', 'desempleado'])
            ->where('IDUsuario', $reporte->IDEntidad)
            ->first();

        if (!$usuario) {
            return $this->moderationNotFound('El perfil reportado no existe.');
        }

        $this->deleteUserStorage($usuario);
        $idUsuario = $usuario->IDUsuario;
        $usuario->delete();

        Reporte::where('TipoEntidad', 'Usuario')
            ->where('IDEntidad', $idUsuario)
            ->delete();

        return response()->json([
            'StatusCode' => 200,
            'ReasonPhrase' => 'Perfil eliminado correctamente.',
            'Message' => 'El perfil reportado fue eliminado por administracion.'
        ], 200);
    }

    private function storeEntityReport(
        Request $request,
        string $tipoEntidad,
        string $idField,
        string $existsRule,
        string $successMessage
    ) {
        $validator = Validator::make($request->all(), [
            $idField => ['required', $existsRule],
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

        $idEntidad = $request->input($idField);
        $ownerId = $this->resolveOwnerId($tipoEntidad, $idEntidad);

        if ($ownerId === auth()->id()) {
            return response()->json([
                'StatusCode' => 403,
                'ReasonPhrase' => 'Accion no permitida.',
                'Message' => 'No puedes reportar tu propio contenido.'
            ], 403);
        }

        $reporte = Reporte::create([
            'TipoEntidad' => $tipoEntidad,
            'IDEntidad' => $idEntidad,
            'IDUsuario' => auth()->id(),
            'Motivo' => $request->input('Motivo'),
            'Descripcion' => $request->input('Descripcion'),
            'FechaReporte' => now(),
        ]);

        event(new ReporteCreado($reporte));

        return response()->json([
            'StatusCode' => 201,
            'ReasonPhrase' => 'Reporte creado correctamente.',
            'Message' => $successMessage,
            'Data' => $reporte
        ], 201);
    }

    private function resolveOwnerId(string $tipoEntidad, int $idEntidad): ?int
    {
        if ($tipoEntidad === 'Publicacion') {
            return Publicacion::where('IDPublicacion', $idEntidad)->value('IDUsuario');
        }

        if ($tipoEntidad === 'Oferta') {
            return OfertaEmpleo::with('empresa')
                ->where('IDOferta', $idEntidad)
                ->first()
                ?->empresa
                ?->IDUsuario;
        }

        return $idEntidad;
    }

    private function notifyReporterOfModeration(Reporte $reporte): void
    {
        if (!$reporte->IDUsuario) {
            return;
        }

        $tipo = $this->nombreTipoEntidad($reporte->TipoEntidad);

        Notificacion::create([
            'IDUsuario' => $reporte->IDUsuario,
            'Titulo' => "Reporte de {$tipo} revisado",
            'Mensaje' => "Tu reporte de {$tipo} fue revisado por administracion y el contenido fue moderado.",
            'Leido' => false,
            'FechaNotificacion' => now(),
            'Ruta' => $this->rutaEntidadReportada($reporte->TipoEntidad, $reporte->IDEntidad),
        ]);
    }

    private function notifyReportedUserOfModeration(Reporte $reporte): void
    {
        if (!$reporte->TipoEntidad || !$reporte->IDEntidad) {
            return;
        }

        $ownerId = $this->resolveOwnerId($reporte->TipoEntidad, $reporte->IDEntidad);

        if (!$ownerId) {
            return;
        }

        $tipo = $this->nombreTipoEntidad($reporte->TipoEntidad);

        Notificacion::create([
            'IDUsuario' => $ownerId,
            'Titulo' => "Contenido moderado",
            'Mensaje' => "Tu {$tipo} fue moderado por administracion tras revisar un reporte.",
            'Leido' => false,
            'FechaNotificacion' => now(),
            'Ruta' => $this->rutaEntidadReportada($reporte->TipoEntidad, $reporte->IDEntidad),
        ]);
    }

    private function notifyAdminsOfModeration(Reporte $reporte): void
    {
        $tipo = $this->nombreTipoEntidad($reporte->TipoEntidad);
        $ruta = $this->rutaEntidadReportada($reporte->TipoEntidad, $reporte->IDEntidad);

        $admins = User::where('rol', 'admin')->get();

        foreach ($admins as $admin) {
            Notificacion::create([
                'IDUsuario' => $admin->IDUsuario,
                'Titulo' => "Reporte de {$tipo} moderado",
                'Mensaje' => "Un reporte de {$tipo} fue moderado por administracion.",
                'Leido' => false,
                'FechaNotificacion' => now(),
                'Ruta' => $ruta,
            ]);
        }
    }

    private function nombreTipoEntidad(?string $tipoEntidad): string
    {
        if ($tipoEntidad === 'Publicacion') {
            return 'publicacion';
        }

        if ($tipoEntidad === 'Oferta') {
            return 'oferta de empleo';
        }

        if ($tipoEntidad === 'Usuario') {
            return 'perfil';
        }

        return 'contenido';
    }

    private function rutaEntidadReportada(?string $tipoEntidad, ?int $idEntidad): string
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

    private function moderationNotFound(string $message)
    {
        return response()->json([
            'StatusCode' => 404,
            'ReasonPhrase' => 'Contenido no encontrado.',
            'Message' => $message
        ], 404);
    }

    private function removePublicacionFiles(Publicacion $publicacion): void
    {
        $paths = [
            $publicacion->Archivo,
            $publicacion->Thumbnail,
            $publicacion->Preview,
        ];

        foreach ($publicacion->documentos as $documento) {
            if ($documento instanceof Documento) {
                $paths[] = $documento->URL;
                $paths[] = $documento->Thumbnail;
                $paths[] = $documento->Preview;
            }
        }

        foreach (array_filter($paths) as $path) {
            $this->deletePublicStoragePath($path);
        }
    }

    private function deletePublicStoragePath(string $path): void
    {
        $relativePath = parse_url($path, PHP_URL_PATH) ?: $path;
        $relativePath = ltrim($relativePath, '/');
        $relativePath = preg_replace('#^storage/#', '', $relativePath);

        if ($relativePath && Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }

    private function moderateUserProfile(User $usuario): void
    {
        $this->deleteUserStorage($usuario);

        if ($usuario->empresa) {
            $usuario->empresa->NombreEmpresa = '[MODERADO] Perfil retirado';
            $usuario->empresa->Ubicacion = 'Perfil moderado por administracion';
            $usuario->empresa->SitioWeb = null;
            $usuario->empresa->Foto = null;
            $usuario->empresa->save();
        }

        if ($usuario->desempleado) {
            $usuario->desempleado->Nombre = '[MODERADO]';
            $usuario->desempleado->Apellido = 'Perfil retirado';
            $usuario->desempleado->Porfolios = null;
            $usuario->desempleado->Disponibilidad = 'Perfil moderado por administracion';
            $usuario->desempleado->Ubicacion = 'Perfil moderado por administracion';
            $usuario->desempleado->Foto = null;
            $usuario->desempleado->save();
        }
    }

    private function deleteUserStorage(User $usuario): void
    {
        $folders = [
            "Empresa/{$usuario->IDUsuario}",
            "Desempleado/{$usuario->IDUsuario}",
        ];

        foreach ($folders as $folder) {
            if (Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->deleteDirectory($folder);
            }
        }
    }
}

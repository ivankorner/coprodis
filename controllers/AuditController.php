<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Database;
use App\Services\AuditService;
use App\Services\ExportService;
use App\Core\Response;

class AuditController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->authorize('super_usuario');
    }

    public function index(Request $request): void
    {
        $page = (int)($request->query('page', 1));
        $filters = [
            'accion' => $request->query('accion'),
            'modulo' => $request->query('modulo'),
            'user_id' => $request->query('user_id'),
            'tipo' => $request->query('tipo'),
            'fecha_desde' => $request->query('fecha_desde'),
            'fecha_hasta' => $request->query('fecha_hasta'),
            'ip' => $request->query('ip'),
        ];

        $result = AuditService::getAll($page, PAGINATION_LIMIT, $filters);
        $stats = AuditService::getStats();
        $timeline = AuditService::getTimeline(7);

        $db = Database::getInstance();
        $modulos = $db->fetchAll("SELECT DISTINCT modulo FROM audits ORDER BY modulo");
        $usuarios = $db->fetchAll("SELECT id, apellido, nombre, email FROM users WHERE deleted_at IS NULL ORDER BY apellido");

        $this->view('audit.index', array_merge($result, [
            'filters' => $filters,
            'modulos' => $modulos,
            'usuarios' => $usuarios,
            'stats' => $stats,
            'timeline' => $timeline,
        ]));
    }

    public function show(Request $request): void
    {
        $id = (int)$request->param('id');
        $audit = AuditService::getById($id);

        if (!$audit) {
            $this->redirectWith(APP_URL . '/auditoria', 'error', 'Registro de auditoría no encontrado.');
        }

        $this->view('audit.show', [
            'audit' => $audit,
        ]);
    }

    public function export(Request $request): void
    {
        $filters = [
            'accion' => $request->query('accion'),
            'modulo' => $request->query('modulo'),
            'user_id' => $request->query('user_id'),
            'tipo' => $request->query('tipo'),
            'fecha_desde' => $request->query('fecha_desde'),
            'fecha_hasta' => $request->query('fecha_hasta'),
        ];

        $result = AuditService::getAll(1, 10000, $filters);

        $headers = [
            'ID', 'Fecha', 'Usuario', 'Email', 'Acción', 'Módulo', 'Tipo',
            'Descripción', 'IP', 'User Agent', 'Entidad', 'Entidad ID', 'Detalles'
        ];

        $data = array_map(fn($item) => [
            $item->id,
            $item->created_at,
            ($item->apellido ?? 'Sistema') . ' ' . ($item->nombre ?? ''),
            $item->email ?? '',
            $item->accion,
            $item->modulo,
            $item->tipo_audit ?? 'info',
            $item->descripcion ?? '',
            $item->ip ?? '',
            $item->user_agent ?? '',
            $item->entidad ?? '',
            $item->entidad_id ?? '',
            $item->detalles ?? '',
        ], $result['data']);

        $filepath = ExportService::toExcel('auditoria', $headers, $data);

        AuditService::register('exportar_auditoria', 'auditoria', 'Exportación de auditoría', null, 'info');

        Response::download($filepath);
    }
}

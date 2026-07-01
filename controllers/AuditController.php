<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Services\AuditService;

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
            'fecha_desde' => $request->query('fecha_desde'),
            'fecha_hasta' => $request->query('fecha_hasta'),
            'ip' => $request->query('ip'),
        ];

        $result = AuditService::getAll($page, PAGINATION_LIMIT, $filters);

        $db = \App\Core\Database::getInstance();
        $modulos = $db->fetchAll("SELECT DISTINCT modulo FROM audits ORDER BY modulo");
        $usuarios = $db->fetchAll("SELECT id, apellido, nombre, email FROM users WHERE deleted_at IS NULL ORDER BY apellido");

        $this->view('audit.index', array_merge($result, [
            'filters' => $filters,
            'modulos' => $modulos,
            'usuarios' => $usuarios,
        ]));
    }

    public function export(Request $request): void
    {
        $filters = [
            'accion' => $request->query('accion'),
            'modulo' => $request->query('modulo'),
            'user_id' => $request->query('user_id'),
            'fecha_desde' => $request->query('fecha_desde'),
            'fecha_hasta' => $request->query('fecha_hasta'),
        ];

        $result = AuditService::getAll(1, 10000, $filters);

        $headers = ['ID', 'Usuario', 'Acción', 'Módulo', 'Descripción', 'IP', 'Fecha'];
        $data = array_map(fn($item) => [
            $item->id,
            ($item->apellido ?? '') . ' ' . ($item->nombre ?? ''),
            $item->accion,
            $item->modulo,
            $item->descripcion,
            $item->ip,
            $item->created_at,
        ], $result['data']);

        $filepath = \App\Services\ExportService::toExcel('auditoria', $headers, $data);

        AuditService::register('exportar_auditoria', 'auditoria', 'Exportación de auditoría');

        \App\Core\Response::download($filepath);
    }
}

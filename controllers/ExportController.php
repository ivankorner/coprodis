<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Database;
use App\Services\AuditService;
use App\Services\ExportService;
use App\Core\Response;

class ExportController extends Controller
{
    public function index(Request $request): void
    {
        $modulo = $request->param('modulo');
        $this->view('exports.index', ['modulo' => $modulo]);
    }

    public function excel(Request $request): void
    {
        $modulo = $request->param('modulo');
        $format = $request->get('format', 'xlsx');

        $result = $this->getData($modulo, $request);

        if ($format === 'csv') {
            $filepath = ExportService::toCsv($modulo, $result['headers'], $result['data']);
        } else {
            $filepath = ExportService::toExcel($modulo, $result['headers'], $result['data']);
        }

        AuditService::register('exportar_excel', 'exportaciones', "Exportación {$modulo} a Excel", null, 'info', ['modulo' => $modulo, 'formato' => 'excel']);

        Response::download($filepath);
    }

    public function csv(Request $request): void
    {
        $modulo = $request->param('modulo');
        $result = $this->getData($modulo, $request);

        $filepath = ExportService::toCsv($modulo, $result['headers'], $result['data']);

        AuditService::register('exportar_csv', 'exportaciones', "Exportación {$modulo} a CSV", null, 'info', ['modulo' => $modulo, 'formato' => 'csv']);

        Response::download($filepath);
    }

    public function pdfList(Request $request): void
    {
        $modulo = $request->param('modulo');
        $result = $this->getData($modulo, $request);

        $data = [
            'title' => 'Listado de ' . ucfirst($modulo),
            'headers' => $result['headers'],
            'data' => $result['data'],
        ];

        $view = 'exports.pdf_list';
        $filepath = ExportService::toPdf($view, $data);

        AuditService::register('exportar_pdf', 'exportaciones', "Exportación {$modulo} a PDF", null, 'info', ['modulo' => $modulo, 'formato' => 'pdf']);

        Response::download($filepath);
    }

    public function pdf(Request $request): void
    {
        $modulo = $request->param('modulo');
        $id = (int)$request->param('id');

        $db = Database::getInstance();
        $view = '';
        $data = [];

        if ($modulo === 'registros') {
            $record = $db->fetch(
                "SELECT r.*, f.titulo as form_titulo
                 FROM records r
                 JOIN forms f ON r.form_id = f.id
                 WHERE r.id = :id",
                ['id' => $id]
            );

            if ($record) {
                $fields = $db->fetchAll(
                    "SELECT ff.*, rd.valor
                     FROM form_fields ff
                     LEFT JOIN record_data rd ON ff.id = rd.field_id AND rd.record_id = :record_id
                     WHERE ff.form_id = :form_id AND ff.deleted_at IS NULL
                     ORDER BY ff.orden",
                    ['record_id' => $id, 'form_id' => $record->form_id]
                );

                $data = [
                    'title' => "Registro #{$id} - {$record->form_titulo}",
                    'record' => $record,
                    'fields' => $fields,
                ];
                $view = 'exports.pdf_record';
            }
        }

        $filepath = ExportService::toPdf($view, $data);

        AuditService::register('exportar_pdf', 'exportaciones', "Exportación {$modulo} a PDF individual #{$id}", null, 'info', ['modulo' => $modulo, 'formato' => 'pdf', 'id' => $id]);

        Response::download($filepath);
    }

    private function getData(string $modulo, Request $request): array
    {
        $db = Database::getInstance();
        $headers = [];
        $data = [];

        switch ($modulo) {
            case 'usuarios':
                $headers = ['ID', 'Apellido', 'Nombre', 'DNI', 'Email', 'Teléfono', 'Localidad', 'Rol', 'Estado', 'Creado'];
                $usuarios = $db->fetchAll(
                    "SELECT u.*, r.nombre as rol_nombre
                     FROM users u JOIN roles r ON u.rol_id = r.id
                     WHERE u.deleted_at IS NULL
                     ORDER BY u.apellido, u.nombre"
                );
                $data = array_map(fn($u) => [
                    $u->id, $u->apellido, $u->nombre, $u->dni, $u->email,
                    $u->telefono, $u->localidad, $u->rol_nombre, $u->estado, $u->created_at,
                ], $usuarios);
                break;

            case 'registros':
                $headers = ['ID', 'Formulario', 'Estado', 'Creado'];
                $records = $db->fetchAll(
                    "SELECT r.*, f.titulo as form_titulo
                     FROM records r
                     JOIN forms f ON r.form_id = f.id
                     WHERE r.deleted_at IS NULL
                     ORDER BY r.created_at DESC"
                );

                if (!empty($records)) {
                    $formIds = array_unique(array_map(fn($r) => $r->form_id, $records));
                    $formIdPlaceholders = implode(',', array_fill(0, count($formIds), '?'));

                    $allFields = $db->fetchAll(
                        "SELECT ff.id, ff.form_id, ff.etiqueta, f.titulo as form_titulo
                         FROM form_fields ff
                         JOIN forms f ON ff.form_id = f.id
                         WHERE ff.form_id IN ($formIdPlaceholders) AND ff.deleted_at IS NULL
                         ORDER BY ff.form_id, ff.orden",
                        $formIds
                    );

                    $fieldLabels = [];
                    $labelCount = [];
                    foreach ($allFields as $ff) {
                        $label = $ff->etiqueta;
                        if (!isset($labelCount[$label])) $labelCount[$label] = 0;
                        $labelCount[$label]++;
                        $fieldLabels[$ff->id] = $labelCount[$label] > 1
                            ? "{$ff->form_titulo}: {$label}"
                            : $label;
                    }

                    $recordIds = array_map(fn($r) => $r->id, $records);
                    $recordPlaceholders = implode(',', array_fill(0, count($recordIds), '?'));

                    $allData = $db->fetchAll(
                        "SELECT record_id, field_id, valor FROM record_data
                         WHERE record_id IN ($recordPlaceholders)",
                        $recordIds
                    );

                    $dataMap = [];
                    foreach ($allData as $d) {
                        $dataMap[$d->record_id][$d->field_id] = $d->valor;
                    }

                    $fieldIdOrder = array_keys($fieldLabels);
                    $headers = array_merge($headers, array_values($fieldLabels));

                    $data = array_map(fn($r) => array_merge(
                        [$r->id, $r->form_titulo, $r->estado, $r->created_at],
                        array_map(fn($fid) => $dataMap[$r->id][$fid] ?? '', $fieldIdOrder)
                    ), $records);
                }
                break;

            case 'auditoria':
                $headers = ['ID', 'Usuario', 'Acción', 'Módulo', 'Descripción', 'IP', 'Fecha'];
                $audits = $db->fetchAll(
                    "SELECT a.*, u.apellido, u.nombre
                     FROM audits a LEFT JOIN users u ON a.user_id = u.id
                     ORDER BY a.created_at DESC LIMIT 1000"
                );
                $data = array_map(fn($a) => [
                    $a->id, ($a->apellido ?? '') . ' ' . ($a->nombre ?? ''),
                    $a->accion, $a->modulo, $a->descripcion, $a->ip, $a->created_at,
                ], $audits);
                break;
        }

        return ['headers' => $headers, 'data' => $data];
    }
}

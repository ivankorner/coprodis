<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Database;
use App\Services\ExportService;
use App\Core\Response;

class ReportController extends Controller
{
    public function index(Request $request): void
    {
        $this->requireAuth();
        $db = Database::getInstance();

        $formId = $request->query('form_id');
        $fechaDesde = $request->query('fecha_desde');
        $fechaHasta = $request->query('fecha_hasta');
        $days = (int)($request->query('days', 30));
        $days = min(max($days, 1), 365);

        $recordWhere = 'WHERE r.deleted_at IS NULL';
        $recordParams = [];
        if ($formId) {
            $recordWhere .= ' AND r.form_id = :fid';
            $recordParams['fid'] = (int)$formId;
        }
        if ($fechaDesde) {
            $recordWhere .= ' AND r.created_at >= :fd';
            $recordParams['fd'] = $fechaDesde . ' 00:00:00';
        }
        if ($fechaHasta) {
            $recordWhere .= ' AND r.created_at <= :fh';
            $recordParams['fh'] = $fechaHasta . ' 23:59:59';
        }

        $form = null;
        if ($formId) {
            $form = $db->fetch("SELECT id, titulo FROM forms WHERE id = :id AND deleted_at IS NULL", ['id' => (int)$formId]);
        }

        $stats = [
            'total_registros' => (int)$db->fetch("SELECT COUNT(*) as t FROM records r {$recordWhere}", $recordParams)->t,
            'hoy' => (int)$db->fetch("SELECT COUNT(*) as t FROM records r {$recordWhere} AND DATE(r.created_at) = CURDATE()", $recordParams)->t,
            'semana' => (int)$db->fetch("SELECT COUNT(*) as t FROM records r {$recordWhere} AND YEARWEEK(r.created_at,1) = YEARWEEK(CURDATE(),1)", $recordParams)->t,
            'mes' => (int)$db->fetch("SELECT COUNT(*) as t FROM records r {$recordWhere} AND MONTH(r.created_at) = MONTH(CURDATE()) AND YEAR(r.created_at) = YEAR(CURDATE())", $recordParams)->t,
            'formularios_activos' => (int)$db->fetch("SELECT COUNT(*) as t FROM forms WHERE estado = 'publicado' AND deleted_at IS NULL")->t,
            'usuarios_activos' => (int)$db->fetch("SELECT COUNT(*) as t FROM users WHERE estado = 'activo' AND deleted_at IS NULL")->t,
            'form_nombre' => $form ? $form->titulo : null,
        ];

        $formsStats = $db->fetchAll(
            "SELECT f.id, f.titulo, COUNT(r.id) as total, MAX(r.created_at) as ultimo
             FROM forms f
             LEFT JOIN records r ON f.id = r.form_id AND r.deleted_at IS NULL
             WHERE f.deleted_at IS NULL
             GROUP BY f.id
             ORDER BY total DESC"
        );

        $recordsByForm = $db->fetchAll(
            "SELECT f.titulo, COUNT(r.id) as total
             FROM forms f
             LEFT JOIN records r ON f.id = r.form_id AND r.deleted_at IS NULL
             WHERE f.deleted_at IS NULL
             GROUP BY f.id ORDER BY total DESC"
        );

        $timelineWhere = $recordWhere;
        $timelineParams = $recordParams;
        if ($fechaDesde) {
            $timelineWhere .= ' AND r.created_at >= :tl_fd';
            $timelineParams['tl_fd'] = $fechaDesde . ' 00:00:00';
        } else {
            $timelineWhere .= ' AND r.created_at >= DATE_SUB(CURDATE(), INTERVAL :tl_days DAY)';
            $timelineParams['tl_days'] = $days;
        }

        $timelineData = $db->fetchAll(
            "SELECT DATE(r.created_at) as fecha, COUNT(*) as total
             FROM records r {$timelineWhere}
             GROUP BY DATE(r.created_at)
             ORDER BY fecha ASC",
            $timelineParams
        );

        $topOperators = $db->fetchAll(
            "SELECT u.id, u.apellido, u.nombre, u.email, COUNT(r.id) as total
             FROM users u
             JOIN records r ON u.id = r.user_id AND r.deleted_at IS NULL
             {$recordWhere}
             GROUP BY u.id
             ORDER BY total DESC LIMIT 5",
            $recordParams
        );

        $favorites = $db->fetchAll(
            "SELECT * FROM report_favorites WHERE user_id = :uid ORDER BY created_at DESC",
            ['uid' => Session::userId()]
        );

        $forms = $db->fetchAll(
            "SELECT id, titulo FROM forms WHERE deleted_at IS NULL ORDER BY titulo"
        );

        $this->view('reports.index', [
            'stats' => $stats,
            'formsStats' => $formsStats,
            'recordsByForm' => $recordsByForm,
            'timelineData' => $timelineData,
            'topOperators' => $topOperators,
            'favorites' => $favorites,
            'forms' => $forms,
            'formId' => $formId,
            'form' => $form,
            'days' => $days,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
        ]);
    }

    public function form(Request $request): void
    {
        $this->requireAuth();
        $formId = (int)$request->param('id');
        $db = Database::getInstance();

        $form = $db->fetch("SELECT * FROM forms WHERE id = :id AND deleted_at IS NULL", ['id' => $formId]);
        if (!$form) {
            $this->redirectWith(APP_URL . '/reportes', 'error', 'Formulario no encontrado.');
        }

        $fields = $db->fetchAll(
            "SELECT * FROM form_fields WHERE form_id = :fid AND deleted_at IS NULL ORDER BY orden",
            ['fid' => $formId]
        );

        $fechaDesde = $request->query('fecha_desde');
        $fechaHasta = $request->query('fecha_hasta');

        $where = 'WHERE r.form_id = :fid AND r.deleted_at IS NULL';
        $params = ['fid' => $formId];
        if ($fechaDesde) { $where .= ' AND r.created_at >= :fd'; $params['fd'] = $fechaDesde . ' 00:00:00'; }
        if ($fechaHasta) { $where .= ' AND r.created_at <= :fh'; $params['fh'] = $fechaHasta . ' 23:59:59'; }

        $totalRecords = (int)$db->fetch(
            "SELECT COUNT(*) as t FROM records r {$where}", $params
        )->t;

        $fieldAnalytics = [];
        foreach ($fields as $field) {
            $analytics = ['field' => $field, 'type' => $field->tipo, 'data' => []];

            switch ($field->tipo) {
                case 'numero':
                case 'moneda':
                case 'porcentaje':
                    $agg = $db->fetch(
                        "SELECT AVG(CAST(rd.valor AS DECIMAL(14,2))) as avg,
                                SUM(CAST(rd.valor AS DECIMAL(14,2))) as sum,
                                MIN(CAST(rd.valor AS DECIMAL(14,2))) as min,
                                MAX(CAST(rd.valor AS DECIMAL(14,2))) as max,
                                COUNT(rd.valor) as filled
                         FROM record_data rd
                         JOIN records r ON rd.record_id = r.id
                         WHERE rd.field_id = :fid AND r.deleted_at IS NULL {$this->dateWhere($fechaDesde, $fechaHasta)}",
                        array_merge(['fid' => $field->id], $this->dateParams($fechaDesde, $fechaHasta))
                    );
                    $analytics['data'] = [
                        'avg' => $agg->avg ? round((float)$agg->avg, 2) : null,
                        'sum' => $agg->sum ? round((float)$agg->sum, 2) : null,
                        'min' => $agg->min !== null ? round((float)$agg->min, 2) : null,
                        'max' => $agg->max !== null ? round((float)$agg->max, 2) : null,
                        'filled' => (int)$agg->filled,
                    ];
                    // Histogram data
                    $buckets = $db->fetchAll(
                        "SELECT rd.valor, COUNT(*) as total
                         FROM record_data rd
                         JOIN records r ON rd.record_id = r.id
                         WHERE rd.field_id = :fid AND r.deleted_at IS NULL {$this->dateWhere($fechaDesde, $fechaHasta)}
                         GROUP BY rd.valor ORDER BY total DESC LIMIT 20",
                        array_merge(['fid' => $field->id], $this->dateParams($fechaDesde, $fechaHasta))
                    );
                    $analytics['data']['distribution'] = $buckets;
                    break;

                case 'select':
                case 'radio':
                    $distribution = $db->fetchAll(
                        "SELECT rd.valor, COUNT(*) as total
                         FROM record_data rd
                         JOIN records r ON rd.record_id = r.id
                         WHERE rd.field_id = :fid AND r.deleted_at IS NULL {$this->dateWhere($fechaDesde, $fechaHasta)}
                         GROUP BY rd.valor ORDER BY total DESC",
                        array_merge(['fid' => $field->id], $this->dateParams($fechaDesde, $fechaHasta))
                    );
                    $analytics['data']['distribution'] = $distribution;
                    $analytics['data']['filled'] = array_sum(array_map(fn($d) => (int)$d->total, $distribution));
                    break;

                case 'checkbox':
                    $raw = $db->fetchAll(
                        "SELECT rd.valor FROM record_data rd
                         JOIN records r ON rd.record_id = r.id
                         WHERE rd.field_id = :fid AND r.deleted_at IS NULL {$this->dateWhere($fechaDesde, $fechaHasta)}
                         AND rd.valor IS NOT NULL AND rd.valor != ''",
                        array_merge(['fid' => $field->id], $this->dateParams($fechaDesde, $fechaHasta))
                    );
                    $freq = [];
                    foreach ($raw as $r) {
                        $vals = json_decode($r->valor, true);
                        if (!is_array($vals)) $vals = [$r->valor];
                        foreach ($vals as $v) {
                            $v = trim($v);
                            if ($v) $freq[$v] = ($freq[$v] ?? 0) + 1;
                        }
                    }
                    arsort($freq);
                    $analytics['data']['frequency'] = $freq;
                    $analytics['data']['total_responses'] = count($raw);
                    break;

                case 'fecha':
                    $months = $db->fetchAll(
                        "SELECT DATE_FORMAT(rd.valor, '%Y-%m') as mes, COUNT(*) as total
                         FROM record_data rd
                         JOIN records r ON rd.record_id = r.id
                         WHERE rd.field_id = :fid AND r.deleted_at IS NULL {$this->dateWhere($fechaDesde, $fechaHasta)}
                         AND rd.valor IS NOT NULL AND rd.valor != ''
                         GROUP BY mes ORDER BY mes",
                        array_merge(['fid' => $field->id], $this->dateParams($fechaDesde, $fechaHasta))
                    );
                    $analytics['data']['months'] = $months;
                    $analytics['data']['filled'] = array_sum(array_map(fn($m) => (int)$m->total, $months));
                    break;

                case 'hora':
                    $hours = $db->fetchAll(
                        "SELECT HOUR(rd.valor) as hora, COUNT(*) as total
                         FROM record_data rd
                         JOIN records r ON rd.record_id = r.id
                         WHERE rd.field_id = :fid AND r.deleted_at IS NULL {$this->dateWhere($fechaDesde, $fechaHasta)}
                         AND rd.valor IS NOT NULL AND rd.valor != ''
                         GROUP BY hora ORDER BY hora",
                        array_merge(['fid' => $field->id], $this->dateParams($fechaDesde, $fechaHasta))
                    );
                    $analytics['data']['hours'] = $hours;
                    $analytics['data']['filled'] = array_sum(array_map(fn($h) => (int)$h->total, $hours));
                    break;

                case 'gps':
                    $coords = $db->fetchAll(
                        "SELECT rd.valor FROM record_data rd
                         JOIN records r ON rd.record_id = r.id
                         WHERE rd.field_id = :fid AND r.deleted_at IS NULL {$this->dateWhere($fechaDesde, $fechaHasta)}
                         AND rd.valor IS NOT NULL AND rd.valor != ''",
                        array_merge(['fid' => $field->id], $this->dateParams($fechaDesde, $fechaHasta))
                    );
                    $points = [];
                    foreach ($coords as $c) {
                        $parts = explode(',', $c->valor);
                        if (count($parts) >= 2 && is_numeric(trim($parts[0])) && is_numeric(trim($parts[1]))) {
                            $points[] = ['lat' => (float)trim($parts[0]), 'lng' => (float)trim($parts[1])];
                        }
                    }
                    $analytics['data']['points'] = $points;
                    $analytics['data']['total'] = count($points);
                    break;

                case 'imagen':
                case 'archivo':
                case 'firma':
                    $filled = (int)$db->fetch(
                        "SELECT COUNT(*) as t FROM record_data rd
                         JOIN records r ON rd.record_id = r.id
                         WHERE rd.field_id = :fid AND r.deleted_at IS NULL {$this->dateWhere($fechaDesde, $fechaHasta)}
                         AND rd.valor IS NOT NULL AND rd.valor != ''",
                        array_merge(['fid' => $field->id], $this->dateParams($fechaDesde, $fechaHasta))
                    )->t;
                    $analytics['data']['filled'] = $filled;
                    break;

                default:
                    $filled = (int)$db->fetch(
                        "SELECT COUNT(*) as t FROM record_data rd
                         JOIN records r ON rd.record_id = r.id
                         WHERE rd.field_id = :fid AND r.deleted_at IS NULL {$this->dateWhere($fechaDesde, $fechaHasta)}
                         AND rd.valor IS NOT NULL AND rd.valor != ''",
                        array_merge(['fid' => $field->id], $this->dateParams($fechaDesde, $fechaHasta))
                    )->t;
                    $total = (int)$db->fetch(
                        "SELECT COUNT(*) as t FROM record_data rd
                         JOIN records r ON rd.record_id = r.id
                         WHERE rd.field_id = :fid AND r.deleted_at IS NULL {$this->dateWhere($fechaDesde, $fechaHasta)}",
                        array_merge(['fid' => $field->id], $this->dateParams($fechaDesde, $fechaHasta))
                    )->t;
                    $analytics['data']['filled'] = $filled;
                    $analytics['data']['total'] = $total;
                    break;
            }

            $fieldAnalytics[] = $analytics;
        }

        $favorites = $db->fetchAll(
            "SELECT * FROM report_favorites WHERE user_id = :uid ORDER BY created_at DESC",
            ['uid' => Session::userId()]
        );

        $groupedAnalytics = $this->groupAnalytics($fieldAnalytics);

        $this->view('reports.form', [
            'form' => $form,
            'fields' => $fields,
            'fieldAnalytics' => $fieldAnalytics,
            'groupedAnalytics' => $groupedAnalytics,
            'totalRecords' => $totalRecords,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'favorites' => $favorites,
        ]);
    }

    public function timeline(Request $request): void
    {
        $this->requireAuth();
        $db = Database::getInstance();

        $formId = $request->query('form_id');
        $days = (int)($request->query('days', 30));
        $days = min(max($days, 1), 365);
        $fechaDesde = $request->query('fecha_desde');
        $fechaHasta = $request->query('fecha_hasta');

        $where = 'WHERE r.deleted_at IS NULL';
        $params = [];
        if ($formId) {
            $where .= ' AND r.form_id = :fid';
            $params['fid'] = (int)$formId;
        }
        if ($fechaDesde) {
            $where .= ' AND r.created_at >= :fd';
            $params['fd'] = $fechaDesde . ' 00:00:00';
        } else {
            $where .= ' AND r.created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)';
            $params['days'] = $days;
        }
        if ($fechaHasta) {
            $where .= ' AND r.created_at <= :fh';
            $params['fh'] = $fechaHasta . ' 23:59:59';
        }

        $timeline = $db->fetchAll(
            "SELECT DATE(r.created_at) as fecha, COUNT(*) as total
             FROM records r {$where}
             GROUP BY DATE(r.created_at) ORDER BY fecha ASC",
            $params
        );

        $records = $db->fetchAll(
            "SELECT r.id, r.created_at, f.titulo as form_titulo, u.apellido, u.nombre
             FROM records r
             JOIN forms f ON r.form_id = f.id
             JOIN users u ON r.user_id = u.id
             {$where}
             ORDER BY r.created_at DESC LIMIT 100",
            $params
        );

        $forms = $db->fetchAll(
            "SELECT id, titulo FROM forms WHERE deleted_at IS NULL ORDER BY titulo"
        );

        $this->view('reports.timeline', [
            'timeline' => $timeline,
            'records' => $records,
            'forms' => $forms,
            'formId' => $formId,
            'days' => $days,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
        ]);
    }

    public function export(Request $request): void
    {
        $this->requireAuth();
        $tipo = $request->param('tipo');
        $db = Database::getInstance();

        if ($tipo === 'pdf-global') {
            $formsStats = $db->fetchAll(
                "SELECT f.titulo, COUNT(r.id) as total
                 FROM forms f LEFT JOIN records r ON f.id = r.form_id AND r.deleted_at IS NULL
                 WHERE f.deleted_at IS NULL GROUP BY f.id ORDER BY total DESC"
            );
            $totalRecords = $db->fetch("SELECT COUNT(*) as t FROM records WHERE deleted_at IS NULL")->t;
            $data = [
                'title' => 'Reporte Global - ' . APP_NAME,
                'stats' => ['total' => $totalRecords],
                'formsStats' => $formsStats,
            ];
            $filepath = ExportService::toPdf('reports.pdf', $data);
            Response::download($filepath);

        } elseif ($tipo === 'pdf-form') {
            $formId = (int)$request->get('form_id');
            $form = $db->fetch("SELECT * FROM forms WHERE id = :id AND deleted_at IS NULL", ['id' => $formId]);
            if (!$form) {
                $this->redirectWith(APP_URL . '/reportes', 'error', 'Formulario no encontrado.');
                return;
            }

            $fields = $db->fetchAll(
                "SELECT * FROM form_fields WHERE form_id = :fid AND deleted_at IS NULL ORDER BY orden",
                ['fid' => $formId]
            );

            $fieldLabels = array_map(fn($f) => $f->etiqueta, $fields);
            $fieldIds = array_map(fn($f) => $f->id, $fields);

            $recordsTableHtml = '';
            $offset = 0;
            $chunkSize = 500;

            while (true) {
                $chunk = $db->fetchAll(
                    "SELECT id, created_at FROM records WHERE form_id = :fid AND deleted_at IS NULL ORDER BY created_at DESC LIMIT " . (int)$chunkSize . " OFFSET " . (int)$offset,
                    ['fid' => $formId]
                );
                if (empty($chunk)) break;

                $chunkIds = array_map(fn($r) => $r->id, $chunk);
                $placeholders = implode(',', array_fill(0, count($chunkIds), '?'));

                $chunkData = $db->fetchAll(
                    "SELECT record_id, field_id, valor FROM record_data WHERE record_id IN ($placeholders)",
                    $chunkIds
                );

                $dataMap = [];
                foreach ($chunkData as $d) {
                    $dataMap[$d->record_id][$d->field_id] = $d->valor;
                }
                unset($chunkData);

                foreach ($chunk as $record) {
                    $recordsTableHtml .= '<tr><td>' . $record->id . '</td>';
                    foreach ($fieldIds as $fid) {
                        $recordsTableHtml .= '<td>' . htmlspecialchars($dataMap[$record->id][$fid] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
                    }
                    $recordsTableHtml .= '</tr>';
                }
                unset($dataMap, $chunk);

                $offset += $chunkSize;
            }

            $data = [
                'title' => "Reporte: {$form->titulo}",
                'form' => $form,
                'fieldLabels' => $fieldLabels,
                'fieldIds' => $fieldIds,
                'recordsTableHtml' => $recordsTableHtml,
            ];
            $filepath = ExportService::toPdf('reports.pdf', $data);
            Response::download($filepath);

        } elseif ($tipo === 'excel-form') {
            $formId = (int)$request->get('form_id');
            $form = $db->fetch("SELECT * FROM forms WHERE id = :id AND deleted_at IS NULL", ['id' => $formId]);
            if (!$form) {
                $this->redirectWith(APP_URL . '/reportes', 'error', 'Formulario no encontrado.');
                return;
            }

            $fields = $db->fetchAll(
                "SELECT * FROM form_fields WHERE form_id = :fid AND deleted_at IS NULL ORDER BY orden",
                ['fid' => $formId]
            );

            $headers = ['ID'];
            $fieldIds = [];
            foreach ($fields as $f) {
                $headers[] = $f->etiqueta;
                $fieldIds[] = $f->id;
            }

            $excelData = [];
            $offset = 0;
            $chunkSize = 500;

            while (true) {
                $chunk = $db->fetchAll(
                    "SELECT id FROM records WHERE form_id = :fid AND deleted_at IS NULL ORDER BY created_at DESC LIMIT " . (int)$chunkSize . " OFFSET " . (int)$offset,
                    ['fid' => $formId]
                );
                if (empty($chunk)) break;

                $chunkIds = array_map(fn($r) => $r->id, $chunk);
                $placeholders = implode(',', array_fill(0, count($chunkIds), '?'));

                $chunkData = $db->fetchAll(
                    "SELECT record_id, field_id, valor FROM record_data WHERE record_id IN ($placeholders)",
                    $chunkIds
                );

                $dataMap = [];
                foreach ($chunkData as $d) {
                    $dataMap[$d->record_id][$d->field_id] = $d->valor;
                }

                foreach ($chunk as $r) {
                    $row = [$r->id];
                    foreach ($fieldIds as $fid) {
                        $row[] = $dataMap[$r->id][$fid] ?? '';
                    }
                    $excelData[] = $row;
                }

                $offset += $chunkSize;
            }

            $filepath = ExportService::toExcel("reporte_{$form->titulo}", $headers, $excelData);
            Response::download($filepath);
        }

        \App\Services\AuditService::register('exportar_reporte', 'reportes', "Exportación de reporte: {$tipo}");
    }

    public function saveFavorite(Request $request): void
    {
        $this->requireAuth();
        $db = Database::getInstance();
        $userId = Session::userId();

        $db->insert('report_favorites', [
            'user_id' => $userId,
            'titulo' => $request->get('titulo'),
            'tipo' => $request->get('tipo'),
            'config' => json_encode($request->get('config', []), JSON_UNESCAPED_UNICODE),
        ]);

        $this->redirectWith(APP_URL . '/reportes', 'success', 'Reporte guardado como favorito.');
    }

    public function deleteFavorite(Request $request): void
    {
        $this->requireAuth();
        $id = (int)$request->param('id');
        $userId = Session::userId();

        $db = Database::getInstance();
        $db->delete('report_favorites', 'id = :id AND user_id = :uid', ['id' => $id, 'uid' => $userId]);

        $this->redirectWith(APP_URL . '/reportes', 'success', 'Favorito eliminado.');
    }

    private function dateWhere(?string $from, ?string $to): string
    {
        $w = '';
        if ($from) $w .= ' AND r.created_at >= :rfd';
        if ($to) $w .= ' AND r.created_at <= :rfh';
        return $w;
    }

    private function dateParams(?string $from, ?string $to): array
    {
        $p = [];
        if ($from) $p['rfd'] = $from . ' 00:00:00';
        if ($to) $p['rfh'] = $to . ' 23:59:59';
        return $p;
    }

    private function groupAnalytics(array $fieldAnalytics): array
    {
        $groups = [
            'numericos'   => ['label' => 'Campos Numéricos', 'icon' => 'fa-calculator', 'types' => ['numero', 'moneda', 'porcentaje'], 'color' => 'blue'],
            'selecciones' => ['label' => 'Selecciones',       'icon' => 'fa-list',       'types' => ['select', 'radio'],          'color' => 'purple'],
            'checkboxes'  => ['label' => 'Checkboxes',        'icon' => 'fa-check-square','types' => ['checkbox'],                  'color' => 'green'],
            'fechas'      => ['label' => 'Fechas',            'icon' => 'fa-calendar',    'types' => ['fecha'],                     'color' => 'indigo'],
            'horas'       => ['label' => 'Horas',             'icon' => 'fa-clock',       'types' => ['hora'],                      'color' => 'amber'],
            'gps'         => ['label' => 'Ubicaciones',       'icon' => 'fa-map-marker-alt','types' => ['gps'],                      'color' => 'red'],
        ];

        $result = [];
        foreach ($groups as $key => $group) {
            $fields = array_filter($fieldAnalytics, fn($fa) => in_array($fa['type'], $group['types']));
            if (!empty($fields)) {
                $result[$key] = $group;
                $result[$key]['fields'] = array_values($fields);
            }
        }
        return $result;
    }
}

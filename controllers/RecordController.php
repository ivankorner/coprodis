<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;
use App\Core\Session;
use App\Core\Response;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Services\FileUploadService;
use App\Helpers\Validator;

class RecordController extends Controller
{
    public function index(Request $request): void
    {
        $this->requireAuth();
        $this->requirePasswordChange();

        $db = Database::getInstance();
        $page = (int)($request->query('page', 1));
        $search = $request->query('search');
        $formId = $request->query('form_id');
        $estado = $request->query('estado');
        $fechaDesde = $request->query('fecha_desde');
        $fechaHasta = $request->query('fecha_hasta');
        $perPage = (int)($request->query('per_page', PAGINATION_LIMIT));
        $allowedPerPage = [10, 25, 50, 100, 250, 500];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = PAGINATION_LIMIT;
        }

        $userRole = Session::userRole();
        $userId = Session::userId();

        $where = [];
        $params = [];

        if ($userRole === 'usuario') {
            $where[] = 'r.user_id = :user_id';
            $params['user_id'] = $userId;
        }

        if ($search) {
            $where[] = '(r.id = :search_id OR f.titulo LIKE :search)';
            $params['search_id'] = is_numeric($search) ? (int)$search : 0;
            $params['search'] = "%{$search}%";
        }
        if ($formId) {
            $where[] = 'r.form_id = :form_id';
            $params['form_id'] = (int)$formId;
        }
        if ($estado) {
            $where[] = 'r.estado = :estado';
            $params['estado'] = $estado;
        }
        if ($fechaDesde) {
            $where[] = 'r.created_at >= :fecha_desde';
            $params['fecha_desde'] = $fechaDesde . ' 00:00:00';
        }
        if ($fechaHasta) {
            $where[] = 'r.created_at <= :fecha_hasta';
            $params['fecha_hasta'] = $fechaHasta . ' 23:59:59';
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) . ' AND r.deleted_at IS NULL' : 'WHERE r.deleted_at IS NULL';
        $offset = ($page - 1) * $perPage;

        $records = $db->fetchAll(
            "SELECT r.*, f.titulo as form_titulo,
                    COALESCE(
                        (SELECT rd.valor FROM record_data rd
                         JOIN form_fields ff ON rd.field_id = ff.id
                         WHERE rd.record_id = r.id AND ff.etiqueta = 'Apellido' LIMIT 1),
                        u.apellido
                    ) as persona_apellido,
                    COALESCE(
                        (SELECT rd.valor FROM record_data rd
                         JOIN form_fields ff ON rd.field_id = ff.id
                         WHERE rd.record_id = r.id AND ff.etiqueta = 'Nombre' LIMIT 1),
                        u.nombre
                    ) as persona_nombre
             FROM records r
             JOIN forms f ON r.form_id = f.id
             JOIN users u ON r.user_id = u.id
             {$whereClause}
             ORDER BY r.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $countSql = "SELECT COUNT(*) as total FROM records r JOIN forms f ON r.form_id = f.id {$whereClause}";
        $total = (int)$db->fetch($countSql, $params)->total;

        // Get forms for filter
        $forms = $db->fetchAll(
            "SELECT id, titulo FROM forms WHERE deleted_at IS NULL AND estado = 'publicado' ORDER BY titulo"
        );

        $this->view('records.index', [
            'records' => $records,
            'forms' => $forms,
            'total' => $total,
            'page' => $page,
            'totalPages' => (int)ceil($total / $perPage),
            'perPage' => $perPage,
            'search' => $search,
            'filtroForm' => $formId,
            'filtroEstado' => $estado,
            'filtroFechaDesde' => $fechaDesde,
            'filtroFechaHasta' => $fechaHasta,
        ]);
    }

    public function buscarAjax(Request $request): void
    {
        $this->requireAuth();
        $this->requirePasswordChange();

        $db = Database::getInstance();
        $q = $request->query('q');
        $formId = $request->query('form_id');
        $fieldIds = $request->query('field_ids') ? (array)$request->query('field_ids') : [];
        $estado = $request->query('estado');
        $fechaDesde = $request->query('fecha_desde');
        $fechaHasta = $request->query('fecha_hasta');
        $page = max(1, (int)($request->query('page', 1)));
        $perPage = (int)($request->query('per_page', PAGINATION_LIMIT));
        $allowedPerPage = [10, 25, 50, 100, 250, 500];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = PAGINATION_LIMIT;
        }

        $userRole = Session::userRole();
        $userId = Session::userId();

        $where = [];
        $params = [];

        if ($userRole === 'usuario') {
            $where[] = 'r.user_id = :user_id';
            $params['user_id'] = $userId;
        }

        if ($formId && $formId !== 'todos') {
            $where[] = 'r.form_id = :form_id';
            $params['form_id'] = (int)$formId;
        } elseif (!$formId) {
            Response::json([
                'records' => [],
                'total' => 0,
                'page' => 1,
                'totalPages' => 0,
                'perPage' => $perPage,
                'q' => $q,
            ]);
        }

        if ($q) {
            $searchTerm = "%{$q}%";
            $searchParts = ['r.id = :search_id'];
            $params['search_id'] = is_numeric($q) ? (int)$q : 0;

            if (!empty($fieldIds)) {
                $fieldPlaceholders = [];
                foreach ($fieldIds as $i => $fid) {
                    $key = 'fid_' . $i;
                    $fieldPlaceholders[] = ':' . $key;
                    $params[$key] = (int)$fid;
                }
                $fieldInSql = implode(',', $fieldPlaceholders);
                $searchParts[] = "EXISTS (
                    SELECT 1 FROM record_data rd2
                    WHERE rd2.record_id = r.id
                    AND rd2.valor LIKE :q_valor
                    AND rd2.field_id IN ({$fieldInSql})
                )";
                $params['q_valor'] = $searchTerm;
            } elseif ($formId === 'todos') {
                $searchParts[] = "EXISTS (
                    SELECT 1 FROM record_data rd2
                    WHERE rd2.record_id = r.id
                    AND rd2.valor LIKE :q_valor
                )";
                $params['q_valor'] = $searchTerm;
            }

            $where[] = '(' . implode(' OR ', $searchParts) . ')';
        }

        if ($estado) {
            $where[] = 'r.estado = :estado';
            $params['estado'] = $estado;
        }
        if ($fechaDesde) {
            $where[] = 'r.created_at >= :fecha_desde';
            $params['fecha_desde'] = $fechaDesde . ' 00:00:00';
        }
        if ($fechaHasta) {
            $where[] = 'r.created_at <= :fecha_hasta';
            $params['fecha_hasta'] = $fechaHasta . ' 23:59:59';
        }

        $whereClause = ($where ? 'WHERE ' . implode(' AND ', $where) . ' AND ' : 'WHERE ') . 'r.deleted_at IS NULL';
        $offset = ($page - 1) * $perPage;

        $records = $db->fetchAll(
            "SELECT r.*, f.titulo as form_titulo,
                    COALESCE(
                        (SELECT rd.valor FROM record_data rd
                         JOIN form_fields ff ON rd.field_id = ff.id
                         WHERE rd.record_id = r.id AND ff.etiqueta = 'Apellido' LIMIT 1),
                        u.apellido
                    ) as persona_apellido,
                    COALESCE(
                        (SELECT rd.valor FROM record_data rd
                         JOIN form_fields ff ON rd.field_id = ff.id
                         WHERE rd.record_id = r.id AND ff.etiqueta = 'Nombre' LIMIT 1),
                        u.nombre
                    ) as persona_nombre
             FROM records r
             JOIN forms f ON r.form_id = f.id
             JOIN users u ON r.user_id = u.id
             {$whereClause}
             ORDER BY r.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $matchedFields = [];
        if ($q && !empty($records) && !empty($fieldIds)) {
            $recordIds = array_map(fn($r) => $r->id, $records);
            $recordPlaceholders = implode(',', array_fill(0, count($recordIds), '?'));
            $fieldPlaceholders = implode(',', array_fill(0, count($fieldIds), '?'));
            $matchedFieldResults = $db->fetchAll(
                "SELECT rd.record_id, ff.etiqueta
                 FROM record_data rd
                 JOIN form_fields ff ON rd.field_id = ff.id
                 WHERE rd.record_id IN ({$recordPlaceholders})
                 AND rd.valor LIKE ?
                 AND rd.field_id IN ({$fieldPlaceholders})
                 AND ff.deleted_at IS NULL
                 GROUP BY rd.record_id, ff.etiqueta",
                array_merge($recordIds, ["%{$q}%"], $fieldIds)
            );
            foreach ($matchedFieldResults as $mf) {
                $matchedFields[$mf->record_id][] = $mf->etiqueta;
            }
        }

        $result = array_map(function($r) use ($matchedFields) {
            return [
                'id' => $r->id,
                'form_titulo' => $r->form_titulo,
                'persona_apellido' => $r->persona_apellido,
                'persona_nombre' => $r->persona_nombre,
                'estado' => $r->estado,
                'created_at' => date('d/m/Y H:i', strtotime($r->created_at)),
                'matched_fields' => $matchedFields[$r->id] ?? [],
            ];
        }, $records);

        $countSql = "SELECT COUNT(*) as total FROM records r JOIN forms f ON r.form_id = f.id {$whereClause}";
        $total = (int)$db->fetch($countSql, $params)->total;

        Response::json([
            'records' => $result,
            'total' => $total,
            'page' => $page,
            'totalPages' => (int)ceil($total / $perPage),
            'perPage' => $perPage,
            'q' => $q,
        ]);
    }

    public function camposBusqueda(Request $request): void
    {
        $this->requireAuth();
        $db = Database::getInstance();
        $formId = (int)$request->param('id');

        $form = $db->fetch(
            "SELECT id, titulo FROM forms WHERE id = :id AND deleted_at IS NULL AND estado = 'publicado'",
            ['id' => $formId]
        );

        if (!$form) {
            Response::error('Formulario no encontrado', 404);
        }

        $fields = $db->fetchAll(
            "SELECT id, etiqueta, tipo FROM form_fields
             WHERE form_id = :form_id AND deleted_at IS NULL
             AND tipo NOT IN ('imagen', 'archivo', 'firma', 'gps', 'separador')
             ORDER BY orden",
            ['form_id' => $formId]
        );

        $commonKeywords = ['dni', 'nombre', 'apellido', 'email', 'correo', 'teléfono', 'telefono', 'dirección', 'direccion', 'celular', 'whatsapp', 'domicilio'];

        $result = array_map(function($f) use ($commonKeywords) {
            $lower = mb_strtolower($f->etiqueta, 'UTF-8');
            $esComun = false;
            foreach ($commonKeywords as $kw) {
                if (str_contains($lower, $kw)) {
                    $esComun = true;
                    break;
                }
            }
            return [
                'id' => (int)$f->id,
                'etiqueta' => $f->etiqueta,
                'tipo' => $f->tipo,
                'es_comun' => $esComun,
            ];
        }, $fields);

        Response::json([
            'form' => ['id' => (int)$form->id, 'titulo' => $form->titulo],
            'fields' => $result,
        ]);
    }

    public function create(Request $request): void
    {
        $this->requireAuth();
        $this->requirePasswordChange();

        $formId = (int)$request->param('form_id');
        $db = Database::getInstance();

        $form = $db->fetch(
            "SELECT *, seccion_inicial_titulo FROM forms WHERE id = :id AND deleted_at IS NULL AND estado = 'publicado'",
            ['id' => $formId]
        );

        if (!$form) {
            $this->redirectWith(APP_URL . '/registros', 'error', 'Formulario no disponible.');
        }

        $fields = $db->fetchAll(
            "SELECT * FROM form_fields WHERE form_id = :form_id AND deleted_at IS NULL ORDER BY orden",
            ['form_id' => $formId]
        );

        $this->view('records.create', ['form' => $form, 'fields' => $fields]);
    }

    public function store(Request $request): void
    {
        $this->requireAuth();
        $formId = (int)$request->param('form_id');
        $db = Database::getInstance();
        $userId = Session::userId();

        $form = $db->fetch(
            "SELECT *, seccion_inicial_titulo FROM forms WHERE id = :id AND deleted_at IS NULL AND estado = 'publicado'",
            ['id' => $formId]
        );

        if (!$form) {
            $this->redirectWith(APP_URL . '/registros', 'error', 'Formulario no disponible.');
        }

        $fields = $db->fetchAll(
            "SELECT * FROM form_fields WHERE form_id = :form_id AND deleted_at IS NULL ORDER BY orden",
            ['form_id' => $formId]
        );

        // Validar archivos antes de guardar
        $maxFileSize = 10485760; // 10MB por defecto
        $fileUpload = new FileUploadService();
        foreach ($fields as $field) {
            if (in_array($field->tipo, ['imagen', 'archivo']) && $request->hasFile('field_' . $field->id)) {
                $file = $_FILES['field_' . $field->id];
                if ($file['error'] === UPLOAD_ERR_OK && $file['size'] > $maxFileSize) {
                    $maxMB = $maxFileSize / 1048576;
                    $this->redirectWith(APP_URL . "/registros/crear/{$formId}", 'error',
                        "El archivo '{$file['name']}' excede el tamaño máximo de {$maxMB}MB.");
                }
            }
        }

        $recordId = $db->insert('records', [
            'form_id' => $formId,
            'user_id' => $userId,
        ]);

        $datosParaCorreo = [];

        foreach ($fields as $field) {
            if ($field->condicion_campo_padre && $field->condicion_valor) {
                $parentValue = $request->get('field_' . $field->condicion_campo_padre);
                if ($parentValue !== $field->condicion_valor) {
                    continue;
                }
            }

            if ($field->tipo === 'firma') {
                $signatureData = $request->get('field_' . $field->id);
                if ($signatureData) {
                    $result = $fileUpload->uploadSignature($signatureData);
                    if ($result['success']) {
                        $db->insert('record_data', [
                            'record_id' => $recordId,
                            'field_id' => $field->id,
                            'valor' => $result['path'],
                        ]);
                    }
                }
            } elseif (in_array($field->tipo, ['imagen', 'archivo'])) {
                if ($request->hasFile('field_' . $field->id)) {
                    $result = $fileUpload->upload(
                        $_FILES['field_' . $field->id],
                        $field->tipo === 'imagen' ? 'images' : 'files'
                    );
                    if ($result['success']) {
                        $db->insert('record_data', [
                            'record_id' => $recordId,
                            'field_id' => $field->id,
                            'valor' => $result['path'],
                        ]);
                        $datosParaCorreo[$field->etiqueta] = 'Archivo: ' . $result['original_name'];
                    }
                }
            } else {
                $value = $request->get('field_' . $field->id);
                if ($value !== null) {
                    $db->insert('record_data', [
                        'record_id' => $recordId,
                        'field_id' => $field->id,
                        'valor' => $value,
                    ]);
                    $datosParaCorreo[$field->etiqueta] = $value;
                }
            }
        }

        $detallesRegistro = array_merge(['formulario' => $form->titulo], $datosParaCorreo);
        try {
            AuditService::register('crear_registro', 'registros', "Registro #{$recordId} creado en: {$form->titulo}", null, 'success', $detallesRegistro, 'record', $recordId);
        } catch (\Throwable $e) {
            error_log("AuditService error: " . $e->getMessage());
        }

        $isAjax = $request->isAjax();
        if ($isAjax) {
            ob_end_clean();
            header_remove();
            http_response_code(200);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'redirect' => APP_URL . '/registros'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $this->redirectWith(APP_URL . '/registros', 'success', 'Registro creado exitosamente.');
    }

    public function show(Request $request): void
    {
        $this->requireAuth();
        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $record = $db->fetch(
            "SELECT r.*, f.titulo as form_titulo, f.seccion_inicial_titulo, u.apellido, u.nombre, u.email as user_email
             FROM records r
             JOIN forms f ON r.form_id = f.id
             JOIN users u ON r.user_id = u.id
             WHERE r.id = :id AND r.deleted_at IS NULL",
            ['id' => $id]
        );

        if (!$record) {
            $this->redirectWith(APP_URL . '/registros', 'error', 'Registro no encontrado.');
        }

        // Check permission
        if (Session::userRole() === 'usuario' && $record->user_id !== Session::userId()) {
            $this->redirectWith(APP_URL . '/registros', 'error', 'No tienes permiso para ver este registro.');
        }

        $fields = $db->fetchAll(
            "SELECT ff.*, rd.valor
             FROM form_fields ff
             LEFT JOIN record_data rd ON ff.id = rd.field_id AND rd.record_id = :record_id
             WHERE ff.form_id = :form_id AND ff.deleted_at IS NULL
             ORDER BY ff.orden",
            ['record_id' => $id, 'form_id' => $record->form_id]
        );

        $changes = $db->fetchAll(
            "SELECT rc.*, ff.etiqueta as field_label, u.apellido, u.nombre
             FROM record_changes rc
             LEFT JOIN form_fields ff ON rc.field_id = ff.id
             LEFT JOIN users u ON rc.user_id = u.id
             WHERE rc.record_id = :record_id
             ORDER BY rc.created_at DESC",
            ['record_id' => $id]
        );

        $this->view('records.show', [
            'record' => $record,
            'fields' => $fields,
            'changes' => $changes,
        ]);
    }

    public function edit(Request $request): void
    {
        $this->requireAuth();
        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $record = $db->fetch(
            "SELECT r.*, f.titulo as form_titulo, f.seccion_inicial_titulo
             FROM records r
             JOIN forms f ON r.form_id = f.id
             WHERE r.id = :id AND r.deleted_at IS NULL",
            ['id' => $id]
        );

        if (!$record) {
            $this->redirectWith(APP_URL . '/registros', 'error', 'Registro no encontrado.');
        }

        if (Session::userRole() === 'usuario' && $record->user_id !== Session::userId()) {
            $this->redirectWith(APP_URL . '/registros', 'error', 'No tienes permiso para editar este registro.');
        }

        $fields = $db->fetchAll(
            "SELECT ff.*, rd.valor
             FROM form_fields ff
             LEFT JOIN record_data rd ON ff.id = rd.field_id AND rd.record_id = :record_id
             WHERE ff.form_id = :form_id AND ff.deleted_at IS NULL
             ORDER BY ff.orden",
            ['record_id' => $id, 'form_id' => $record->form_id]
        );

        $this->view('records.edit', ['record' => $record, 'fields' => $fields]);
    }

    public function update(Request $request): void
    {
        $this->requireAuth();
        $id = (int)$request->param('id');
        $db = Database::getInstance();
        $userId = Session::userId();

        $record = $db->fetch(
            "SELECT * FROM records WHERE id = :id AND deleted_at IS NULL",
            ['id' => $id]
        );

        if (!$record) {
            $this->redirectWith(APP_URL . '/registros', 'error', 'Registro no encontrado.');
        }

        if (Session::userRole() === 'usuario' && $record->user_id !== $userId) {
            $this->redirectWith(APP_URL . '/registros', 'error', 'No tienes permiso para editar este registro.');
        }

        $fields = $db->fetchAll(
            "SELECT ff.*, rd.valor as valor_actual
             FROM form_fields ff
             LEFT JOIN record_data rd ON ff.id = rd.field_id AND rd.record_id = :record_id
             WHERE ff.form_id = :form_id AND ff.deleted_at IS NULL",
            ['record_id' => $id, 'form_id' => $record->form_id]
        );

        foreach ($fields as $field) {
            if ($field->condicion_campo_padre && $field->condicion_valor) {
                $parentValue = $request->get('field_' . $field->condicion_campo_padre);
                if ($parentValue !== $field->condicion_valor) {
                    continue;
                }
            }

            $newValue = $request->get('field_' . $field->id);

            if ($field->tipo === 'firma') {
                $signatureData = $request->get('field_' . $field->id);
                if ($signatureData && strpos($signatureData, 'data:image') === 0) {
                    $fileUpload = new FileUploadService();
                    $result = $fileUpload->uploadSignature($signatureData);
                    if ($result['success']) {
                        $newValue = $result['path'];
                    }
                } else {
                    continue; // Keep existing signature
                }
            }

            if ($newValue !== $field->valor_actual) {
                if ($field->valor_actual) {
                    $db->update('record_data', ['valor' => $newValue],
                        'record_id = :record_id AND field_id = :field_id',
                        ['record_id' => $id, 'field_id' => $field->id]
                    );
                } else {
                    $db->insert('record_data', [
                        'record_id' => $id,
                        'field_id' => $field->id,
                        'valor' => $newValue,
                    ]);
                }

                // Record change
                $db->insert('record_changes', [
                    'record_id' => $id,
                    'field_id' => $field->id,
                    'user_id' => $userId,
                    'valor_anterior' => $field->valor_actual,
                    'valor_nuevo' => $newValue,
                ]);
            }
        }

        AuditService::register('editar_registro', 'registros', "Registro #{$id} editado", null, 'warning', [], 'record', $id);

        $isAjax = $request->isAjax();
        if ($isAjax) {
            ob_end_clean();
            header_remove();
            http_response_code(200);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'redirect' => APP_URL . "/registros/{$id}"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $this->redirectWith(APP_URL . "/registros/{$id}", 'success', 'Registro actualizado exitosamente.');
    }

    public function archive(Request $request): void
    {
        $this->requireAuth();
        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $record = $db->fetch("SELECT * FROM records WHERE id = :id AND deleted_at IS NULL", ['id' => $id]);
        if (!$record) {
            $this->redirectWith(APP_URL . '/registros', 'error', 'Registro no encontrado.');
        }

        $db->update('records', ['estado' => 'archivado', 'updated_at' => date('Y-m-d H:i:s')],
            'id = :id', ['id' => $id]);

        AuditService::register('archivar_registro', 'registros', "Registro #{$id} archivado", null, 'warning', [], 'record', $id);

        $this->redirectWith(APP_URL . '/registros', 'success', 'Registro archivado exitosamente.');
    }

    public function restore(Request $request): void
    {
        $this->requireAuth();
        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $record = $db->fetch("SELECT * FROM records WHERE id = :id", ['id' => $id]);
        if (!$record) {
            $this->redirectWith(APP_URL . '/registros', 'error', 'Registro no encontrado.');
        }

        $db->update('records', ['estado' => 'activo', 'updated_at' => date('Y-m-d H:i:s'), 'deleted_at' => null],
            'id = :id', ['id' => $id]);

        AuditService::register('restaurar_registro', 'registros', "Registro #{$id} restaurado", null, 'success', [], 'record', $id);

        $this->redirectWith(APP_URL . '/registros', 'success', 'Registro restaurado exitosamente.');
    }

    public function destroy(Request $request): void
    {
        $this->requireAuth();
        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $record = $db->fetch("SELECT * FROM records WHERE id = :id AND deleted_at IS NULL", ['id' => $id]);
        if (!$record) {
            $this->redirectWith(APP_URL . '/registros', 'error', 'Registro no encontrado.');
        }

        if ($record->estado !== 'archivado') {
            $this->redirectWith(APP_URL . '/registros', 'error', 'Solo se pueden eliminar registros archivados.');
        }

        $db->update('records', ['deleted_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);

        AuditService::register('eliminar_registro', 'registros', "Registro #{$id} eliminado", null, 'danger', [], 'record', $id);

        $this->redirectWith(APP_URL . '/registros', 'success', 'Registro eliminado exitosamente.');
    }

    public function history(Request $request): void
    {
        $this->requireAuth();
        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $record = $db->fetch(
            "SELECT r.*, f.titulo as form_titulo
             FROM records r JOIN forms f ON r.form_id = f.id
             WHERE r.id = :id AND r.deleted_at IS NULL",
            ['id' => $id]
        );

        if (!$record) {
            $this->redirectWith(APP_URL . '/registros', 'error', 'Registro no encontrado.');
        }

        $changes = $db->fetchAll(
            "SELECT rc.*, ff.etiqueta as field_label, u.apellido, u.nombre
             FROM record_changes rc
             LEFT JOIN form_fields ff ON rc.field_id = ff.id
             LEFT JOIN users u ON rc.user_id = u.id
             WHERE rc.record_id = :record_id
             ORDER BY rc.created_at DESC",
            ['record_id' => $id]
        );

        $this->view('records.history', ['record' => $record, 'changes' => $changes]);
    }
}

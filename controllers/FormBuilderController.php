<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;
use App\Core\Session;
use App\Services\AuditService;
use App\Helpers\Validator;

class FormBuilderController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->authorize('super_usuario');
    }

    public function index(Request $request): void
    {
        $db = Database::getInstance();
        $page = (int)($request->query('page', 1));
        $search = $request->query('search');
        $estado = $request->query('estado');

        $where = [];
        $params = [];

        if ($search) {
            $where[] = '(f.titulo LIKE :search OR f.descripcion LIKE :search2)';
            $params['search'] = "%{$search}%";
            $params['search2'] = "%{$search}%";
        }
        if ($estado) {
            $where[] = 'f.estado = :estado';
            $params['estado'] = $estado;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) . ' AND f.deleted_at IS NULL' : 'WHERE f.deleted_at IS NULL';
        $limit = PAGINATION_LIMIT;
        $offset = ($page - 1) * $limit;

        $forms = $db->fetchAll(
            "SELECT f.*, u.apellido, u.nombre,
             (SELECT COUNT(*) FROM form_fields ff WHERE ff.form_id = f.id AND ff.deleted_at IS NULL) as total_campos,
             (SELECT COUNT(*) FROM records r WHERE r.form_id = f.id AND r.deleted_at IS NULL) as total_registros
             FROM forms f
             JOIN users u ON f.created_by = u.id
             {$whereClause}
             ORDER BY f.created_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        $countSql = "SELECT COUNT(*) as total FROM forms f {$whereClause}";
        $total = (int)$db->fetch($countSql, $params)->total;

        $this->view('forms.index', [
            'forms' => $forms,
            'total' => $total,
            'page' => $page,
            'totalPages' => (int)ceil($total / $limit),
            'search' => $search,
            'filtroEstado' => $estado,
        ]);
    }

    public function create(Request $request): void
    {
        $this->view('forms.create');
    }

    public function store(Request $request): void
    {
        $data = $request->only(['titulo', 'descripcion']);

        $validator = Validator::validate($data, [
            'titulo' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            $this->redirectWith(APP_URL . '/formularios/crear', 'error', $validator->firstError());
        }

        $db = Database::getInstance();
        $formId = $db->insert('forms', [
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'] ?? null,
            'estado' => 'borrador',
            'created_by' => Session::userId(),
        ]);

        AuditService::register('crear_formulario', 'formularios', "Formulario creado: {$data['titulo']}", null, 'success', ['titulo' => $data['titulo']], 'form', $formId);

        $this->redirectWith(APP_URL . "/formularios/{$formId}/editar", 'success', 'Formulario creado. Ahora puedes agregar campos.');
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $form = $db->fetch("SELECT * FROM forms WHERE id = :id AND deleted_at IS NULL", ['id' => $id]);
        if (!$form) {
            $this->redirectWith(APP_URL . '/formularios', 'error', 'Formulario no encontrado.');
        }

        $fields = $db->fetchAll(
            "SELECT * FROM form_fields WHERE form_id = :form_id AND deleted_at IS NULL ORDER BY orden ASC",
            ['form_id' => $id]
        );

        $this->view('forms.edit', ['form' => $form, 'fields' => $fields]);
    }

    public function update(Request $request): void
    {
        $id = (int)$request->param('id');
        $data = $request->only(['titulo', 'descripcion']);

        $db = Database::getInstance();
        $db->update('forms', [
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'] ?? null,
        ], 'id = :id', ['id' => $id]);

        AuditService::register('editar_formulario', 'formularios', "Formulario editado: {$data['titulo']}", null, 'warning', [], 'form', $id);

        $this->redirectWith(APP_URL . '/formularios', 'success', 'Formulario actualizado exitosamente.');
    }

    public function saveFields(Request $request): void
    {
        $formId = (int)$request->get('form_id');
        $fields = $request->get('fields', []);
        $titulo = $request->get('titulo');
        $descripcion = $request->get('descripcion');
        $seccionInicialTitulo = $request->get('seccion_inicial_titulo');

        $db = Database::getInstance();

        $form = $db->fetch("SELECT * FROM forms WHERE id = :id AND deleted_at IS NULL", ['id' => $formId]);
        if (!$form) {
            $this->json(['success' => false, 'message' => 'Formulario no encontrado.']);
            return;
        }

        $db->beginTransaction();
        try {
            $db->update('forms', [
                'titulo' => $titulo,
                'descripcion' => $descripcion ?? null,
                'seccion_inicial_titulo' => $seccionInicialTitulo ?? 'General',
            ], 'id = :id', ['id' => $formId]);

            AuditService::register('editar_formulario', 'formularios', "Formulario editado: {$titulo}", null, 'warning', [], 'form', $formId);

            $db->update('form_fields', ['deleted_at' => date('Y-m-d H:i:s')], 'form_id = :form_id', ['form_id' => $formId]);

            $orden = 0;
            $nombreToId = [];
            $seccionCounter = 0;

            foreach ($fields as $field) {
                $esSeparador = ($field['tipo'] ?? '') === 'separador';

                if ($esSeparador) {
                    $seccionCounter++;
                    $nombre = 'seccion_' . $seccionCounter;
                    $placeholder = null;
                    $ayuda = null;
                    $opciones = null;
                    $valorDefecto = null;
                    $requerido = 0;
                } else {
                    $nombre = $field['nombre'];
                    $placeholder = $field['placeholder'] ?? null;
                    $ayuda = $field['ayuda'] ?? null;
                    $opciones = !empty($field['opciones']) ? json_encode($field['opciones']) : null;
                    $valorDefecto = $field['valor_defecto'] ?? null;
                    $requerido = !empty($field['requerido']) ? 1 : 0;
                }

                $condicionPadreRaw = $field['condicion_campo_padre'] ?? null;
                $condicionValor = $field['condicion_valor'] ?? null;

                $newId = $db->insert('form_fields', [
                    'form_id' => $formId,
                    'tipo' => $field['tipo'],
                    'nombre' => $nombre,
                    'etiqueta' => $field['etiqueta'],
                    'placeholder' => $placeholder ?: null,
                    'ayuda' => $ayuda ?: null,
                    'requerido' => $requerido,
                    'opciones' => $opciones,
                    'valor_defecto' => $valorDefecto ?: null,
                    'orden' => $orden++,
                    'condicion_campo_padre' => null,
                    'condicion_valor' => $condicionValor ?: null,
                ]);

                $nombreToId[$field['nombre']] = $newId;
            }

            foreach ($fields as $field) {
                $condicionPadreRaw = $field['condicion_campo_padre'] ?? null;
                if (!$condicionPadreRaw || !$field['condicion_valor']) continue;

                $childId = $nombreToId[$field['nombre']] ?? null;
                $parentId = $nombreToId[$condicionPadreRaw] ?? null;

                if (!$parentId && is_numeric($condicionPadreRaw)) {
                    $parentId = (int)$condicionPadreRaw;
                }

                if ($childId && $parentId) {
                    $db->update('form_fields',
                        ['condicion_campo_padre' => $parentId],
                        'id = :id',
                        ['id' => $childId]
                    );
                }
            }

            $db->commit();
            $this->json(['success' => true, 'message' => 'Campos guardados exitosamente.', 'redirect' => APP_URL . "/formularios/{$formId}/editar"]);
        } catch (\Exception $e) {
            $db->rollback();
            $this->json(['success' => false, 'message' => 'Error al guardar campos: ' . $e->getMessage()], 500);
        }
    }

    public function duplicate(Request $request): void
    {
        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $form = $db->fetch("SELECT * FROM forms WHERE id = :id AND deleted_at IS NULL", ['id' => $id]);
        if (!$form) {
            $this->redirectWith(APP_URL . '/formularios', 'error', 'Formulario no encontrado.');
        }

        $fields = $db->fetchAll(
            "SELECT * FROM form_fields WHERE form_id = :form_id AND deleted_at IS NULL ORDER BY orden",
            ['form_id' => $id]
        );

        $db->beginTransaction();
        try {
            $newFormId = $db->insert('forms', [
                'titulo' => $form->titulo . ' (copia)',
                'descripcion' => $form->descripcion,
                'estado' => 'borrador',
                'created_by' => Session::userId(),
            ]);

            $idMap = [];
            foreach ($fields as $field) {
                $newFieldId = $db->insert('form_fields', [
                    'form_id' => $newFormId,
                    'tipo' => $field->tipo,
                    'nombre' => $field->nombre,
                    'etiqueta' => $field->etiqueta,
                    'placeholder' => $field->placeholder,
                    'ayuda' => $field->ayuda,
                    'requerido' => $field->requerido,
                    'opciones' => $field->opciones,
                    'valor_defecto' => $field->valor_defecto,
                    'orden' => $field->orden,
                ]);
                $idMap[$field->id] = $newFieldId;
            }

            foreach ($fields as $field) {
                if ($field->condicion_campo_padre && isset($idMap[$field->condicion_campo_padre])) {
                    $db->update('form_fields', [
                        'condicion_campo_padre' => $idMap[$field->condicion_campo_padre],
                        'condicion_valor' => $field->condicion_valor,
                    ], 'id = :id', ['id' => $idMap[$field->id]]);
                }
            }

            $db->commit();

            AuditService::register('duplicar_formulario', 'formularios', "Formulario duplicado: {$form->titulo}", null, 'info', [], 'form', $newFormId);

            $this->redirectWith(APP_URL . '/formularios', 'success', 'Formulario duplicado exitosamente.');
        } catch (\Exception $e) {
            $db->rollback();
            $this->redirectWith(APP_URL . '/formularios', 'error', 'Error al duplicar el formulario.');
        }
    }

    public function toggleStatus(Request $request): void
    {
        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $form = $db->fetch("SELECT * FROM forms WHERE id = :id AND deleted_at IS NULL", ['id' => $id]);
        if (!$form) {
            $this->redirectWith(APP_URL . '/formularios', 'error', 'Formulario no encontrado.');
        }

        $nuevoEstado = match ($form->estado) {
            'borrador' => 'publicado',
            'publicado' => 'despublicado',
            'despublicado' => 'publicado',
            default => 'borrador',
        };

        $db->update('forms', ['estado' => $nuevoEstado], 'id = :id', ['id' => $id]);

        AuditService::register('cambiar_estado_formulario', 'formularios', "Formulario {$nuevoEstado}: {$form->titulo}", null, 'warning', ['estado' => $nuevoEstado], 'form', $id);

        $this->redirectWith(APP_URL . '/formularios', 'success', "Formulario {$nuevoEstado} exitosamente.");
    }

    public function delete(Request $request): void
    {
        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $form = $db->fetch("SELECT * FROM forms WHERE id = :id AND deleted_at IS NULL", ['id' => $id]);
        if (!$form) {
            $this->redirectWith(APP_URL . '/formularios', 'error', 'Formulario no encontrado.');
        }

        $db->update('forms', ['deleted_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);

        AuditService::register('eliminar_formulario', 'formularios', "Formulario eliminado: {$form->titulo}", null, 'danger', [], 'form', $id);

        $this->redirectWith(APP_URL . '/formularios', 'success', 'Formulario eliminado exitosamente.');
    }
}

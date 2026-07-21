<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;
use App\Core\Session;
use App\Services\AuditService;
use App\Services\MailService;
use App\Helpers\SecurityHelper;
use App\Helpers\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->authorizeAny(['super_usuario', 'administrador']);
    }

    public function index(Request $request): void
    {
        $db = Database::getInstance();
        $page = (int)($request->query('page', 1));
        $search = $request->query('search');
        $estado = $request->query('estado');
        $rol = $request->query('rol');
        $localidad = $request->query('localidad');

        $where = [];
        $params = [];

        if ($search) {
            $where[] = '(u.apellido LIKE :search OR u.nombre LIKE :search2 OR u.email LIKE :search3 OR u.dni LIKE :search4)';
            $params['search'] = "%{$search}%";
            $params['search2'] = "%{$search}%";
            $params['search3'] = "%{$search}%";
            $params['search4'] = "%{$search}%";
        }
        if ($estado) {
            $where[] = 'u.estado = :estado';
            $params['estado'] = $estado;
        }
        if ($rol) {
            $where[] = 'r.slug = :rol';
            $params['rol'] = $rol;
        }
        if ($localidad) {
            $where[] = 'u.localidad = :localidad';
            $params['localidad'] = $localidad;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) . ' AND u.deleted_at IS NULL' : 'WHERE u.deleted_at IS NULL';
        $limit = PAGINATION_LIMIT;
        $offset = ($page - 1) * $limit;

        $sql = "SELECT u.*, r.nombre as rol_nombre, r.slug as rol_slug
                FROM users u
                JOIN roles r ON u.rol_id = r.id
                {$whereClause}
                ORDER BY u.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";

        $countSql = "SELECT COUNT(*) as total FROM users u JOIN roles r ON u.rol_id = r.id {$whereClause}";

        $users = $db->fetchAll($sql, $params);
        $total = (int)$db->fetch($countSql, $params)->total;
        $totalPages = (int)ceil($total / $limit);

        $roles = $db->fetchAll("SELECT * FROM roles ORDER BY id");
        $localidades = $db->fetchAll(
            "SELECT DISTINCT localidad FROM users WHERE localidad IS NOT NULL AND localidad != '' AND deleted_at IS NULL ORDER BY localidad"
        );

        $this->view('users.index', [
            'users' => $users,
            'roles' => $roles,
            'localidades' => $localidades,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'filtroEstado' => $estado,
            'filtroRol' => $rol,
            'filtroLocalidad' => $localidad,
        ]);
    }

    public function create(Request $request): void
    {
        $db = Database::getInstance();
        $roles = $db->fetchAll("SELECT * FROM roles ORDER BY id");
        $this->view('users.create', ['roles' => $roles]);
    }

    public function store(Request $request): void
    {
        $data = $request->only(['apellido', 'nombre', 'dni', 'email', 'telefono', 'localidad', 'rol_id']);

        $validator = Validator::validate($data, [
            'apellido' => 'required|max:100',
            'nombre' => 'required|max:100',
            'dni' => 'required|dni|unique:users,dni,,,true',
            'email' => 'required|email|unique:users,email,,,true',
            'rol_id' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect(APP_URL . '/usuarios/crear');
        }

        $password = SecurityHelper::generatePassword();
        $hashedPassword = SecurityHelper::hashPassword($password);

        $db = Database::getInstance();
        $userId = $db->insert('users', [
            'apellido' => $data['apellido'],
            'nombre' => $data['nombre'],
            'dni' => $data['dni'],
            'email' => $data['email'],
            'telefono' => $data['telefono'] ?? null,
            'localidad' => $data['localidad'] ?? null,
            'password' => $hashedPassword,
            'password_temporal' => true,
            'rol_id' => (int)$data['rol_id'],
        ]);

        MailService::sendWelcome($data['email'], $data['nombre'], $password);

        AuditService::register('crear_usuario', 'usuarios', "Usuario creado: {$data['email']}", null, 'success', ['email' => $data['email'], 'nombre' => $data['nombre']], 'user');

        $this->redirectWith(APP_URL . '/usuarios', 'success', 'Usuario creado exitosamente. Se ha enviado un correo con las credenciales.');
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $user = $db->fetch(
            "SELECT u.*, r.nombre as rol_nombre FROM users u JOIN roles r ON u.rol_id = r.id WHERE u.id = :id",
            ['id' => $id]
        );

        if (!$user) {
            $this->redirectWith(APP_URL . '/usuarios', 'error', 'Usuario no encontrado.');
        }

        $roles = $db->fetchAll("SELECT * FROM roles ORDER BY id");
        $this->view('users.edit', ['user' => $user, 'roles' => $roles]);
    }

    public function update(Request $request): void
    {
        $id = (int)$request->param('id');
        $data = $request->only(['apellido', 'nombre', 'dni', 'email', 'telefono', 'localidad', 'rol_id']);

        $validator = Validator::validate($data, [
            'apellido' => 'required|max:100',
            'nombre' => 'required|max:100',
            'dni' => "required|dni|unique:users,dni,{$id},id,true",
            'email' => "required|email|unique:users,email,{$id},id,true",
        ]);

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect(APP_URL . "/usuarios/{$id}/editar");
        }

        $db = Database::getInstance();
        $db->update('users', [
            'apellido' => $data['apellido'],
            'nombre' => $data['nombre'],
            'dni' => $data['dni'],
            'email' => $data['email'],
            'telefono' => $data['telefono'] ?? null,
            'localidad' => $data['localidad'] ?? null,
            'rol_id' => (int)$data['rol_id'],
        ], 'id = :id', ['id' => $id]);

        AuditService::register('editar_usuario', 'usuarios', "Usuario editado: {$data['email']}", null, 'warning', ['email' => $data['email']], 'user', $id);

        $this->redirectWith(APP_URL . '/usuarios', 'success', 'Usuario actualizado exitosamente.');
    }

    public function toggleStatus(Request $request): void
    {
        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $user = $db->fetch("SELECT * FROM users WHERE id = :id", ['id' => $id]);
        if (!$user) {
            $this->redirectWith(APP_URL . '/usuarios', 'error', 'Usuario no encontrado.');
        }

        $nuevoEstado = $user->estado === 'activo' ? 'inactivo' : 'activo';
        $db->update('users', ['estado' => $nuevoEstado], 'id = :id', ['id' => $id]);

        $accion = $nuevoEstado === 'activo' ? 'activar_usuario' : 'desactivar_usuario';
        $tipo = $nuevoEstado === 'activo' ? 'success' : 'warning';
        AuditService::register($accion, 'usuarios', "Usuario {$nuevoEstado}: {$user->email}", null, $tipo, [], 'user', $id);

        $this->redirectWith(APP_URL . '/usuarios', 'success', "Usuario {$nuevoEstado} exitosamente.");
    }

    public function resetPassword(Request $request): void
    {
        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $user = $db->fetch("SELECT * FROM users WHERE id = :id", ['id' => $id]);
        if (!$user) {
            $this->redirectWith(APP_URL . '/usuarios', 'error', 'Usuario no encontrado.');
        }

        $password = SecurityHelper::generatePassword();
        $hashedPassword = SecurityHelper::hashPassword($password);

        $db->update('users', [
            'password' => $hashedPassword,
            'password_temporal' => true,
        ], 'id = :id', ['id' => $id]);

        MailService::sendNewPassword($user->email, $user->nombre, $password);

        AuditService::register('reset_password_usuario', 'usuarios', "Contraseña restablecida: {$user->email}", null, 'warning', [], 'user', $id);

        $this->redirectWith(APP_URL . '/usuarios', 'success', 'Contraseña restablecida. Se ha enviado un correo con la nueva contraseña.');
    }

    public function delete(Request $request): void
    {
        $id = (int)$request->param('id');

        if ($id === Session::userId()) {
            $this->redirectWith(APP_URL . '/usuarios', 'error', 'No puedes eliminar tu propio usuario.');
        }

        $db = Database::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE id = :id", ['id' => $id]);

        if (!$user) {
            $this->redirectWith(APP_URL . '/usuarios', 'error', 'Usuario no encontrado.');
        }

        $db->update('users', ['deleted_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);

        AuditService::register('eliminar_usuario', 'usuarios', "Usuario eliminado: {$user->email}", null, 'danger', [], 'user', $id);

        $this->redirectWith(APP_URL . '/usuarios', 'success', 'Usuario eliminado exitosamente.');
    }

    public function restore(Request $request): void
    {
        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $user = $db->fetch("SELECT * FROM users WHERE id = :id", ['id' => $id]);
        if (!$user) {
            $this->redirectWith(APP_URL . '/usuarios', 'error', 'Usuario no encontrado.');
        }

        $db->update('users', ['deleted_at' => null], 'id = :id', ['id' => $id]);

        AuditService::register('restaurar_usuario', 'usuarios', "Usuario restaurado: {$user->email}", null, 'success', [], 'user', $id);

        $this->redirectWith(APP_URL . '/usuarios', 'success', 'Usuario restaurado exitosamente.');
    }

    public function trashed(Request $request): void
    {
        $db = Database::getInstance();
        $users = $db->fetchAll(
            "SELECT u.*, r.nombre as rol_nombre
             FROM users u
             JOIN roles r ON u.rol_id = r.id
             WHERE u.deleted_at IS NOT NULL
             ORDER BY u.deleted_at DESC"
        );

        $this->view('users.trashed', ['users' => $users]);
    }
}

<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;
use App\Core\Session;
use App\Services\AuditService;

class ConfigController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->authorize('super_usuario');
    }

    public function index(Request $request): void
    {
        $db = Database::getInstance();
        $configs = $db->fetchAll("SELECT * FROM configuraciones ORDER BY clave");
        $config = [];
        foreach ($configs as $c) {
            $config[$c->clave] = $c->valor;
        }

        $this->view('config.index', ['config' => $config]);
    }

    public function update(Request $request): void
    {
        $db = Database::getInstance();
        $data = $request->all();

        $allowedKeys = [
            'nombre_sistema', 'zona_horaria', 'registros_por_pagina',
            'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_user',
            'smtp_pass', 'smtp_from_email', 'smtp_from_name',
        ];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                $existing = $db->fetch(
                    "SELECT id FROM configuraciones WHERE clave = :clave",
                    ['clave' => $key]
                );

                if ($existing) {
                    $db->update('configuraciones', ['valor' => $value],
                        'clave = :clave', ['clave' => $key]);
                } else {
                    $db->insert('configuraciones', ['clave' => $key, 'valor' => $value]);
                }
            }
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $file = $_FILES['logo'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                $uploadDir = UPLOADS_PATH . '/images';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $filename = 'logo_' . uniqid() . '.' . $ext;
                move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename);

                $existing = $db->fetch(
                    "SELECT id FROM configuraciones WHERE clave = 'logo'"
                );
                if ($existing) {
                    $db->update('configuraciones', ['valor' => "uploads/images/{$filename}"],
                        "clave = 'logo'");
                } else {
                    $db->insert('configuraciones', ['clave' => 'logo', 'valor' => "uploads/images/{$filename}"]);
                }
            }
        }

        AuditService::register('configuracion_actualizada', 'configuracion', 'Configuración del sistema actualizada', null, 'warning');

        $this->redirectWith(APP_URL . '/configuracion', 'success', 'Configuración actualizada exitosamente.');
    }
}

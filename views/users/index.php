<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900">Usuarios</h1>
        <a href="<?= APP_URL ?>/usuarios/crear"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors">
            <i class="fas fa-plus mr-2"></i> Nuevo Usuario
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <input type="text" name="search" placeholder="Buscar..." value="<?= $search ?? '' ?>"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            <select name="rol" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">Todos los roles</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= $r->slug ?>" <?= ($filtroRol ?? '') === $r->slug ? 'selected' : '' ?>><?= $r->nombre ?></option>
                <?php endforeach; ?>
            </select>
            <select name="estado" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">Todos los estados</option>
                <option value="activo" <?= ($filtroEstado ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
                <option value="inactivo" <?= ($filtroEstado ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                <option value="bloqueado" <?= ($filtroEstado ?? '') === 'bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
            </select>
            <select name="localidad" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">Todas las localidades</option>
                <?php foreach ($localidades as $l): ?>
                    <option value="<?= $l->localidad ?>" <?= ($filtroLocalidad ?? '') === $l->localidad ? 'selected' : '' ?>><?= $l->localidad ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">
                <i class="fas fa-search mr-1"></i> Filtrar
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Contacto</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">Rol</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">Último Acceso</th>
                        <th class="px-4 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 sm:px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <?php if (!empty($u->avatar)): ?>
                                <img src="<?= APP_URL ?>/<?= $u->avatar ?>"
                                     alt="Foto de perfil"
                                     class="w-8 h-8 rounded-full object-cover border border-gray-200 flex-shrink-0">
                                <?php else: ?>
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-sm font-medium text-blue-600"><?= strtoupper(substr($u->nombre, 0, 1) . substr($u->apellido, 0, 1)) ?></span>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?= $u->apellido . ', ' . $u->nombre ?></p>
                                    <p class="text-xs text-gray-500">DNI: <?= $u->dni ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 sm:px-6 py-4 hidden md:table-cell">
                            <p class="text-sm text-gray-700"><?= $u->email ?></p>
                            <p class="text-xs text-gray-500"><?= $u->telefono ?? '-' ?></p>
                        </td>
                        <td class="px-4 sm:px-6 py-4 hidden lg:table-cell">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?= $u->rol_slug === 'super_usuario' ? 'bg-purple-100 text-purple-800' : ($u->rol_slug === 'administrador' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') ?>">
                                <?= $u->rol_nombre ?>
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?= $u->estado === 'activo' ? 'bg-green-100 text-green-800' : ($u->estado === 'bloqueado' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') ?>">
                                <?= ucfirst($u->estado) ?>
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-4 hidden lg:table-cell text-sm text-gray-500">
                            <?= $u->ultimo_acceso ? date('d/m/Y H:i', strtotime($u->ultimo_acceso)) : 'Nunca' ?>
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="<?= APP_URL ?>/usuarios/<?= $u->id ?>/editar"
                                   class="p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="confirmToggle(<?= $u->id ?>, '<?= $u->estado === 'activo' ? 'desactivar' : 'activar' ?>')"
                                        class="p-1.5 text-gray-400 hover:text-amber-600 rounded-lg hover:bg-amber-50">
                                    <i class="fas <?= $u->estado === 'activo' ? 'fa-ban' : 'fa-check' ?>"></i>
                                </button>
                                <button onclick="confirmReset(<?= $u->id ?>)"
                                        class="p-1.5 text-gray-400 hover:text-purple-600 rounded-lg hover:bg-purple-50">
                                    <i class="fas fa-key"></i>
                                </button>
                                <button onclick="confirmDelete(<?= $u->id ?>)"
                                        class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                    <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No se encontraron usuarios</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="px-4 sm:px-6 py-3 border-t border-gray-200 flex items-center justify-between">
            <p class="text-sm text-gray-500">Página <?= $page ?> de <?= $totalPages ?></p>
            <div class="flex space-x-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&search=<?= $search ?? '' ?>&estado=<?= $filtroEstado ?? '' ?>&rol=<?= $filtroRol ?? '' ?>"
                       class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Anterior</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= $search ?? '' ?>&estado=<?= $filtroEstado ?? '' ?>&rol=<?= $filtroRol ?? '' ?>"
                       class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Siguiente</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="flex justify-end">
        <a href="<?= APP_URL ?>/usuarios/eliminados"
           class="text-sm text-gray-500 hover:text-blue-600">
            <i class="fas fa-trash-restore mr-1"></i> Ver usuarios eliminados
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmToggle(id, action) {
    Swal.fire({
        title: '¿' + action.charAt(0).toUpperCase() + action.slice(1) + ' usuario?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, ' + action,
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= APP_URL ?>/usuarios/' + id + '/toggle-estado';
            form.innerHTML = '<input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">';
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function confirmReset(id) {
    Swal.fire({
        title: '¿Restablecer contraseña?',
        text: 'Se enviará una nueva contraseña temporal al usuario.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#7c3aed',
        confirmButtonText: 'Sí, restablecer',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= APP_URL ?>/usuarios/' + id + '/reset-password';
            form.innerHTML = '<input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">';
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function confirmDelete(id) {
    Swal.fire({
        title: '¿Eliminar usuario?',
        text: 'El usuario será eliminado (soft delete). Puedes restaurarlo después.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= APP_URL ?>/usuarios/' + id + '/eliminar';
            form.innerHTML = '<input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">';
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

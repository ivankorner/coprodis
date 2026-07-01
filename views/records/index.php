<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900">Registros</h1>
        <div class="flex space-x-2">
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                    <i class="fas fa-plus mr-2"></i> Nuevo Registro
                </button>
                <div x-show="open" @click.outside="open = false"
                     class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                    <div class="p-3 border-b border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 uppercase">Seleccionar formulario</p>
                    </div>
                    <div class="max-h-60 overflow-y-auto">
                        <?php
                        $db = \App\Core\Database::getInstance();
                        $formsList = $db->fetchAll("SELECT id, titulo FROM forms WHERE deleted_at IS NULL AND estado = 'publicado' ORDER BY titulo");
                        ?>
                        <?php foreach ($formsList as $f): ?>
                            <a href="<?= APP_URL ?>/registros/crear/<?= $f->id ?>"
                               class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                <?= $f->titulo ?>
                            </a>
                        <?php endforeach; ?>
                        <?php if (empty($formsList)): ?>
                            <p class="px-4 py-3 text-sm text-gray-500">No hay formularios disponibles</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">
                    <i class="fas fa-download mr-2"></i> Exportar
                </button>
                <div x-show="open" @click.outside="open = false"
                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                    <form action="<?= APP_URL ?>/exportar/registros/excel" method="POST" class="inline">
                        <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
                        <button type="submit" class="w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 text-left">
                            <i class="fas fa-file-excel mr-2 text-green-600"></i> Excel
                        </button>
                    </form>
                    <form action="<?= APP_URL ?>/exportar/registros/csv" method="POST" class="inline">
                        <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
                        <button type="submit" class="w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 text-left">
                            <i class="fas fa-file-csv mr-2 text-blue-600"></i> CSV
                        </button>
                    </form>
                    <form action="<?= APP_URL ?>/exportar/registros/pdf" method="POST" class="inline">
                        <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
                        <button type="submit" class="w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 text-left">
                            <i class="fas fa-file-pdf mr-2 text-red-600"></i> PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <input type="text" name="search" placeholder="Buscar..." value="<?= $search ?? '' ?>"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <select name="form_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Todos los formularios</option>
                <?php foreach ($forms as $f): ?>
                    <option value="<?= $f->id ?>" <?= ($filtroForm ?? '') == $f->id ? 'selected' : '' ?>><?= $f->titulo ?></option>
                <?php endforeach; ?>
            </select>
            <select name="estado" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Todos los estados</option>
                <option value="activo" <?= ($filtroEstado ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
                <option value="archivado" <?= ($filtroEstado ?? '') === 'archivado' ? 'selected' : '' ?>>Archivado</option>
            </select>
            <input type="date" name="fecha_desde" value="<?= $filtroFechaDesde ?? '' ?>"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <input type="date" name="fecha_hasta" value="<?= $filtroFechaHasta ?? '' ?>"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
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
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Formulario</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Persona</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">Creado</th>
                        <th class="px-4 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($records as $r): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 sm:px-6 py-4 text-sm font-medium text-gray-900">#<?= $r->id ?></td>
                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-700"><?= $r->form_titulo ?></td>
                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-500 hidden md:table-cell"><?= $r->persona_apellido . ' ' . $r->persona_nombre ?></td>
                        <td class="px-4 sm:px-6 py-4">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?= $r->estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                <?= ucfirst($r->estado) ?>
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-500 hidden lg:table-cell">
                            <?= date('d/m/Y H:i', strtotime($r->created_at)) ?>
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="<?= APP_URL ?>/registros/<?= $r->id ?>"
                                   class="p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= APP_URL ?>/registros/<?= $r->id ?>/editar"
                                   class="p-1.5 text-gray-400 hover:text-amber-600 rounded-lg hover:bg-amber-50">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($r->estado === 'activo'): ?>
                                <form action="<?= APP_URL ?>/registros/<?= $r->id ?>/archivar" method="POST" class="inline">
                                    <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-purple-600 rounded-lg hover:bg-purple-50"
                                            onclick="event.preventDefault(); confirmSwal('Archivar registro', '¿Archivar este registro?', () => this.closest('form').submit())">
                                        <i class="fas fa-archive"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php if ($r->estado === 'archivado'): ?>
                                <form action="<?= APP_URL ?>/registros/<?= $r->id ?>/eliminar" method="POST" class="inline">
                                    <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50"
                                            onclick="event.preventDefault(); confirmSwal('Eliminar registro', '¿Eliminar este registro permanentemente?', () => this.closest('form').submit())">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <a href="<?= APP_URL ?>/registros/<?= $r->id ?>/historial"
                                   class="p-1.5 text-gray-400 hover:text-green-600 rounded-lg hover:bg-green-50 hidden lg:block">
                                    <i class="fas fa-history"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($records)): ?>
                    <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No se encontraron registros</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="px-4 sm:px-6 py-3 border-t border-gray-200 flex items-center justify-between">
            <p class="text-sm text-gray-500">Página <?= $page ?> de <?= $totalPages ?></p>
            <div class="flex space-x-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&search=<?= $search ?? '' ?>&form_id=<?= $filtroForm ?? '' ?>&estado=<?= $filtroEstado ?? '' ?>"
                       class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Anterior</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= $search ?? '' ?>&form_id=<?= $filtroForm ?? '' ?>&estado=<?= $filtroEstado ?? '' ?>"
                       class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Siguiente</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

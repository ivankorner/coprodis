<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Auditoría del Sistema</h1>
        <a href="<?= APP_URL ?>/auditoria/exportar?<?= http_build_query($filters ?? []) ?>"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
            <i class="fas fa-file-excel mr-2"></i> Exportar
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <input type="text" name="accion" placeholder="Acción..." value="<?= $filters['accion'] ?? '' ?>"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <select name="modulo" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Todos los módulos</option>
                <?php foreach ($modulos as $m): ?>
                    <option value="<?= $m->modulo ?>" <?= ($filters['modulo'] ?? '') === $m->modulo ? 'selected' : '' ?>><?= ucfirst($m->modulo) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="user_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Todos los usuarios</option>
                <?php foreach ($usuarios as $u): ?>
                    <option value="<?= $u->id ?>" <?= ($filters['user_id'] ?? '') == $u->id ? 'selected' : '' ?>><?= $u->apellido . ' ' . $u->nombre ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="fecha_desde" value="<?= $filters['fecha_desde'] ?? '' ?>"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <input type="date" name="fecha_hasta" value="<?= $filters['fecha_hasta'] ?? '' ?>"
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
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acción</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Módulo</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">IP</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">Descripción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($data as $audit): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 sm:px-6 py-3 text-sm text-gray-500 whitespace-nowrap"><?= date('d/m/Y H:i', strtotime($audit->created_at)) ?></td>
                        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700"><?= ($audit->apellido ?? 'Sistema') . ' ' . ($audit->nombre ?? '') ?></td>
                        <td class="px-4 sm:px-6 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                <?= $audit->accion ?>
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-3 text-sm text-gray-500 hidden md:table-cell"><?= ucfirst($audit->modulo) ?></td>
                        <td class="px-4 sm:px-6 py-3 text-sm text-gray-400 font-mono hidden lg:table-cell"><?= $audit->ip ?></td>
                        <td class="px-4 sm:px-6 py-3 text-sm text-gray-500 max-w-xs truncate hidden lg:table-cell"><?= $audit->descripcion ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($data)): ?>
                    <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No se encontraron registros de auditoría</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="px-6 py-3 border-t border-gray-200 flex items-center justify-between">
            <p class="text-sm text-gray-500">Página <?= $page ?> de <?= $totalPages ?></p>
            <div class="flex space-x-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&accion=<?= $filters['accion'] ?? '' ?>&modulo=<?= $filters['modulo'] ?? '' ?>&user_id=<?= $filters['user_id'] ?? '' ?>"
                       class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Anterior</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&accion=<?= $filters['accion'] ?? '' ?>&modulo=<?= $filters['modulo'] ?? '' ?>&user_id=<?= $filters['user_id'] ?? '' ?>"
                       class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Siguiente</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

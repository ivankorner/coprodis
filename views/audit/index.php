<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Auditoría del Sistema</h1>
        <a href="<?= APP_URL ?>/auditoria/exportar?<?= http_build_query($filters ?? []) ?>"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
            <i class="fas fa-file-excel mr-2"></i> Exportar
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Hoy</p>
            <p class="text-2xl font-bold text-blue-600 mt-1"><?= number_format($stats['hoy']) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Esta Semana</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1"><?= number_format($stats['semana']) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Este Mes</p>
            <p class="text-2xl font-bold text-purple-600 mt-1"><?= number_format($stats['mes']) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total</p>
            <p class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($stats['total']) ?></p>
        </div>
    </div>

    <!-- Timeline Chart + Module Breakdown -->
    <div class="grid lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Actividad (Últimos 7 días)</h3>
            <canvas id="auditTimeline" height="80"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Por Módulo</h3>
            <div class="space-y-2">
                <?php foreach ($stats['por_modulo'] as $m): ?>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 capitalize"><?= $m->modulo ?></span>
                    <span class="font-semibold text-gray-900"><?= number_format($m->total) ?></span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5">
                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: <?= min(100, ($m->total / max($stats['por_modulo'][0]->total, 1)) * 100) ?>%"></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3">
            <input type="text" name="accion" placeholder="Acción..." value="<?= $filters['accion'] ?? '' ?>"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <select name="modulo" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Todos los módulos</option>
                <?php foreach ($modulos as $m): ?>
                    <option value="<?= $m->modulo ?>" <?= ($filters['modulo'] ?? '') === $m->modulo ? 'selected' : '' ?>><?= ucfirst($m->modulo) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="tipo" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Todos los tipos</option>
                <option value="info" <?= ($filters['tipo'] ?? '') === 'info' ? 'selected' : '' ?>>Info</option>
                <option value="success" <?= ($filters['tipo'] ?? '') === 'success' ? 'selected' : '' ?>>Éxito</option>
                <option value="warning" <?= ($filters['tipo'] ?? '') === 'warning' ? 'selected' : '' ?>>Advertencia</option>
                <option value="danger" <?= ($filters['tipo'] ?? '') === 'danger' ? 'selected' : '' ?>>Crítico</option>
            </select>
            <select name="user_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Todos los usuarios</option>
                <?php foreach ($usuarios as $u): ?>
                    <option value="<?= $u->id ?>" <?= ($filters['user_id'] ?? '') == $u->id ? 'selected' : '' ?>><?= $u->apellido . ' ' . $u->nombre ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="ip" placeholder="IP..." value="<?= $filters['ip'] ?? '' ?>"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <input type="date" name="fecha_desde" value="<?= $filters['fecha_desde'] ?? '' ?>"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">
                <i class="fas fa-search mr-1"></i> Filtrar
            </button>
        </form>
        <div class="flex flex-wrap gap-2 mt-3">
            <a href="?fecha_desde=<?= date('Y-m-d') ?>&fecha_hasta=<?= date('Y-m-d') ?>"
               class="px-3 py-1 text-xs font-medium rounded-full border <?= ($filters['fecha_desde'] ?? '') === date('Y-m-d') ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50' ?>">Hoy</a>
            <a href="?fecha_desde=<?= date('Y-m-d', strtotime('monday this week')) ?>&fecha_hasta=<?= date('Y-m-d') ?>"
               class="px-3 py-1 text-xs font-medium rounded-full border <?= ($filters['fecha_desde'] ?? '') === date('Y-m-d', strtotime('monday this week')) ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50' ?>">Esta Semana</a>
            <a href="?fecha_desde=<?= date('Y-m-01') ?>&fecha_hasta=<?= date('Y-m-t') ?>"
               class="px-3 py-1 text-xs font-medium rounded-full border <?= ($filters['fecha_desde'] ?? '') === date('Y-m-01') ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50' ?>">Este Mes</a>
            <?php if (!empty(array_filter($filters))): ?>
            <a href="<?= APP_URL ?>/auditoria"
               class="px-3 py-1 text-xs font-medium rounded-full bg-red-50 text-red-600 border border-red-200 hover:bg-red-100">Limpiar filtros</a>
            <?php endif; ?>
        </div>
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
                        <th class="px-4 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Detalle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($data as $audit): ?>
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="toggleDetail(<?= $audit->id ?>)">
                        <td class="px-4 sm:px-6 py-3 text-sm text-gray-500 whitespace-nowrap"><?= date('d/m/Y H:i', strtotime($audit->created_at)) ?></td>
                        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700">
                            <?php
                            $tipoDot = $audit->tipo_audit ?? 'info';
                            $dotColors = ['info' => 'bg-blue-500', 'success' => 'bg-green-500', 'warning' => 'bg-amber-500', 'danger' => 'bg-red-500'];
                            ?>
                            <span class="inline-block w-2 h-2 rounded-full <?= $dotColors[$tipoDot] ?? 'bg-gray-400' ?> mr-1.5"></span>
                            <?= ($audit->apellido ?? 'Sistema') . ' ' . ($audit->nombre ?? '') ?>
                        </td>
                        <td class="px-4 sm:px-6 py-3">
                            <?php
                            $actionBadge = match ($audit->accion) {
                                'inicio_sesion', 'inicio_sesion_fallido' => 'bg-blue-100 text-blue-700',
                                'cierre_sesion' => 'bg-gray-100 text-gray-600',
                                'crear_usuario', 'crear_formulario', 'crear_registro' => 'bg-green-100 text-green-700',
                                'editar_usuario', 'editar_formulario', 'editar_registro' => 'bg-amber-100 text-amber-700',
                                'eliminar_usuario', 'eliminar_formulario', 'eliminar_registro' => 'bg-red-100 text-red-700',
                                'archivar_registro' => 'bg-purple-100 text-purple-700',
                                'restaurar_usuario', 'restaurar_registro' => 'bg-teal-100 text-teal-700',
                                'exportar_excel', 'exportar_csv', 'exportar_pdf', 'exportar_auditoria' => 'bg-indigo-100 text-indigo-700',
                                default => 'bg-gray-100 text-gray-600'
                            };
                            ?>
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium <?= $actionBadge ?>">
                                <?= $audit->accion ?>
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-3 text-sm text-gray-500 hidden md:table-cell capitalize"><?= $audit->modulo ?></td>
                        <td class="px-4 sm:px-6 py-3 text-sm text-gray-400 font-mono hidden lg:table-cell"><?= $audit->ip ?></td>
                        <td class="px-4 sm:px-6 py-3 text-sm text-gray-500 max-w-xs truncate hidden lg:table-cell" title="<?= $audit->descripcion ?>"><?= $audit->descripcion ?></td>
                        <td class="px-4 sm:px-6 py-3 text-right">
                            <a href="<?= APP_URL ?>/auditoria/<?= $audit->id ?>"
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </td>
                    </tr>
                    <tr id="detail-<?= $audit->id ?>" class="hidden">
                        <td colspan="7" class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase">User Agent</p>
                                    <p class="mt-1 text-gray-700 break-words"><?= $audit->user_agent ?? 'No registrado' ?></p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase">IP</p>
                                    <p class="mt-1 text-gray-700 font-mono"><?= $audit->ip ?></p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase">ID Auditoría</p>
                                    <p class="mt-1 text-gray-700">#<?= $audit->id ?></p>
                                </div>
                                <?php if ($audit->entidad): ?>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase">Entidad</p>
                                    <p class="mt-1 text-gray-700 capitalize"><?= $audit->entidad ?> #<?= $audit->entidad_id ?>
                                    <?php if ($audit->entidad === 'record'): ?>
                                        <a href="<?= APP_URL ?>/registros/<?= $audit->entidad_id ?>" class="text-blue-600 hover:underline ml-1">Ver registro</a>
                                    <?php endif; ?>
                                    </p>
                                </div>
                                <?php endif; ?>
                                <?php if ($audit->detalles && is_string($audit->detalles)): ?>
                                <div class="sm:col-span-2 lg:col-span-3">
                                    <p class="text-xs font-medium text-gray-500 uppercase">Detalles</p>
                                    <pre class="mt-1 text-xs text-gray-600 bg-white rounded p-2 border border-gray-200 overflow-x-auto max-h-32"><?= $audit->detalles ?></pre>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="mt-3">
                                <a href="<?= APP_URL ?>/auditoria/<?= $audit->id ?>"
                                   class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                    <i class="fas fa-external-link-alt mr-1"></i> Ver detalle completo
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($data)): ?>
                    <tr><td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">No se encontraron registros de auditoría</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="px-6 py-3 border-t border-gray-200 flex items-center justify-between">
            <p class="text-sm text-gray-500">Página <?= $page ?> de <?= $totalPages ?></p>
            <div class="flex space-x-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&accion=<?= $filters['accion'] ?? '' ?>&modulo=<?= $filters['modulo'] ?? '' ?>&tipo=<?= $filters['tipo'] ?? '' ?>&user_id=<?= $filters['user_id'] ?? '' ?>&ip=<?= $filters['ip'] ?? '' ?>&fecha_desde=<?= $filters['fecha_desde'] ?? '' ?>&fecha_hasta=<?= $filters['fecha_hasta'] ?? '' ?>"
                       class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Anterior</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&accion=<?= $filters['accion'] ?? '' ?>&modulo=<?= $filters['modulo'] ?? '' ?>&tipo=<?= $filters['tipo'] ?? '' ?>&user_id=<?= $filters['user_id'] ?? '' ?>&ip=<?= $filters['ip'] ?? '' ?>&fecha_desde=<?= $filters['fecha_desde'] ?? '' ?>&fecha_hasta=<?= $filters['fecha_hasta'] ?? '' ?>"
                       class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Siguiente</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleDetail(id) {
    const row = document.getElementById('detail-' + id);
    if (row) row.classList.toggle('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('auditTimeline');
    if (ctx) {
        const data = <?= json_encode($timeline ?? []) ?>;
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => {
                    const parts = d.fecha.split('-');
                    return parts[2] + '/' + parts[1];
                }),
                datasets: [
                    {
                        label: 'Info',
                        data: data.map(d => parseInt(d.info)),
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1
                    },
                    {
                        label: 'Éxito',
                        data: data.map(d => parseInt(d.success)),
                        backgroundColor: 'rgba(34, 197, 94, 0.7)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1
                    },
                    {
                        label: 'Advertencia',
                        data: data.map(d => parseInt(d.warning)),
                        backgroundColor: 'rgba(245, 158, 11, 0.7)',
                        borderColor: 'rgb(245, 158, 11)',
                        borderWidth: 1
                    },
                    {
                        label: 'Crítico',
                        data: data.map(d => parseInt(d.danger)),
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderColor: 'rgb(239, 68, 68)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 9 } } },
                    y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 9 } } }
                }
            }
        });
    }
});
</script>

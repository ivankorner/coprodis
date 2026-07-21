<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500"><?= date('d/m/Y H:i') ?></p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-medium text-gray-500">Total Usuarios</p>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900"><?= $stats['total_usuarios'] ?></p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-medium text-gray-500">Usuarios Activos</p>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-check text-green-600"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-green-600"><?= $stats['usuarios_activos'] ?></p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-medium text-gray-500">Registros Hoy</p>
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-alt text-purple-600"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-purple-600"><?= $stats['registros_hoy'] ?></p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-medium text-gray-500">Registros del Mes</p>
                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-amber-600"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-amber-600"><?= $stats['registros_mes'] ?></p>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Registros por Formulario</h3>
            <canvas id="formChart" height="200"></canvas>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Actividad Reciente</h3>
            <div class="space-y-3">
                <?php if (empty($actividadReciente)): ?>
                    <p class="text-sm text-gray-500 text-center py-4">Sin actividad reciente</p>
                <?php else: ?>
                    <?php foreach ($actividadReciente as $act): ?>
                        <div class="flex items-start space-x-3">
                            <div class="w-2 h-2 mt-2 bg-blue-500 rounded-full"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-700 truncate">
                                    <?= ($act->apellido ?? 'Sistema') . ' ' . ($act->nombre ?? '') ?> -
                                    <span class="text-gray-500"><?= $act->accion ?></span>
                                </p>
                                <p class="text-xs text-gray-400"><?= date('d/m/Y H:i', strtotime($act->created_at)) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Records -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-700">Últimos Registros</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Formulario</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($ultimosRegistros)): ?>
                        <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">Sin registros</td></tr>
                    <?php else: ?>
                        <?php foreach ($ultimosRegistros as $r): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 sm:px-6 py-3 text-sm font-medium text-gray-900">#<?= $r->id ?></td>
                            <td class="px-4 sm:px-6 py-3 text-sm text-gray-700"><?= $r->form_titulo ?></td>
                            <td class="px-4 sm:px-6 py-3 text-sm text-gray-700"><?= $r->apellido . ' ' . $r->nombre ?></td>
                            <td class="px-4 sm:px-6 py-3 text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($r->created_at)) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('formChart');
    if (ctx) {
        const isDark = document.documentElement.classList.contains('dark');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_map(fn($f) => $f->titulo, $registrosPorFormulario ?? [])) ?>,
                datasets: [{
                    label: 'Registros',
                    data: <?= json_encode(array_map(fn($f) => (int)$f->total, $registrosPorFormulario ?? [])) ?>,
                    backgroundColor: isDark ? 'rgba(96, 165, 250, 0.6)' : 'rgba(37, 99, 235, 0.7)',
                    borderColor: isDark ? 'rgb(96, 165, 250)' : 'rgb(37, 99, 235)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: isDark ? '#9ca3af' : '#6b7280'
                        },
                        grid: { color: isDark ? 'rgba(75, 85, 99, 0.5)' : 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            font: { size: 10 },
                            color: isDark ? '#9ca3af' : '#6b7280'
                        },
                        grid: { color: isDark ? 'rgba(75, 85, 99, 0.3)' : 'rgba(0,0,0,0.05)' }
                    }
                }
            }
        });
    }
});
</script>

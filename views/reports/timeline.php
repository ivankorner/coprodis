<div class="space-y-6" x-data="{ viewMode: 'chart' }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="<?= APP_URL ?>/reportes" class="text-sm text-blue-600 hover:underline">&larr; Volver a reportes</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Timeline de Registros</h1>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="downloadChart('timelineChart', 'timeline-registros')"
                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 text-sm font-medium transition-colors">
                <i class="fas fa-download mr-1.5"></i> PNG
            </button>
            <div class="flex space-x-1 bg-gray-100 rounded-lg p-1">
                <button @click="viewMode = 'chart'" :class="viewMode === 'chart' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500'"
                        class="px-3 py-1.5 text-sm font-medium rounded-md transition-all">Gráfico</button>
                <button @click="viewMode = 'table'" :class="viewMode === 'table' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500'"
                        class="px-3 py-1.5 text-sm font-medium rounded-md transition-all">Tabla</button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <form method="GET" class="space-y-4">
            <div class="grid sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Formulario</label>
                    <select name="form_id"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="">Todos</option>
                        <?php foreach ($forms as $f): ?>
                        <option value="<?= $f->id ?>" <?= ($formId ?? '') == $f->id ? 'selected' : '' ?>><?= $f->titulo ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Desde</label>
                    <input type="date" name="fecha_desde" value="<?= $fechaDesde ?? '' ?>"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Hasta</label>
                    <input type="date" name="fecha_hasta" value="<?= $fechaHasta ?? '' ?>"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Días</label>
                    <select name="days"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <?php foreach ([7, 15, 30, 60, 90, 180, 365] as $d): ?>
                        <option value="<?= $d ?>" <?= ($days ?? 30) == $d ? 'selected' : '' ?>><?= $d ?> días</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors">
                    <i class="fas fa-search mr-1.5"></i> Filtrar
                </button>
                <?php if ($fechaDesde || $formId): ?>
                <a href="<?= APP_URL ?>/reportes/timeline"
                   class="px-4 py-2.5 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 text-sm font-medium transition-colors">
                    Limpiar
                </a>
                <?php endif; ?>
            </div>
        </form>
        <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-100">
            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider mr-1 self-center">Rápido:</span>
            <a href="?days=7<?= $formId ? '&form_id='.$formId : '' ?>"
               class="px-3.5 py-1.5 text-xs font-medium rounded-full border transition-colors <?= ($days ?? 30) == 7 && !$fechaDesde ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50 hover:border-gray-400' ?>">7 días</a>
            <a href="?days=30<?= $formId ? '&form_id='.$formId : '' ?>"
               class="px-3.5 py-1.5 text-xs font-medium rounded-full border transition-colors <?= ($days ?? 30) == 30 && !$fechaDesde ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50 hover:border-gray-400' ?>">30 días</a>
            <a href="?days=90<?= $formId ? '&form_id='.$formId : '' ?>"
               class="px-3.5 py-1.5 text-xs font-medium rounded-full border transition-colors <?= ($days ?? 30) == 90 && !$fechaDesde ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50 hover:border-gray-400' ?>">90 días</a>
            <a href="?days=365<?= $formId ? '&form_id='.$formId : '' ?>"
               class="px-3.5 py-1.5 text-xs font-medium rounded-full border transition-colors <?= ($days ?? 30) == 365 && !$fechaDesde ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50 hover:border-gray-400' ?>">1 año</a>
        </div>
    </div>

    <!-- Chart View -->
    <div x-show="viewMode === 'chart'" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700">Actividad de Registros</h3>
            <button onclick="downloadChart('timelineChart', 'timeline-registros')"
                    class="p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-colors text-xs" title="Descargar PNG">
                <i class="fas fa-download"></i>
            </button>
        </div>
        <canvas id="timelineChart" height="120"></canvas>
    </div>

    <!-- Table View -->
    <div x-show="viewMode === 'table'" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Registros Recientes</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Formulario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operador</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($records as $r): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3 text-sm font-medium text-gray-900">#<?= $r->id ?></td>
                        <td class="px-6 py-3 text-sm text-gray-700"><?= $r->form_titulo ?></td>
                        <td class="px-6 py-3 text-sm text-gray-500"><?= $r->apellido . ' ' . $r->nombre ?></td>
                        <td class="px-6 py-3 text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($r->created_at)) ?></td>
                        <td class="px-6 py-3 text-right">
                            <a href="<?= APP_URL ?>/registros/<?= $r->id ?>"
                               class="inline-flex items-center px-3 py-1.5 text-blue-600 hover:text-blue-800 text-sm font-medium rounded-lg hover:bg-blue-50 transition-colors">
                                <i class="fas fa-eye mr-1"></i> Ver
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($records)): ?>
                    <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No se encontraron registros en este período</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function downloadChart(canvasId, filename) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const link = document.createElement('a');
    link.download = filename + '.png';
    link.href = canvas.toDataURL('image/png');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

document.addEventListener('DOMContentLoaded', function() {
    const data = <?= json_encode($timeline) ?>;
    const ctx = document.getElementById('timelineChart');
    if (ctx && data.length) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => {
                    const p = d.fecha.split('-');
                    return p[2] + '/' + p[1];
                }),
                datasets: [{
                    label: 'Registros',
                    data: data.map(d => parseInt(d.total)),
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: data.length > 60 ? 1 : 3,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#3B82F6',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.parsed.y + ' registros'
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 }, maxTicksLimit: 20 }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { size: 10 } },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    } else if (ctx) {
        ctx.parentElement.innerHTML = '<div class="text-center py-12"><i class="fas fa-chart-line text-gray-300 text-4xl mb-3"></i><p class="text-gray-500">Sin datos para el período seleccionado</p></div>';
    }
});
</script>

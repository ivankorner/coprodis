<div class="space-y-6" x-data="{
    tab: 'general',
    formId: null,
    showSaveModal: false,
    favTitle: '',
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900">
            Reportes
            <?php if ($form): ?>
            <span class="text-base font-normal text-gray-400 ml-2">/ <?= $form->titulo ?></span>
            <?php endif; ?>
        </h1>
        <div class="flex space-x-2">
            <a href="<?= APP_URL ?>/reportes/timeline"
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium transition-colors">
                <i class="fas fa-chart-line mr-2"></i> Timeline
            </a>
            <form action="<?= APP_URL ?>/reportes/exportar/pdf-global" method="POST" class="inline">
                <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors">
                    <i class="fas fa-file-pdf mr-2"></i> Exportar PDF
                </button>
            </form>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <form method="GET" class="space-y-4">
            <div class="grid sm:grid-cols-4 gap-4">
                <div class="sm:col-span-1">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Formulario</label>
                    <select name="form_id"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="">Todos los formularios</option>
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
                <div class="flex items-end space-x-2">
                    <button type="submit"
                            class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors">
                        <i class="fas fa-search mr-1.5"></i> Aplicar
                    </button>
                    <?php if ($fechaDesde || $fechaHasta || $formId): ?>
                    <a href="<?= APP_URL ?>/reportes"
                       class="px-4 py-2.5 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 text-sm font-medium transition-colors">
                        Limpiar
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <!-- Quick Chips -->
        <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-100">
            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider mr-1 self-center">Rápido:</span>
            <a href="?days=1<?= $formId ? '&form_id='.$formId : '' ?>"
               class="px-3.5 py-1.5 text-xs font-medium rounded-full border transition-colors <?= ($days ?? 30) == 1 && !$fechaDesde && !$fechaHasta ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50 hover:border-gray-400' ?>">
                Hoy
            </a>
            <a href="?days=7<?= $formId ? '&form_id='.$formId : '' ?>"
               class="px-3.5 py-1.5 text-xs font-medium rounded-full border transition-colors <?= ($days ?? 30) == 7 && !$fechaDesde && !$fechaHasta ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50 hover:border-gray-400' ?>">
                7 días
            </a>
            <a href="?days=30<?= $formId ? '&form_id='.$formId : '' ?>"
               class="px-3.5 py-1.5 text-xs font-medium rounded-full border transition-colors <?= ($days ?? 30) == 30 && !$fechaDesde && !$fechaHasta ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50 hover:border-gray-400' ?>">
                30 días
            </a>
            <a href="?days=90<?= $formId ? '&form_id='.$formId : '' ?>"
               class="px-3.5 py-1.5 text-xs font-medium rounded-full border transition-colors <?= ($days ?? 30) == 90 && !$fechaDesde && !$fechaHasta ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50 hover:border-gray-400' ?>">
                90 días
            </a>
            <a href="?days=365<?= $formId ? '&form_id='.$formId : '' ?>"
               class="px-3.5 py-1.5 text-xs font-medium rounded-full border transition-colors <?= ($days ?? 30) == 365 && !$fechaDesde && !$fechaHasta ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50 hover:border-gray-400' ?>">
                1 año
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Registros</p>
            <p class="text-2xl font-bold text-blue-600 mt-1"><?= number_format($stats['total_registros']) ?></p>
            <?php if ($form): ?>
            <p class="text-xs text-gray-400 mt-0.5 truncate"><?= $form->titulo ?></p>
            <?php endif; ?>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Hoy</p>
            <p class="text-2xl font-bold text-green-600 mt-1"><?= number_format($stats['hoy']) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Esta Semana</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1"><?= number_format($stats['semana']) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Este Mes</p>
            <p class="text-2xl font-bold text-purple-600 mt-1"><?= number_format($stats['mes']) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Formularios</p>
            <p class="text-2xl font-bold text-amber-600 mt-1"><?= number_format($stats['formularios_activos']) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Usuarios</p>
            <p class="text-2xl font-bold text-teal-600 mt-1"><?= number_format($stats['usuarios_activos']) ?></p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex space-x-1 bg-gray-100 rounded-lg p-1 w-fit">
        <button @click="tab = 'general'" :class="tab === 'general' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm font-medium rounded-md transition-all">General</button>
        <button @click="tab = 'forms'" :class="tab === 'forms' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm font-medium rounded-md transition-all">Por Formulario</button>
        <button @click="tab = 'operators'" :class="tab === 'operators' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm font-medium rounded-md transition-all">Operadores</button>
        <button @click="tab = 'saved'" :class="tab === 'saved' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm font-medium rounded-md transition-all">Guardados</button>
    </div>

    <!-- General Tab -->
    <div x-show="tab === 'general'">
        <div class="grid lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">
                        <?= $form ? 'Campos del formulario' : 'Registros por Formulario' ?>
                    </h3>
                    <button onclick="downloadChart('recordsByFormChart', 'registros-por-formulario')"
                            class="p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-colors text-xs" title="Descargar PNG">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
                <canvas id="recordsByFormChart" height="160"></canvas>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">
                        Actividad
                        <?php if ($form): ?><span class="text-gray-400 font-normal"> — <?= $form->titulo ?></span><?php endif; ?>
                    </h3>
                    <button onclick="downloadChart('timelineChart', 'actividad')"
                            class="p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-colors text-xs" title="Descargar PNG">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
                <canvas id="timelineChart" height="160"></canvas>
            </div>
        </div>
    </div>

    <!-- Forms Tab -->
    <div x-show="tab === 'forms'">
        <?php if ($form): ?>
        <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-5 mb-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-600">Filtrando por:</p>
                    <p class="text-lg font-semibold text-gray-900"><?= $form->titulo ?></p>
                </div>
                <a href="<?= APP_URL ?>/reportes" class="text-sm text-blue-600 hover:underline">Ver todos</a>
            </div>
        </div>
        <?php endif; ?>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($formsStats as $fs): ?>
            <a href="<?= APP_URL ?>/reportes/formulario/<?= $fs->id ?>"
               class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md hover:border-blue-200 transition-all group">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 truncate"><?= $fs->titulo ?></h3>
                        <p class="text-xs text-gray-500 mt-1">
                            <span class="font-medium text-blue-600"><?= number_format($fs->total) ?></span> registros
                        </p>
                    </div>
                    <i class="fas fa-chevron-right text-gray-300 group-hover:text-blue-400 mt-1"></i>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5 mt-3">
                    <div class="bg-blue-500 h-1.5 rounded-full transition-all" style="width: <?= min(100, $stats['total_registros'] > 0 ? ($fs->total / $stats['total_registros']) * 100 : 0) ?>%"></div>
                </div>
                <?php if ($fs->ultimo): ?>
                <p class="text-xs text-gray-400 mt-2">Último: <?= date('d/m/Y', strtotime($fs->ultimo)) ?></p>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
            <?php if (empty($formsStats)): ?>
            <div class="col-span-full text-center py-12">
                <i class="fas fa-layer-group text-gray-300 text-4xl mb-3"></i>
                <p class="text-gray-500 text-sm">No hay formularios disponibles</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Operators Tab -->
    <div x-show="tab === 'operators'">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">
                    Top Operadores
                    <?php if ($form): ?><span class="text-gray-400 font-normal"> — <?= $form->titulo ?></span><?php endif; ?>
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Registros</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($topOperators as $i => $op): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3 text-sm text-gray-500"><?= $i + 1 ?></td>
                            <td class="px-6 py-3 text-sm font-medium text-gray-900"><?= $op->apellido . ' ' . $op->nombre ?></td>
                            <td class="px-6 py-3 text-sm text-gray-500"><?= $op->email ?></td>
                            <td class="px-6 py-3 text-sm text-right font-semibold text-blue-600"><?= number_format($op->total) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($topOperators)): ?>
                        <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">Sin datos de operadores</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Saved Tab -->
    <div x-show="tab === 'saved'">
        <div class="space-y-3">
            <?php if (!empty($favorites)): ?>
                <?php foreach ($favorites as $fav): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center justify-between hover:shadow-md transition-shadow">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-bookmark text-blue-500"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-900"><?= $fav->titulo ?></p>
                            <p class="text-xs text-gray-500 capitalize"><?= $fav->tipo ?> &middot; <?= date('d/m/Y', strtotime($fav->created_at)) ?></p>
                        </div>
                    </div>
                    <form action="<?= APP_URL ?>/reportes/favoritos/<?= $fav->id ?>/eliminar" method="POST" class="inline">
                        <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors"
                                onclick="event.preventDefault(); confirmSwal('Eliminar favorito', '¿Eliminar este reporte guardado?', () => this.closest('form').submit())">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-bookmark text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500 text-sm">No hay reportes guardados</p>
                    <p class="text-xs text-gray-400 mt-1">Guarda reportes desde la vista de cada formulario</p>
                </div>
            <?php endif; ?>
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
    const byForm = document.getElementById('recordsByFormChart');
    if (byForm) {
        const data = <?= json_encode($recordsByForm) ?>;
        const colors = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#06B6D4','#84CC16','#F97316','#6366F1','#14B8A6','#A855F7'];
        new Chart(byForm, {
            type: 'doughnut',
            data: {
                labels: data.map(d => d.titulo),
                datasets: [{
                    data: data.map(d => parseInt(d.total)),
                    backgroundColor: colors.slice(0, data.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12, font: { size: 10 } } }
                },
                cutout: '55%'
            }
        });
    }

    const timelineCtx = document.getElementById('timelineChart');
    if (timelineCtx) {
        const data = <?= json_encode($timelineData) ?>;
        new Chart(timelineCtx, {
            type: 'line',
            data: {
                labels: data.map(d => { const p = d.fecha.split('-'); return p[2] + '/' + p[1]; }),
                datasets: [{
                    label: 'Registros',
                    data: data.map(d => parseInt(d.total)),
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: data.length > 60 ? 1 : 3,
                    pointHoverRadius: 5,
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
                        ticks: { font: { size: 9 }, maxTicksLimit: 20 }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { size: 9 } },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    }
                },
                interaction: { intersect: false, mode: 'index' }
            }
        });
    }
});
</script>

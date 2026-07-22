<div class="space-y-6" x-data="{
    activeField: null,
    showSaveModal: false,
    favTitle: '',
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="<?= APP_URL ?>/reportes" class="text-sm text-blue-600 hover:underline">&larr; Volver a reportes</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1"><?= $form->titulo ?></h1>
        </div>
        <div class="flex space-x-2">
            <button @click="showSaveModal = true"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">
                <i class="fas fa-bookmark mr-2"></i> Guardar Reporte
            </button>
            <form action="<?= APP_URL ?>/reportes/exportar/pdf-form" method="POST" class="inline">
                <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="form_id" value="<?= $form->id ?>">
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                    <i class="fas fa-file-pdf mr-2"></i> Exportar PDF
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Registros</p>
            <p class="text-2xl font-bold text-blue-600 mt-1"><?= number_format($totalRecords) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Campos</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1"><?= count($fields) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</p>
            <p class="text-2xl font-bold text-amber-600 mt-1 capitalize"><?= $form->estado ?? 'n/a' ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Creado</p>
            <p class="text-lg font-bold text-gray-900 mt-1"><?= date('d/m/Y', strtotime($form->created_at ?? 'now')) ?></p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Desde</label>
                <input type="date" name="fecha_desde" value="<?= $fechaDesde ?? '' ?>"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" value="<?= $fechaHasta ?? '' ?>"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">
                <i class="fas fa-search mr-1"></i> Filtrar
            </button>
            <?php if ($fechaDesde || $fechaHasta): ?>
            <a href="<?= APP_URL ?>/reportes/formulario/<?= $form->id ?>"
               class="px-3 py-1.5 text-xs font-medium rounded-full bg-red-50 text-red-600 border border-red-200 hover:bg-red-100">
                Limpiar filtros
            </a>
            <?php endif; ?>
        </form>
    </div>

    <?php foreach ($fieldAnalytics as $fa): $f = $fa['field']; $d = $fa['data']; $t = $fa['type']; ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <button @click="activeField = activeField === <?= $f->id ?> ? null : <?= $f->id ?>"
                class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
            <div class="flex items-center space-x-3">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600">
                    <?php
                    $icons = [
                        'texto' => 'fa-font', 'numero' => 'fa-hashtag', 'email' => 'fa-envelope',
                        'telefono' => 'fa-phone', 'textarea' => 'fa-align-left', 'select' => 'fa-list',
                        'radio' => 'fa-dot-circle', 'checkbox' => 'fa-check-square', 'fecha' => 'fa-calendar',
                        'hora' => 'fa-clock', 'gps' => 'fa-map-marker-alt', 'imagen' => 'fa-image',
                        'archivo' => 'fa-file', 'firma' => 'fa-pen',
                    ];
                    ?>
                    <i class="fas <?= $icons[$t] ?? 'fa-cog' ?>"></i>
                </span>
                <div class="text-left">
                    <h3 class="text-sm font-semibold text-gray-900"><?= $f->etiqueta ?></h3>
                    <p class="text-xs text-gray-500 capitalize"><?= $t ?></p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <?php if (isset($d['filled'])): ?>
                <span class="text-xs text-gray-500"><?= number_format($d['filled']) ?> respuestas</span>
                <?php endif; ?>
                <i class="fas fa-chevron-down text-gray-400 transition-transform"
                   :class="activeField === <?= $f->id ?> ? 'rotate-180' : ''"></i>
            </div>
        </button>

        <div x-show="activeField === <?= $f->id ?>" x-collapse>
            <div class="px-6 pb-4 border-t border-gray-100 pt-4">
                <?php if ($t === 'numero'): ?>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                    <?php if ($d['avg'] !== null): ?>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-xs text-gray-500">Promedio</p>
                        <p class="text-lg font-bold text-blue-600"><?= $d['avg'] ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($d['sum'] !== null): ?>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-xs text-gray-500">Suma</p>
                        <p class="text-lg font-bold text-green-600"><?= $d['sum'] ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($d['min'] !== null): ?>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-xs text-gray-500">Mínimo</p>
                        <p class="text-lg font-bold text-amber-600"><?= $d['min'] ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($d['max'] !== null): ?>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-xs text-gray-500">Máximo</p>
                        <p class="text-lg font-bold text-red-600"><?= $d['max'] ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($d['distribution'])): ?>
                <canvas id="histogram-<?= $f->id ?>" height="100"></canvas>
                <?php endif; ?>

                <?php elseif ($t === 'select' || $t === 'radio'): ?>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <canvas id="pie-<?= $f->id ?>" height="160"></canvas>
                    </div>
                    <div class="space-y-2">
                        <?php foreach ($d['distribution'] ?? [] as $row): ?>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 truncate"><?= $row->valor ?: '(sin respuesta)' ?></span>
                            <div class="flex items-center space-x-2">
                                <span class="font-semibold text-gray-900"><?= $row->total ?></span>
                                <span class="text-xs text-gray-400">(<?= $d['filled'] > 0 ? round(($row->total / $d['filled']) * 100) : 0 ?>%)</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-blue-500 h-1.5 rounded-full" style="width: <?= $d['filled'] > 0 ? ($row->total / $d['filled']) * 100 : 0 ?>%"></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php elseif ($t === 'checkbox'): ?>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <canvas id="checkbox-<?= $f->id ?>" height="160"></canvas>
                    </div>
                    <div class="space-y-2">
                        <p class="text-xs text-gray-500 mb-2"><?= number_format($d['total_responses'] ?? 0) ?> respuestas</p>
                        <?php foreach (array_slice($d['frequency'] ?? [], 0, 10) as $opt => $count): ?>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 truncate"><?= $opt ?></span>
                            <span class="font-semibold text-gray-900"><?= $count ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php elseif ($t === 'fecha'): ?>
                <canvas id="fecha-<?= $f->id ?>" height="100"></canvas>

                <?php elseif ($t === 'hora'): ?>
                <canvas id="hora-<?= $f->id ?>" height="100"></canvas>

                <?php elseif ($t === 'gps'): ?>
                <div>
                    <p class="text-sm text-gray-600 mb-2"><?= number_format($d['total'] ?? 0) ?> puntos registrados</p>
                    <?php if (!empty($d['points'])): ?>
                    <div id="gps-map-<?= $f->id ?>" style="height: 300px;" class="rounded-lg border border-gray-200"></div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-3">
                        <?php foreach (array_slice($d['points'], 0, 8) as $pt): ?>
                        <div class="text-xs text-gray-500 font-mono bg-gray-50 rounded p-1.5">
                            <?= $pt['lat'] ?>, <?= $pt['lng'] ?>
                        </div>
                        <?php endforeach; ?>
                        <?php if (count($d['points']) > 8): ?>
                        <div class="text-xs text-gray-400 font-mono bg-gray-50 rounded p-1.5 text-center">
                            +<?= count($d['points']) - 8 ?> más
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <?php elseif (in_array($t, ['imagen', 'archivo', 'firma'])): ?>
                <div class="text-center py-6">
                    <i class="fas fa-image text-gray-300 text-3xl mb-2"></i>
                    <p class="text-sm text-gray-500"><?= number_format($d['filled'] ?? 0) ?> archivos cargados</p>
                    <p class="text-xs text-gray-400">Los detalles de archivos están disponibles en cada registro</p>
                </div>

                <?php else: ?>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500">Completados</p>
                        <p class="text-lg font-bold text-green-600"><?= number_format($d['filled'] ?? 0) ?></p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500">Total</p>
                        <p class="text-lg font-bold text-blue-600"><?= number_format($d['total'] ?? 0) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($fieldAnalytics)): ?>
    <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-200">
        <i class="fas fa-chart-bar text-gray-300 text-4xl mb-3"></i>
        <p class="text-gray-500">No hay datos para este formulario</p>
    </div>
    <?php endif; ?>

    <!-- Save Report Modal -->
    <div x-show="showSaveModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
         x-cloak @click.outside="showSaveModal = false">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Guardar Reporte</h3>
            <form action="<?= APP_URL ?>/reportes/favoritos/guardar" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="tipo" value="formulario">
                <input type="hidden" name="config[form_id]" value="<?= $form->id ?>">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del reporte</label>
                <input type="text" name="titulo" x-model="favTitle" required
                       placeholder="Ej: Reporte mensual <?= $form->titulo ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" @click="showSaveModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fieldAnalytics = <?= json_encode($fieldAnalytics) ?>;
    fieldAnalytics.forEach(fa => {
        const f = fa.field, d = fa.data, t = fa.type;

        if (t === 'numero' && d.distribution && d.distribution.length) {
            const ctx = document.getElementById('histogram-' + f.id);
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: d.distribution.map(r => r.valor),
                        datasets: [{
                            label: 'Frecuencia',
                            data: d.distribution.map(r => parseInt(r.total)),
                            backgroundColor: 'rgba(59, 130, 246, 0.6)',
                            borderColor: '#3B82F6',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 9 } } },
                            y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 9 } } }
                        }
                    }
                });
            }
        }

        if ((t === 'select' || t === 'radio') && d.distribution && d.distribution.length) {
            const ctx = document.getElementById('pie-' + f.id);
            if (ctx) {
                const colors = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#06B6D4','#84CC16','#F97316','#6366F1'];
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: d.distribution.map(r => r.valor || '(sin respuesta)'),
                        datasets: [{
                            data: d.distribution.map(r => parseInt(r.total)),
                            backgroundColor: colors.slice(0, d.distribution.length),
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 10 } } }
                        }
                    }
                });
            }
        }

        if (t === 'checkbox' && d.frequency && Object.keys(d.frequency).length) {
            const ctx = document.getElementById('checkbox-' + f.id);
            if (ctx) {
                const labels = Object.keys(d.frequency).slice(0, 10);
                const values = Object.values(d.frequency).slice(0, 10);
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Selecciones',
                            data: values,
                            backgroundColor: 'rgba(16, 185, 129, 0.6)',
                            borderColor: '#10B981',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 9 } } },
                            y: { grid: { display: false }, ticks: { font: { size: 9 } } }
                        }
                    }
                });
            }
        }

        if (t === 'fecha' && d.months && d.months.length) {
            const ctx = document.getElementById('fecha-' + f.id);
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: d.months.map(m => m.mes),
                        datasets: [{
                            label: 'Registros',
                            data: d.months.map(m => parseInt(m.total)),
                            backgroundColor: 'rgba(99, 102, 241, 0.6)',
                            borderColor: '#6366F1',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 9 } } },
                            y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 9 } } }
                        }
                    }
                });
            }
        }

        if (t === 'hora' && d.hours && d.hours.length) {
            const ctx = document.getElementById('hora-' + f.id);
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: d.hours.map(h => h.hora + ':00'),
                        datasets: [{
                            label: 'Registros',
                            data: d.hours.map(h => parseInt(h.total)),
                            backgroundColor: 'rgba(245, 158, 11, 0.6)',
                            borderColor: '#F59E0B',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 9 } } },
                            y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 9 } } }
                        }
                    }
                });
            }
        }

        if (t === 'gps' && d.points && d.points.length) {
            const mapEl = document.getElementById('gps-map-' + f.id);
            if (mapEl && typeof L !== 'undefined') {
                const map = L.map(mapEl).setView([d.points[0].lat, d.points[0].lng], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);
                d.points.forEach(p => {
                    L.marker([p.lat, p.lng]).addTo(map);
                });
                if (d.points.length > 1) {
                    map.fitBounds(d.points.map(p => [p.lat, p.lng]));
                }
            }
        }
    });
});

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
</script>

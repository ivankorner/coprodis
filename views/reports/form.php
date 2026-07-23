<div class="space-y-6" x-data="{
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

    <?php
    $graficableTypes = ['numero', 'moneda', 'porcentaje', 'select', 'radio', 'checkbox', 'fecha', 'hora', 'gps', 'imagen', 'archivo', 'firma'];
    $textTypes = ['texto', 'textarea', 'email', 'telefono'];

    $chartFields = array_filter($fieldAnalytics, function($fa) use ($graficableTypes) {
        return in_array($fa['type'], $graficableTypes);
    });
    $textFields = array_filter($fieldAnalytics, function($fa) use ($textTypes) {
        return in_array($fa['type'], $textTypes);
    });
    $sepFields = array_filter($fieldAnalytics, function($fa) {
        return $fa['type'] === 'separador';
    });
    ?>

    <!-- Chart Grid -->
    <?php if (!empty($chartFields)): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($chartFields as $fa): $f = $fa['field']; $d = $fa['data']; $t = $fa['type']; ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900 truncate"><?= $f->etiqueta ?></h3>
            </div>
            <div class="p-4">
                <?php if (in_array($t, ['numero', 'moneda', 'porcentaje'])): ?>
                <div class="flex flex-wrap gap-2 mb-3">
                    <?php if ($d['avg'] !== null): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">Prom: <?= $d['avg'] ?></span>
                    <?php endif; ?>
                    <?php if ($d['sum'] !== null): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700">Sum: <?= $d['sum'] ?></span>
                    <?php endif; ?>
                    <?php if ($d['min'] !== null): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-700">Min: <?= $d['min'] ?></span>
                    <?php endif; ?>
                    <?php if ($d['max'] !== null): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-700">Max: <?= $d['max'] ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($d['distribution'])): ?>
                <canvas id="histogram-<?= $f->id ?>" height="120"></canvas>
                <?php else: ?>
                <p class="text-center text-gray-400 text-sm py-4">Sin datos</p>
                <?php endif; ?>

                <?php elseif (in_array($t, ['select', 'radio'])): ?>
                <canvas id="pie-<?= $f->id ?>" height="140"></canvas>

                <?php elseif ($t === 'checkbox'): ?>
                <canvas id="checkbox-<?= $f->id ?>" height="120"></canvas>

                <?php elseif ($t === 'fecha'): ?>
                <canvas id="fecha-<?= $f->id ?>" height="100"></canvas>

                <?php elseif ($t === 'hora'): ?>
                <canvas id="hora-<?= $f->id ?>" height="100"></canvas>

                <?php elseif ($t === 'gps'): ?>
                <p class="text-xs text-gray-500 mb-2"><?= number_format($d['total'] ?? 0) ?> puntos</p>
                <?php if (!empty($d['points'])): ?>
                <div id="gps-map-<?= $f->id ?>" style="height: 180px;" class="rounded-lg border border-gray-200"></div>
                <?php else: ?>
                <p class="text-center text-gray-400 text-sm py-4">Sin datos</p>
                <?php endif; ?>

                <?php elseif (in_array($t, ['imagen', 'archivo', 'firma'])): ?>
                <div class="text-center py-6">
                    <i class="fas fa-<?= $t === 'imagen' ? 'image' : ($t === 'archivo' ? 'file' : 'pen') ?> text-gray-300 text-2xl mb-1"></i>
                    <p class="text-sm font-semibold text-gray-600"><?= number_format($d['filled'] ?? 0) ?></p>
                    <p class="text-xs text-gray-400">archivos cargados</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-200">
        <i class="fas fa-chart-bar text-gray-300 text-4xl mb-3"></i>
        <p class="text-gray-500">No hay datos para este formulario</p>
    </div>
    <?php endif; ?>

    <!-- Text Fields List -->
    <?php if (!empty($textFields) || !empty($sepFields)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-3 border-b border-gray-100 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-700">Datos registrados</h3>
        </div>
        <div class="divide-y divide-gray-100">
            <?php foreach ($textFields as $fa): $f = $fa['field']; $d = $fa['data']; ?>
            <div class="px-6 py-3 flex items-center justify-between">
                <span class="text-sm text-gray-700"><?= $f->etiqueta ?></span>
                <span class="text-sm font-medium text-gray-900"><?= number_format($d['filled'] ?? 0) ?> respuestas</span>
            </div>
            <?php endforeach; ?>
            <?php foreach ($sepFields as $fa): $f = $fa['field']; ?>
            <div class="px-6 py-2">
                <span class="text-xs text-gray-400 italic">── Separador: <?= $f->etiqueta ?> ──</span>
            </div>
            <?php endforeach; ?>
        </div>
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

        if ((t === 'numero' || t === 'moneda' || t === 'porcentaje') && d.distribution && d.distribution.length) {
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

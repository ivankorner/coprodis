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
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4" x-data="{ showAdvanced: <?= !empty($fieldFilters) ? 'true' : 'false' ?> }">
        <form method="GET">
            <div class="flex flex-wrap items-end gap-3">
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
                <?php if ($fechaDesde || $fechaHasta || !empty($fieldFilters)): ?>
                <a href="<?= APP_URL ?>/reportes/formulario/<?= $form->id ?>"
                   class="px-3 py-1.5 text-xs font-medium rounded-full bg-red-50 text-red-600 border border-red-200 hover:bg-red-100">
                    Limpiar filtros
                </a>
                <?php endif; ?>
            </div>

            <?php if (!empty($filterableFields)): ?>
            <div class="mt-3 pt-3 border-t border-gray-100">
                <button type="button" @click="showAdvanced = !showAdvanced"
                        class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                    <i class="fas" :class="showAdvanced ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                    Filtros avanzados
                    <?php if (!empty($fieldFilters)): ?>
                    <span class="inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-blue-500 rounded-full">
                        <?= count($fieldFilters) ?>
                    </span>
                    <?php endif; ?>
                </button>

                <div x-show="showAdvanced" x-cloak>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mt-3">
                        <?php foreach ($filterableFields as $ff): $fid = $ff->id; ?>
                            <?php if (in_array($ff->tipo, ['select', 'radio', 'checkbox']) && !empty($fieldOptions[$fid])): ?>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1"><?= htmlspecialchars($ff->etiqueta) ?></label>
                                <select name="f_<?= $fid ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                                    <option value="">Todos</option>
                                    <?php foreach ($fieldOptions[$fid] as $opt): ?>
                                    <option value="<?= htmlspecialchars($opt) ?>" <?= ($fieldFilters[$fid]['value'] ?? '') === $opt ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($opt) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php elseif (in_array($ff->tipo, ['numero', 'moneda', 'porcentaje'])): ?>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1"><?= htmlspecialchars($ff->etiqueta) ?></label>
                                <div class="flex items-center gap-1">
                                    <input type="number" step="any" name="f_<?= $fid ?>_min" placeholder="Min"
                                           value="<?= htmlspecialchars($fieldFilters[$fid]['min'] ?? '') ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    <span class="text-gray-400 shrink-0">—</span>
                                    <input type="number" step="any" name="f_<?= $fid ?>_max" placeholder="Max"
                                           value="<?= htmlspecialchars($fieldFilters[$fid]['max'] ?? '') ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!empty($fieldFilters)): ?>
                    <div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-gray-100">
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wider self-center">Filtros activos:</span>
                        <?php foreach ($fieldFilters as $fid => $filter): ?>
                            <?php
                            $ffLabel = '';
                            foreach ($filterableFields as $ff) {
                                if ($ff->id == $fid) { $ffLabel = $ff->etiqueta; break; }
                            }
                            ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-blue-50 text-blue-700 rounded-full">
                                <?= htmlspecialchars($ffLabel) ?>:
                                <?php if ($filter['type'] === 'eq'): ?>
                                    <?= htmlspecialchars($filter['value']) ?>
                                <?php elseif ($filter['type'] === 'range'): ?>
                                    <?= ($filter['min'] !== '' ? $filter['min'] : '0') ?> - <?= ($filter['max'] !== '' ? $filter['max'] : '∞') ?>
                                <?php endif; ?>
                                <a href="?<?= http_build_query(array_filter(array_merge($_GET, ['f_' . $fid => null, 'f_' . $fid . '_min' => null, 'f_' . $fid . '_max' => null]))) ?>"
                                   class="text-blue-400 hover:text-blue-700 ml-0.5">&times;</a>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
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
                <?php if ($d['avg'] !== null || $d['sum'] !== null || $d['min'] !== null || $d['max'] !== null): ?>
                <div class="flex flex-wrap gap-2 mb-3">
                    <?php if ($d['avg'] !== null): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">Prom: <?= $d['avg'] ?></span>
                    <?php endif; ?>
                    <?php if ($d['sum'] !== null): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700">Sum: <?= number_format($d['sum'], 0, ',', '.') ?></span>
                    <?php endif; ?>
                    <?php if ($d['min'] !== null): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-700">Min: <?= $d['min'] ?></span>
                    <?php endif; ?>
                    <?php if ($d['max'] !== null): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-700">Max: <?= $d['max'] ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($d['distribution'])): ?>
                <div style="height: 140px;"><canvas id="histogram-<?= $f->id ?>"></canvas></div>
                <?php else: ?>
                <p class="text-center text-gray-400 text-sm py-4">Sin datos</p>
                <?php endif; ?>

                <?php elseif (in_array($t, ['select', 'radio'])): ?>
                <?php if (!empty($d['distribution'])): ?>
                <div style="height: 180px;"><canvas id="pie-<?= $f->id ?>"></canvas></div>
                <?php else: ?>
                <p class="text-center text-gray-400 text-sm py-4">Sin datos</p>
                <?php endif; ?>

                <?php elseif ($t === 'checkbox'): ?>
                <?php if (!empty($d['frequency'])): ?>
                <div style="height: <?= min(count($d['frequency']) * 28 + 20, 300) ?>px;"><canvas id="checkbox-<?= $f->id ?>"></canvas></div>
                <?php else: ?>
                <p class="text-center text-gray-400 text-sm py-4">Sin datos</p>
                <?php endif; ?>

                <?php elseif ($t === 'fecha'): ?>
                <?php if (!empty($d['months'])): ?>
                <div style="height: 140px;"><canvas id="fecha-<?= $f->id ?>"></canvas></div>
                <?php else: ?>
                <p class="text-center text-gray-400 text-sm py-4">Sin datos</p>
                <?php endif; ?>

                <?php elseif ($t === 'hora'): ?>
                <?php if (!empty($d['hours'])): ?>
                <div style="height: 140px;"><canvas id="hora-<?= $f->id ?>"></canvas></div>
                <?php else: ?>
                <p class="text-center text-gray-400 text-sm py-4">Sin datos</p>
                <?php endif; ?>

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
    <?php if (!empty($textFields)): ?>
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
    const fieldAnalytics = <?= json_encode($fieldAnalytics, JSON_UNESCAPED_UNICODE) ?>;

    function createChart(fn) {
        try { fn(); } catch(e) { console.warn('Chart error:', e); }
    }

    fieldAnalytics.forEach(function(fa) {
        var f = fa.field, d = fa.data, t = fa.type;

        if ((t === 'numero' || t === 'moneda' || t === 'porcentaje') && d.distribution && d.distribution.length) {
            createChart(function() {
                var ctx = document.getElementById('histogram-' + f.id);
                if (!ctx) return;
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: d.distribution.map(function(r) { return r.valor; }),
                        datasets: [{
                            label: 'Frecuencia',
                            data: d.distribution.map(function(r) { return parseInt(r.total) || 0; }),
                            backgroundColor: 'rgba(59, 130, 246, 0.6)',
                            borderColor: '#3B82F6',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 9 }, maxRotation: 45 } },
                            y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 9 } } }
                        }
                    }
                });
            });
        }

        if ((t === 'select' || t === 'radio') && d.distribution && d.distribution.length) {
            createChart(function() {
                var ctx = document.getElementById('pie-' + f.id);
                if (!ctx) return;
                var colors = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#06B6D4','#84CC16','#F97316','#6366F1'];
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: d.distribution.map(function(r) { return r.valor || '(sin respuesta)'; }),
                        datasets: [{
                            data: d.distribution.map(function(r) { return parseInt(r.total) || 0; }),
                            backgroundColor: colors.slice(0, d.distribution.length),
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 10 } } }
                        }
                    }
                });
            });
        } else if (t === 'select' || t === 'radio') {
            var el = document.getElementById('pie-' + f.id);
            if (el) { el.parentElement.innerHTML = '<p class="text-center text-gray-400 text-sm py-4">Sin datos</p>'; }
        }

        if (t === 'checkbox' && d.frequency && Object.keys(d.frequency).length) {
            createChart(function() {
                var ctx = document.getElementById('checkbox-' + f.id);
                if (!ctx) return;
                var labels = Object.keys(d.frequency).slice(0, 10);
                var values = Object.values(d.frequency).slice(0, 10);
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
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 9 } } },
                            y: { grid: { display: false }, ticks: { font: { size: 9 } } }
                        }
                    }
                });
            });
        } else if (t === 'checkbox') {
            var el = document.getElementById('checkbox-' + f.id);
            if (el) { el.parentElement.innerHTML = '<p class="text-center text-gray-400 text-sm py-4">Sin datos</p>'; }
        }

        if (t === 'fecha' && d.months && d.months.length) {
            createChart(function() {
                var ctx = document.getElementById('fecha-' + f.id);
                if (!ctx) return;
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: d.months.map(function(m) { return m.mes; }),
                        datasets: [{
                            label: 'Registros',
                            data: d.months.map(function(m) { return parseInt(m.total) || 0; }),
                            backgroundColor: 'rgba(99, 102, 241, 0.6)',
                            borderColor: '#6366F1',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 9 } } },
                            y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 9 } } }
                        }
                    }
                });
            });
        } else if (t === 'fecha') {
            var el = document.getElementById('fecha-' + f.id);
            if (el) { el.parentElement.innerHTML = '<p class="text-center text-gray-400 text-sm py-4">Sin datos</p>'; }
        }

        if (t === 'hora' && d.hours && d.hours.length) {
            createChart(function() {
                var ctx = document.getElementById('hora-' + f.id);
                if (!ctx) return;
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: d.hours.map(function(h) { return h.hora + ':00'; }),
                        datasets: [{
                            label: 'Registros',
                            data: d.hours.map(function(h) { return parseInt(h.total) || 0; }),
                            backgroundColor: 'rgba(245, 158, 11, 0.6)',
                            borderColor: '#F59E0B',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 9 } } },
                            y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 9 } } }
                        }
                    }
                });
            });
        } else if (t === 'hora') {
            var el = document.getElementById('hora-' + f.id);
            if (el) { el.parentElement.innerHTML = '<p class="text-center text-gray-400 text-sm py-4">Sin datos</p>'; }
        }

        if (t === 'gps' && d.points && d.points.length) {
            createChart(function() {
                var mapEl = document.getElementById('gps-map-' + f.id);
                if (!mapEl || typeof L === 'undefined') return;
                var map = L.map(mapEl).setView([d.points[0].lat, d.points[0].lng], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);
                d.points.forEach(function(p) {
                    L.marker([p.lat, p.lng]).addTo(map);
                });
                if (d.points.length > 1) {
                    map.fitBounds(d.points.map(function(p) { return [p.lat, p.lng]; }));
                }
            });
        }
    });
});

function downloadChart(canvasId, filename) {
    var canvas = document.getElementById(canvasId);
    if (!canvas) return;
    var link = document.createElement('a');
    link.download = filename + '.png';
    link.href = canvas.toDataURL('image/png');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

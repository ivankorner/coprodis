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

    <?php foreach ($groupedAnalytics as $gKey => $group): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 flex items-center justify-between bg-gray-50 border-b border-gray-200">
            <div class="flex items-center space-x-3">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-<?= $group['color'] ?>-50 text-<?= $group['color'] ?>-600">
                    <i class="fas <?= $group['icon'] ?>"></i>
                </span>
                <h2 class="text-base font-semibold text-gray-900"><?= $group['label'] ?></h2>
                <span class="text-xs text-gray-400">(<?= count($group['fields']) ?> campos)</span>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <?php if ($gKey === 'numericos'): ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <canvas id="chart-numericos" height="180"></canvas>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 uppercase">
                                <th class="pb-2 pr-3">Campo</th>
                                <th class="pb-2 pr-3 text-right">Promedio</th>
                                <th class="pb-2 pr-3 text-right">Suma</th>
                                <th class="pb-2 pr-3 text-right">Mín</th>
                                <th class="pb-2 text-right">Máx</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($group['fields'] as $fa): $f = $fa['field']; $d = $fa['data']; ?>
                            <tr class="hover:bg-gray-50 cursor-pointer" @click="activeField = activeField === 'num-<?= $f->id ?>' ? null : 'num-<?= $f->id ?>'">
                                <td class="py-2 pr-3 font-medium text-gray-900"><?= $f->etiqueta ?></td>
                                <td class="py-2 pr-3 text-right font-mono text-blue-600"><?= $d['avg'] ?? '-' ?></td>
                                <td class="py-2 pr-3 text-right font-mono"><?= $d['sum'] ?? '-' ?></td>
                                <td class="py-2 pr-3 text-right font-mono text-amber-600"><?= $d['min'] ?? '-' ?></td>
                                <td class="py-2 text-right font-mono text-red-600"><?= $d['max'] ?? '-' ?></td>
                            </tr>
                            <tr x-show="activeField === 'num-<?= $f->id ?>'" x-collapse>
                                <td colspan="5" class="py-3 pl-6">
                                    <?php if (!empty($d['distribution'])): ?>
                                    <canvas id="histogram-<?= $f->id ?>" height="80"></canvas>
                                    <?php else: ?>
                                    <p class="text-xs text-gray-400">Sin datos de distribución</p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php elseif ($gKey === 'selecciones'): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($group['fields'] as $fa): $f = $fa['field']; $d = $fa['data']; ?>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <h4 class="text-sm font-semibold text-gray-900 mb-1 truncate"><?= $f->etiqueta ?></h4>
                    <p class="text-xs text-gray-400 mb-3"><?= number_format($d['filled'] ?? 0) ?> respuestas</p>
                    <canvas id="pie-<?= $f->id ?>" height="120"></canvas>
                </div>
                <?php endforeach; ?>
            </div>

            <?php elseif ($gKey === 'checkboxes'): ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <?php foreach ($group['fields'] as $fa): $f = $fa['field']; $d = $fa['data']; ?>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <h4 class="text-sm font-semibold text-gray-900 mb-1 truncate"><?= $f->etiqueta ?></h4>
                    <p class="text-xs text-gray-400 mb-3"><?= number_format($d['total_responses'] ?? 0) ?> respuestas</p>
                    <canvas id="checkbox-<?= $f->id ?>" height="100"></canvas>
                </div>
                <?php endforeach; ?>
            </div>

            <?php elseif ($gKey === 'fechas'): ?>
            <div>
                <canvas id="chart-fechas" height="180"></canvas>
            </div>

            <?php elseif ($gKey === 'horas'): ?>
            <div>
                <canvas id="chart-horas" height="180"></canvas>
            </div>

            <?php elseif ($gKey === 'gps'): ?>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <?php foreach ($group['fields'] as $fa): $f = $fa['field']; $d = $fa['data']; ?>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 text-center">
                    <p class="text-xs text-gray-500 truncate"><?= $f->etiqueta ?></p>
                    <p class="text-2xl font-bold text-red-600 mt-1"><?= number_format($d['total'] ?? 0) ?></p>
                    <p class="text-xs text-gray-400">puntos</p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($group['fields']) <= 2): ?>
                <?php foreach ($group['fields'] as $fa): $f = $fa['field']; $d = $fa['data']; ?>
                    <?php if (!empty($d['points'])): ?>
                    <div id="gps-map-<?= $f->id ?>" style="height: 250px;" class="rounded-lg border border-gray-200"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; if (empty($groupedAnalytics)): ?>
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
    const groupedAnalytics = <?= json_encode($groupedAnalytics) ?>;

    // Numeric fields — combined bar chart
    if (groupedAnalytics.numericos && groupedAnalytics.numericos.fields.length) {
        const fields = groupedAnalytics.numericos.fields;
        const ctx = document.getElementById('chart-numericos');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: fields.map(f => f.field.etiqueta.length > 20 ? f.field.etiqueta.substring(0, 18) + '...' : f.field.etiqueta),
                    datasets: [{
                        label: 'Promedio',
                        data: fields.map(f => f.data.avg !== null ? parseFloat(f.data.avg) : 0),
                        backgroundColor: fields.map(f => {
                            const t = f.type;
                            return t === 'moneda' ? 'rgba(16, 185, 129, 0.7)' :
                                   t === 'porcentaje' ? 'rgba(245, 158, 11, 0.7)' :
                                   'rgba(59, 130, 246, 0.7)';
                        }),
                        borderColor: fields.map(f => {
                            const t = f.type;
                            return t === 'moneda' ? '#10B981' :
                                   t === 'porcentaje' ? '#F59E0B' :
                                   '#3B82F6';
                        }),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                        y: { beginAtZero: true, ticks: { font: { size: 10 } } }
                    }
                }
            });
        }
    }

    // Selection fields — individual doughnut charts
    fieldAnalytics.forEach(fa => {
        const f = fa.field, d = fa.data, t = fa.type;
        if ((t === 'select' || t === 'radio') && d.distribution && d.distribution.length) {
            const ctx = document.getElementById('pie-' + f.id);
            if (ctx) {
                const colors = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#06B6D4','#84CC16','#F97316','#6366F1'];
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: d.distribution.map(r => r.valor || '(sin)'),
                        datasets: [{
                            data: d.distribution.map(r => parseInt(r.total)),
                            backgroundColor: colors.slice(0, d.distribution.length),
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        cutout: '65%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                        const pct = total > 0 ? Math.round(ctx.parsed / total * 100) : 0;
                                        return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
    });

    // Checkbox fields — individual horizontal bar charts
    fieldAnalytics.forEach(fa => {
        const f = fa.field, d = fa.data, t = fa.type;
        if (t === 'checkbox' && d.frequency && Object.keys(d.frequency).length) {
            const ctx = document.getElementById('checkbox-' + f.id);
            if (ctx) {
                const labels = Object.keys(d.frequency).slice(0, 8);
                const values = Object.values(d.frequency).slice(0, 8);
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
                        maintainAspectRatio: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 9 } } },
                            y: { grid: { display: false }, ticks: { font: { size: 9 } } }
                        }
                    }
                });
            }
        }
    });

    // Date fields — combined line chart
    const dateFields = (groupedAnalytics.fechas?.fields || []).filter(f => f.data.months?.length);
    if (dateFields.length) {
        const ctx = document.getElementById('chart-fechas');
        if (ctx) {
            const allMonths = [...new Set(dateFields.flatMap(f => f.data.months.map(m => m.mes)))].sort();
            const colors = ['#6366F1', '#EC4899', '#06B6D4', '#F59E0B', '#84CC16', '#F97316'];
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: allMonths,
                    datasets: dateFields.map((f, i) => {
                        const monthMap = {};
                        f.data.months.forEach(m => { monthMap[m.mes] = parseInt(m.total); });
                        return {
                            label: f.field.etiqueta,
                            data: allMonths.map(m => monthMap[m] || 0),
                            borderColor: colors[i % colors.length],
                            backgroundColor: colors[i % colors.length] + '20',
                            fill: false,
                            tension: 0.3,
                            pointRadius: 3
                        };
                    })
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 10 } } }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 9 } } },
                        y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 9 } } }
                    }
                }
            });
        }
    }

    // Hour fields — combined grouped bar chart
    const hourFields = (groupedAnalytics.horas?.fields || []).filter(f => f.data.hours?.length);
    if (hourFields.length) {
        const ctx = document.getElementById('chart-horas');
        if (ctx) {
            const allHours = Array.from({length: 24}, (_, i) => i);
            const colors = ['#F59E0B', '#8B5CF6', '#EF4444', '#06B6D4', '#84CC16', '#F97316'];
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: allHours.map(h => h + ':00'),
                    datasets: hourFields.map((f, i) => {
                        const hourMap = {};
                        f.data.hours.forEach(h => { hourMap[parseInt(h.hora)] = parseInt(h.total); });
                        return {
                            label: f.field.etiqueta,
                            data: allHours.map(h => hourMap[h] || 0),
                            backgroundColor: colors[i % colors.length] + '80',
                            borderColor: colors[i % colors.length],
                            borderWidth: 1
                        };
                    })
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 10 } } }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 8 } } },
                        y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 9 } } }
                    }
                }
            });
        }
    }

    // GPS fields — individual maps
    fieldAnalytics.forEach(fa => {
        const f = fa.field, d = fa.data, t = fa.type;
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

    // Numeric field individual histograms (expandable)
    fieldAnalytics.forEach(fa => {
        const f = fa.field, d = fa.data, t = fa.type;
        if (['numero', 'moneda', 'porcentaje'].includes(t) && d.distribution && d.distribution.length) {
            const ctx = document.getElementById('histogram-' + f.id);
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: d.distribution.map(r => r.valor),
                        datasets: [{
                            label: 'Frecuencia',
                            data: d.distribution.map(r => parseInt(r.total)),
                            backgroundColor: 'rgba(59, 130, 246, 0.5)',
                            borderColor: '#3B82F6',
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
            }
        }
    });
});
</script>

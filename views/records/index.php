<style>
@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-row {
    animation: fadeSlideIn 0.15s ease-out both;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .5; }
}
.skeleton-pulse {
    animation: pulse 1.5s ease-in-out infinite;
}
[x-cloak] { display: none !important; }
</style>

<div class="space-y-5"
     x-data="searchComponent()"
     @keydown.window.ctrl.k.prevent="if (formLoaded || showAllMode) $refs.searchInput.focus()"
     @keydown.window.meta.k.prevent="if (formLoaded || showAllMode) $refs.searchInput.focus()">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900">Registros</h1>
        <div class="flex space-x-2">
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors">
                    <i class="fas fa-plus mr-2"></i> Nuevo Registro
                </button>
                <div x-show="open" @click.outside="open = false"
                     class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                    <div class="p-3 border-b border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Seleccionar formulario</p>
                    </div>
                    <div class="max-h-60 overflow-y-auto">
                        <?php
                        $db = \App\Core\Database::getInstance();
                        $formsList = $db->fetchAll("SELECT id, titulo FROM forms WHERE deleted_at IS NULL AND estado = 'publicado' ORDER BY titulo");
                        ?>
                        <?php foreach ($formsList as $f): ?>
                            <a href="<?= APP_URL ?>/registros/crear/<?= $f->id ?>"
                               class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <?= $f->titulo ?>
                            </a>
                        <?php endforeach; ?>
                        <?php if (empty($formsList)): ?>
                            <p class="px-4 py-3 text-sm text-gray-500">No hay formularios disponibles</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <button @click="toggleShowAll()"
                    :class="showAllMode ? 'bg-green-600 text-white hover:bg-green-700 ring-2 ring-green-300' : 'border border-gray-300 text-gray-700 hover:bg-gray-50'"
                    class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i class="fas" :class="showAllMode ? 'fa-eye-slash mr-2' : 'fa-eye mr-2'"></i>
                <span x-text="showAllMode ? 'Ocultar todos' : 'Mostrar Registros'"></span>
            </button>

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium transition-colors">
                    <i class="fas fa-download mr-2"></i> Exportar
                </button>
                <div x-show="open" @click.outside="open = false"
                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                    <form action="<?= APP_URL ?>/exportar/registros/excel" method="POST" class="inline">
                        <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
                        <button type="submit" class="w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors text-left">
                            <i class="fas fa-file-excel mr-2 text-green-600"></i> Excel
                        </button>
                    </form>
                    <form action="<?= APP_URL ?>/exportar/registros/csv" method="POST" class="inline">
                        <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
                        <button type="submit" class="w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors text-left">
                            <i class="fas fa-file-csv mr-2 text-blue-600"></i> CSV
                        </button>
                    </form>
                    <form action="<?= APP_URL ?>/exportar/registros/pdf" method="POST" class="inline">
                        <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
                        <button type="submit" class="w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors text-left">
                            <i class="fas fa-file-pdf mr-2 text-red-600"></i> PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div>

        <div x-show="!formId && !showAllMode" class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-200 p-8 text-center">
            <div class="flex flex-col items-center gap-3">
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-search text-xl text-blue-500"></i>
                </div>
                <h2 class="text-lg font-semibold text-gray-800">Búsqueda de registros</h2>
                <p class="text-sm text-gray-500 max-w-md">Seleccioná un formulario para comenzar. Podrás elegir en qué campos buscar y filtrar los resultados.</p>
            </div>
        </div>

        <div x-show="showAllMode" class="bg-green-50 border border-green-200 rounded-xl p-4 sm:p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center shrink-0">
                    <i class="fas fa-eye text-green-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-green-800">Mostrando todos los registros</h3>
                    <p class="text-sm text-green-600">Se están mostrando registros de todos los formularios. Usá los filtros para acotar la búsqueda.</p>
                </div>
            </div>
        </div>

        <div x-show="!showAllMode" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-5">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">1. Seleccioná un formulario</label>
            <select x-model="formId" @change="onFormChange"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm bg-white focus:border-blue-500 focus:ring-0 outline-none transition-colors text-gray-700 font-medium">
                <option value="">— Elegí un formulario —</option>
                <?php foreach ($forms as $f): ?>
                    <option value="<?= $f->id ?>"><?= $f->titulo ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <template x-if="formLoaded || showAllMode">
            <div class="space-y-4">
                <div x-show="!showAllMode" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-5">
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">2. Campos de búsqueda</label>
                        <span class="text-xs text-gray-400" x-text="selectedFieldIds.length + ' seleccionados'"></span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="field in commonFields" :key="field.id">
                            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border cursor-pointer select-none transition-all"
                                   :class="selectedFieldIds.includes(field.id) ? 'bg-blue-50 border-blue-300 shadow-sm' : 'bg-white border-gray-200 hover:border-gray-300'">
                                <input type="checkbox" :value="field.id"
                                       :checked="selectedFieldIds.includes(field.id)"
                                       @change="toggleField(field.id)"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm font-medium" :class="selectedFieldIds.includes(field.id) ? 'text-blue-700' : 'text-gray-600'" x-text="field.etiqueta"></span>
                                <span class="text-[10px] text-gray-400 uppercase px-1 py-0.5 bg-gray-100 rounded" x-text="field.tipo"></span>
                            </label>
                        </template>
                    </div>
                    <div x-show="otherFields.length" class="mt-3">
                        <button @click="showAllFields = !showAllFields"
                                class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 transition-colors font-medium">
                            <i class="fas" :class="showAllFields ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                            <span x-text="showAllFields ? 'Ocultar campos extra' : 'Mostrar todos los campos (' + otherFields.length + ')'"></span>
                        </button>
                    </div>
                    <div x-show="showAllFields && otherFields.length" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-gray-100">
                        <template x-for="field in otherFields" :key="field.id">
                            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border cursor-pointer select-none transition-all"
                                   :class="selectedFieldIds.includes(field.id) ? 'bg-blue-50 border-blue-300 shadow-sm' : 'bg-white border-gray-200 hover:border-gray-300'">
                                <input type="checkbox" :value="field.id"
                                       :checked="selectedFieldIds.includes(field.id)"
                                       @change="toggleField(field.id)"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm" :class="selectedFieldIds.includes(field.id) ? 'text-blue-700 font-medium' : 'text-gray-600'" x-text="field.etiqueta"></span>
                                <span class="text-[10px] text-gray-400 uppercase px-1 py-0.5 bg-gray-100 rounded" x-text="field.tipo"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 sm:p-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input x-ref="searchInput" x-model="q" @input.debounce.300ms="doSearch"
                               type="text" :placeholder="showAllMode ? 'Buscar en todos los registros...' : 'Buscar en los campos seleccionados...'"
                               class="w-full pl-10 pr-28 py-2.5 border-2 border-gray-200 rounded-lg text-sm placeholder-gray-400
                                      focus:border-blue-500 focus:ring-0 transition-colors outline-none">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 gap-1.5">
                            <button x-show="q" @click="q = ''; doSearch()"
                                    class="p-0.5 text-gray-400 hover:text-gray-600 rounded transition-colors"
                                    title="Limpiar búsqueda">
                                <i class="fas fa-times"></i>
                            </button>
                            <kbd class="hidden sm:inline-flex items-center px-1.5 py-0.5 text-[11px] text-gray-400 bg-gray-100 rounded border border-gray-200 font-sans leading-none">
                                <span class="text-[10px] mr-0.5">⌘</span>K
                            </kbd>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <button @click="filtersOpen = !filtersOpen"
                            class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="fas fa-sliders-h text-xs" :class="filtersOpen ? 'text-blue-600' : ''"></i>
                        Filtros avanzados
                        <span x-show="hasExtraFilters"
                              class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[11px] font-bold text-white bg-blue-600 rounded-full leading-none"
                              x-text="extraFilterCount"></span>
                    </button>
                    <button x-show="hasExtraFilters" @click="clearExtraFilters"
                            class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                        <i class="fas fa-undo mr-1"></i> Limpiar filtros
                    </button>
                </div>

                <div x-show="filtersOpen" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Estado</label>
                            <select x-model="estado" @change="doSearch"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:border-blue-500 focus:ring-0 outline-none transition-colors">
                                <option value="">Todos los estados</option>
                                <option value="activo">Activo</option>
                                <option value="archivado">Archivado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Desde</label>
                            <input x-model="fechaDesde" @change="doSearch" type="date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:border-blue-500 focus:ring-0 outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Hasta</label>
                            <input x-model="fechaHasta" @change="doSearch" type="date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:border-blue-500 focus:ring-0 outline-none transition-colors">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div x-show="!loading && !initialLoad" class="flex items-center gap-1.5 text-sm text-gray-500">
                        <span class="font-medium text-gray-700" x-text="total"></span>
                        <span>registro<span x-show="total !== 1">s</span> encontrado<span x-show="total !== 1">s</span></span>
                        <span x-show="totalPages > 1" class="text-gray-300 mx-1">·</span>
                        <span x-show="totalPages > 1" class="text-gray-400" x-text="'Pág. ' + page + ' de ' + totalPages"></span>
                    </div>
                    <div x-show="!loading && !initialLoad && total > 0" class="flex items-center gap-2 text-sm text-gray-500">
                        <span>Mostrar</span>
                        <select x-model="perPage" @change="page = 1; fetchRecords()"
                                class="px-2 py-1 border border-gray-300 rounded-lg text-sm bg-white outline-none focus:border-blue-500 transition-colors">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Formulario</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Persona</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Creado</th>
                                    <th class="px-4 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="records-tbody" class="divide-y divide-gray-200"></tbody>
                            <tbody id="records-skeleton" x-show="loading" x-cloak class="divide-y divide-gray-200 skeleton-pulse">
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                <tr>
                                    <td class="px-4 sm:px-6 py-4"><div class="h-4 bg-gray-200 rounded w-10"></div></td>
                                    <td class="px-4 sm:px-6 py-4"><div class="h-4 bg-gray-200 rounded w-48"></div></td>
                                    <td class="px-4 sm:px-6 py-4 hidden md:table-cell"><div class="h-4 bg-gray-200 rounded w-36"></div></td>
                                    <td class="px-4 sm:px-6 py-4"><div class="h-5 bg-gray-200 rounded-full w-16"></div></td>
                                    <td class="px-4 sm:px-6 py-4 hidden lg:table-cell"><div class="h-4 bg-gray-200 rounded w-28"></div></td>
                                    <td class="px-4 sm:px-6 py-4"><div class="h-4 bg-gray-200 rounded w-24 ml-auto"></div></td>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                            <tbody id="records-empty" x-show="!loading && records.length === 0 && !initialLoad" x-cloak class="divide-y divide-gray-200">
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="fas fa-inbox text-3xl text-gray-300"></i>
                                            <p class="text-sm text-gray-500">No se encontraron registros</p>
                                            <p class="text-xs text-gray-400">Probá con otros términos o cambiá los filtros</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div x-show="totalPages > 0" x-cloak class="px-4 sm:px-6 py-3 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="text-sm text-gray-500" x-text="recordRange"></div>
                            <nav x-show="totalPages > 1" class="flex items-center gap-1">
                                <button @click="goToPage(1)" :disabled="page <= 1"
                                        class="px-2.5 py-1.5 text-sm border border-gray-300 rounded-lg transition-colors"
                                        :class="page <= 1 ? 'opacity-40 cursor-not-allowed text-gray-400' : 'hover:bg-gray-50 text-gray-600'">
                                    <i class="fas fa-angle-double-left"></i>
                                </button>
                                <button @click="goToPage(page - 1)" :disabled="page <= 1"
                                        class="px-2.5 py-1.5 text-sm border border-gray-300 rounded-lg transition-colors"
                                        :class="page <= 1 ? 'opacity-40 cursor-not-allowed text-gray-400' : 'hover:bg-gray-50 text-gray-600'">
                                    <i class="fas fa-angle-left"></i>
                                </button>

                                <template x-for="p in visiblePages" :key="p">
                                    <button @click="goToPage(p)"
                                            class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg font-medium transition-colors"
                                            :class="p === page ? 'bg-blue-600 text-white border-blue-600 cursor-default' : 'hover:bg-gray-50 text-gray-600'"
                                            x-text="p">
                                    </button>
                                </template>

                                <button @click="goToPage(page + 1)" :disabled="page >= totalPages"
                                        class="px-2.5 py-1.5 text-sm border border-gray-300 rounded-lg transition-colors"
                                        :class="page >= totalPages ? 'opacity-40 cursor-not-allowed text-gray-400' : 'hover:bg-gray-50 text-gray-600'">
                                    <i class="fas fa-angle-right"></i>
                                </button>
                                <button @click="goToPage(totalPages)" :disabled="page >= totalPages"
                                        class="px-2.5 py-1.5 text-sm border border-gray-300 rounded-lg transition-colors"
                                        :class="page >= totalPages ? 'opacity-40 cursor-not-allowed text-gray-400' : 'hover:bg-gray-50 text-gray-600'">
                                    <i class="fas fa-angle-double-right"></i>
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
function searchComponent() {
    return {
        formId: '<?= addslashes($filtroForm ?? '') ?>',
        formLoaded: false,
        fields: [],
        selectedFieldIds: [],
        showAllFields: false,
        q: '<?= addslashes($search ?? '') ?>',
        estado: '<?= addslashes($filtroEstado ?? '') ?>',
        fechaDesde: '<?= addslashes($filtroFechaDesde ?? '') ?>',
        fechaHasta: '<?= addslashes($filtroFechaHasta ?? '') ?>',
        page: 1,
        perPage: <?= (int)($perPage ?? PAGINATION_LIMIT) ?>,
        records: [],
        total: 0,
        totalPages: 0,
        loading: false,
        initialLoad: true,
        filtersOpen: false,
        showAllMode: false,

        init() {
            if (this.formId) {
                this.loadFormFields();
            }
        },

        get commonFields() {
            return this.fields.filter(f => f.es_comun);
        },

        get otherFields() {
            return this.fields.filter(f => !f.es_comun);
        },

        get hasExtraFilters() {
            return !!this.estado || !!this.fechaDesde || !!this.fechaHasta;
        },

        get extraFilterCount() {
            let n = 0;
            if (this.estado) n++;
            if (this.fechaDesde) n++;
            if (this.fechaHasta) n++;
            return n;
        },

        get recordRange() {
            if (this.total === 0 || this.initialLoad) return '';
            const start = (this.page - 1) * this.perPage + 1;
            const end = Math.min(this.page * this.perPage, this.total);
            return 'Mostrando ' + start + '-' + end + ' de ' + this.total + ' registro' + (this.total !== 1 ? 's' : '');
        },

        onFormChange() {
            this.fields = [];
            this.selectedFieldIds = [];
            this.formLoaded = false;
            this.q = '';
            this.records = [];
            this.total = 0;
            this.totalPages = 0;
            this.page = 1;
            this.initialLoad = true;
            this.showAllFields = false;
            if (this.formId) {
                this.loadFormFields();
            }
        },

        loadFormFields() {
            if (!this.formId) return;
            fetch('<?= APP_URL ?>/api/formularios/' + this.formId + '/campos-busqueda')
                .then(r => r.json())
                .then(data => {
                    this.fields = data.fields;
                    this.selectedFieldIds = data.fields.filter(f => f.es_comun).map(f => f.id);
                    this.formLoaded = true;
                    this.$nextTick(() => this.doSearch());
                });
        },

        toggleField(fieldId) {
            const idx = this.selectedFieldIds.indexOf(fieldId);
            if (idx >= 0) {
                this.selectedFieldIds.splice(idx, 1);
            } else {
                this.selectedFieldIds.push(fieldId);
            }
            this.doSearch();
        },

        doSearch() {
            this.page = 1;
            this.fetchRecords();
        },

        fetchRecords() {
            if (!this.showAllMode && (!this.formId || this.selectedFieldIds.length === 0)) return;
            this.loading = true;
            const params = new URLSearchParams();
            if (this.q) params.set('q', this.q);
            if (this.showAllMode) {
                params.set('form_id', 'todos');
            } else {
                params.set('form_id', this.formId);
                for (const fid of this.selectedFieldIds) {
                    params.append('field_ids[]', fid);
                }
            }
            if (this.estado) params.set('estado', this.estado);
            if (this.fechaDesde) params.set('fecha_desde', this.fechaDesde);
            if (this.fechaHasta) params.set('fecha_hasta', this.fechaHasta);
            params.set('page', String(this.page));
            params.set('per_page', String(this.perPage));

            fetch('<?= APP_URL ?>/api/registros/buscar?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(d => {
                this.records = d.records;
                this.total = d.total;
                this.totalPages = d.totalPages;
                this.loading = false;
                this.initialLoad = false;
                this.renderTable();
            })
            .catch((err) => {
                console.error('Error fetching records:', err);
                this.loading = false;
                this.initialLoad = false;
            });
        },

        goToPage(p) {
            if (p < 1 || p > this.totalPages) return;
            this.page = p;
            this.fetchRecords();
        },

        toggleShowAll() {
            this.showAllMode = !this.showAllMode;
            if (this.showAllMode) {
                this.page = 1;
                this.q = '';
                this.records = [];
                this.fetchRecords();
            } else {
                this.formId = '';
                this.formLoaded = false;
                this.records = [];
                this.total = 0;
                this.totalPages = 0;
                this.initialLoad = true;
                this.q = '';
                this.page = 1;
            }
        },

        clearExtraFilters() {
            this.estado = '';
            this.fechaDesde = '';
            this.fechaHasta = '';
            this.doSearch();
        },

        get visiblePages() {
            const total = this.totalPages;
            const current = this.page;
            let start = Math.max(1, current - 2);
            let end = Math.min(total, current + 2);
            if (end - start < 4) {
                if (start === 1) {
                    end = Math.min(total, start + 4);
                } else {
                    start = Math.max(1, end - 4);
                }
            }
            const pages = [];
            for (let i = start; i <= end; i++) pages.push(i);
            return pages;
        },

        highlight(text) {
            if (!text && text !== 0) return '';
            text = String(text);
            if (!this.q) return this.escapeHtml(text);
            const escaped = this.q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const regex = new RegExp(escaped, 'gi');
            let result = '';
            let lastIdx = 0;
            let m;
            while ((m = regex.exec(text)) !== null) {
                result += this.escapeHtml(text.slice(lastIdx, m.index));
                result += '<mark class="bg-amber-200/70 text-amber-900 rounded px-0.5">' + this.escapeHtml(m[0]) + '</mark>';
                lastIdx = regex.lastIndex;
            }
            result += this.escapeHtml(text.slice(lastIdx));
            return result;
        },

        escapeHtml(text) {
            const d = document.createElement('div');
            d.textContent = text;
            return d.innerHTML;
        },

        renderTable() {
            const tbody = document.getElementById('records-tbody');
            if (!tbody) return;

            if (this.records.length === 0) {
                tbody.innerHTML = '';
                return;
            }

            let html = '';
            const csrf = '<?= $csrf_token ?>';
            const baseUrl = '<?= APP_URL ?>';

            for (let i = 0; i < this.records.length; i++) {
                const r = this.records[i];

                const matchedBadge = r.matched_fields && r.matched_fields.length > 0
                    ? ' <span class="text-[11px] text-blue-600 font-medium">(' + r.matched_fields.join(', ') + ')</span>'
                    : '';

                const estadoClass = r.estado === 'activo' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700';
                const estadoLabel = r.estado.charAt(0).toUpperCase() + r.estado.slice(1);

                const archiveForm = r.estado === 'activo'
                    ? '<form action="' + baseUrl + '/registros/' + r.id + '/archivar" method="POST" class="inline" onsubmit="event.preventDefault(); confirmSwal(\'Archivar registro\', \'¿Archivar este registro?\', () => this.submit())"><input type="hidden" name="_csrf_token" value="' + csrf + '"><button type="submit" title="Archivar" class="p-1.5 text-gray-400 hover:text-purple-600 rounded-lg hover:bg-purple-50 transition-colors"><i class="fas fa-archive"></i></button></form>'
                    : '';

                const deleteForm = r.estado === 'archivado'
                    ? '<form action="' + baseUrl + '/registros/' + r.id + '/eliminar" method="POST" class="inline" onsubmit="event.preventDefault(); confirmSwal(\'Eliminar registro\', \'¿Eliminar este registro permanentemente?\', () => this.submit())"><input type="hidden" name="_csrf_token" value="' + csrf + '"><button type="submit" title="Eliminar" class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors"><i class="fas fa-trash"></i></button></form>'
                    : '';

                const delay = Math.min(i * 30, 150);
                html += '<tr class="hover:bg-gray-50 transition-colors animate-row" style="animation-delay: ' + delay + 'ms">';
                html += '<td class="px-4 sm:px-6 py-4 text-sm font-medium text-gray-900">' + this.highlight('# ' + r.id) + '</td>';
                html += '<td class="px-4 sm:px-6 py-4 text-sm text-gray-700">' + this.highlight(r.form_titulo) + matchedBadge + '</td>';
                html += '<td class="px-4 sm:px-6 py-4 text-sm text-gray-500 hidden md:table-cell">' + this.highlight(r.persona_apellido + ' ' + r.persona_nombre) + '</td>';
                html += '<td class="px-4 sm:px-6 py-4"><span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium ' + estadoClass + '">' + estadoLabel + '</span></td>';
                html += '<td class="px-4 sm:px-6 py-4 text-sm text-gray-500 hidden lg:table-cell whitespace-nowrap">' + r.created_at + '</td>';
                html += '<td class="px-4 sm:px-6 py-4 text-right"><div class="flex items-center justify-end space-x-1">';
                html += '<a href="' + baseUrl + '/registros/' + r.id + '" title="Ver" class="p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-colors"><i class="fas fa-eye"></i></a>';
                html += '<a href="' + baseUrl + '/registros/' + r.id + '/editar" title="Editar" class="p-1.5 text-gray-400 hover:text-amber-600 rounded-lg hover:bg-amber-50 transition-colors"><i class="fas fa-edit"></i></a>';
                html += archiveForm;
                html += deleteForm;
                html += '<a href="' + baseUrl + '/registros/' + r.id + '/historial" title="Historial" class="p-1.5 text-gray-400 hover:text-green-600 rounded-lg hover:bg-green-50 transition-colors hidden lg:block"><i class="fas fa-history"></i></a>';
                html += '</div></td>';
                html += '</tr>';
            }

            tbody.innerHTML = html;
        }
    };
}
</script>

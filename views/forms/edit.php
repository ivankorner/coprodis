<div class="space-y-6">
    <div class="flex items-center space-x-4">
        <a href="<?= APP_URL ?>/formularios" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900">Editar: <?= $form->titulo ?></h1>
            <p class="text-sm text-gray-500">Estado: <span class="font-medium"><?= ucfirst($form->estado) ?></span></p>
        </div>
    </div>

    <div x-data="formBuilder()" class="grid lg:grid-cols-3 gap-6">
        <!-- Field Editor -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-700">Campos del Formulario</h2>
                    <span class="text-xs text-gray-500" x-text="fields.length + ' campo(s)'"></span>
                </div>

                <!-- Field list -->
                <div class="space-y-3">
                    <template x-for="(field, index) in fields" :key="index">
                         <div class="flex items-start border rounded-lg p-4 transition-colors"
                              :class="field.tipo === 'separador' 
                                 ? 'border-blue-200 bg-blue-50' 
                                 : field.condicion_campo_padre_id
                                   ? 'ml-8 border-l-4 border-green-400 bg-green-50'
                                   : (editingIndex === index ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300 bg-white')">
                            <!-- Move arrows (hidden on child fields) -->
                            <div x-show="!field.condicion_campo_padre_id" class="flex-shrink-0 flex flex-col mr-3 mt-1">
                                <button @click="moveField(index, -1)" :disabled="index === 0"
                                        class="p-0.5 text-gray-400 hover:text-blue-600 disabled:opacity-25 disabled:cursor-not-allowed"
                                        title="Mover arriba">
                                    <i class="fas fa-chevron-up text-xs"></i>
                                </button>
                                <button @click="moveField(index, 1)" :disabled="index === fields.length - 1"
                                        class="p-0.5 text-gray-400 hover:text-blue-600 disabled:opacity-25 disabled:cursor-not-allowed"
                                        title="Mover abajo">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </button>
                            </div>
                            <div class="flex-1 min-w-0 cursor-pointer" @click="editField(index)">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="text-xs font-medium px-2 py-0.5 rounded"
                                          :class="field.tipo === 'separador' ? 'text-indigo-600 bg-indigo-50' : field.condicion_campo_padre_id ? 'text-green-700 bg-green-100' : 'text-blue-600 bg-blue-50'">
                                        <i x-show="field.condicion_campo_padre_id" class="fas fa-code-branch mr-1"></i>
                                        <span x-text="field.condicion_campo_padre_id ? 'Sub-pregunta' : field.tipo.charAt(0).toUpperCase() + field.tipo.slice(1)"></span>
                                    </span>
                                    <span class="text-xs text-gray-500" x-show="field.requerido && field.tipo !== 'separador'">* Requerido</span>
                                    <span class="text-xs text-amber-600 bg-amber-50 px-2 py-0.5 rounded" x-show="editingIndex === index">Editando</span>
                                </div>
                                <p class="text-sm font-medium text-gray-900" x-text="field.etiqueta || 'Sin etiqueta'"></p>
                                <p class="text-xs text-green-600 mt-0.5" x-show="field.condicion_campo_padre_id"
                                   x-text="'Sub-pregunta de: ' + getParentLabel(field)"></p>
                                <p class="text-xs text-gray-500" x-show="field.placeholder && field.tipo !== 'separador'" x-text="field.placeholder"></p>
                                <p class="text-xs text-gray-400 mt-1" x-show="field.ayuda && field.tipo !== 'separador'" x-text="'Ayuda: ' + field.ayuda"></p>
                            </div>
                            <div class="flex-shrink-0 ml-2 flex flex-col space-y-1">
                                <button @click.stop="editField(index)" class="p-1 text-gray-400 hover:text-blue-600"
                                        title="Editar campo"
                                        :class="editingIndex === index ? 'text-blue-600' : ''">
                                    <i class="fas fa-pen text-xs"></i>
                                </button>
                                <button @click="removeField(index)" class="p-1 text-gray-400 hover:text-red-600"
                                        title="Eliminar campo">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                    <div x-show="fields.length === 0" class="text-center py-8">
                        <i class="fas fa-plus-circle text-gray-300 text-3xl mb-2"></i>
                        <p class="text-sm text-gray-500">Agrega campos al formulario</p>
                    </div>
                </div>
            </div>

            <!-- Form metadata edit -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Información del Formulario</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                        <input type="text" x-model="formTitle" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea x-model="formDesc" rows="2"
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Título de Sección Inicial</label>
                        <input type="text" x-model="formSectionTitle"
                               placeholder="Ej: General, Datos Personales, Información General"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                        <p class="mt-1 text-xs text-gray-500">Título de la primera sección (antes del primer separador)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Field Panel -->
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 sticky top-24 max-h-[calc(100vh-7rem)] overflow-y-auto overflow-x-hidden">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-700" x-text="editingIndex !== null ? 'Editar Campo' : 'Agregar Campo'"></h2>
                    <span x-show="editingIndex !== null"
                          class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded font-medium">
                        Editando campo <span x-text="editingIndex + 1"></span>
                    </span>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipo de campo</label>
                        <select x-model="fieldForm.tipo" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="texto">Texto</option>
                            <option value="numero">Número</option>
                            <option value="telefono">Teléfono</option>
                            <option value="moneda">Moneda ($)</option>
                            <option value="porcentaje">Porcentaje</option>
                            <option value="url">URL / Enlace</option>
                            <option value="email">Correo Electrónico</option>
                            <option value="fecha">Fecha</option>
                            <option value="hora">Hora</option>
                            <option value="textarea">Área de Texto</option>
                            <option value="select">Lista Desplegable</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="radio">Radio Button</option>
                            <option value="imagen">Imagen</option>
                            <option value="archivo">Archivo</option>
                            <option value="firma">Firma Digital</option>
                            <option value="gps">Coordenadas GPS</option>
                            <option value="separador">Separador de Sección</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Etiqueta *</label>
                        <input type="text" x-model="fieldForm.etiqueta" placeholder="Ej: Nombre completo"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>

                    <div x-show="fieldForm.tipo !== 'separador'">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nombre del campo *</label>
                        <input type="text" x-model="fieldForm.nombre" placeholder="Ej: nombre_completo"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>

                    <div x-show="fieldForm.tipo !== 'separador'">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Placeholder</label>
                        <input type="text" x-model="fieldForm.placeholder"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>

                    <div x-show="fieldForm.tipo !== 'separador'">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Texto de ayuda</label>
                        <input type="text" x-model="fieldForm.ayuda" placeholder="Texto informativo para el usuario"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>

                    <div x-show="fieldForm.tipo !== 'separador' && ['select', 'checkbox', 'radio'].includes(fieldForm.tipo)">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Opciones (una por línea)</label>
                        <textarea x-model="fieldForm.opciones_text" rows="4"
                                  placeholder="Opción 1&#10;Opción 2&#10;Opción 3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                    </div>

                    <label x-show="fieldForm.tipo !== 'separador'" class="flex items-center space-x-2">
                        <input type="checkbox" x-model="fieldForm.requerido" class="rounded border-gray-300 text-blue-600">
                        <span class="text-sm text-gray-700">Campo requerido</span>
                    </label>

                    <!-- Sub-preguntas condicionales (solo para select/radio) -->
                    <div x-show="['select', 'radio'].includes(fieldForm.tipo)" class="border-t pt-4 mt-2">
                        <label class="block text-xs font-medium text-gray-600 mb-2">
                            <i class="fas fa-code-branch mr-1"></i> Sub-preguntas condicionales
                        </label>
                        <p class="text-xs text-gray-400 mb-3">Vinculá un campo existente o creá uno nuevo para cada opción</p>
                        
                        <div class="space-y-2">
                            <template x-for="(opt, optIdx) in fieldForm.opciones_array" :key="optIdx">
                                <div>
                                        <div class="flex items-center space-x-2 bg-gray-50 rounded-lg p-2">
                                        <span class="text-xs text-gray-500 flex-shrink-0"
                                              x-text="'Si: ' + opt"></span>
                                        <div class="flex-1"></div>
                                        <span x-show="fieldForm.sub_preguntas[opt] && fieldForm.sub_preguntas[opt].length > 0"
                                              class="text-xs px-2 py-0.5 rounded flex-shrink-0"
                                              :class="editingIndex !== null ? 'text-green-600 bg-green-50' : 'text-amber-600 bg-amber-50'">
                                            <i class="fas" :class="editingIndex !== null ? 'fa-check' : 'fa-clock'"></i>
                                            <span x-text="fieldForm.sub_preguntas[opt].length + ' campo(s)'"></span>
                                        </span>
                                        <button type="button" @click="openSubPreguntaForm(opt)"
                                                class="flex-shrink-0 px-2 py-1.5 text-xs text-green-600 hover:text-green-800 hover:bg-green-50 rounded-lg transition-colors"
                                                :title="(fieldForm.sub_preguntas[opt] && fieldForm.sub_preguntas[opt].length > 0) ? 'Editar campo vinculado' : 'Crear campo nuevo para esta opción'">
                                            <i class="fas" :class="(fieldForm.sub_preguntas[opt] && fieldForm.sub_preguntas[opt].length > 0) ? 'fa-pen' : 'fa-plus'"></i>
                                        </button>
                                    </div>

                                    <!-- Mini-formulario inline para crear campo condicional -->
                                    <div x-show="subPreguntaVisible === opt" x-transition class="ml-6 mt-2 p-3 bg-white border border-green-200 rounded-lg space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-medium text-green-700">
                                                <i class="fas fa-plus-circle mr-1"></i> Nuevo campo para: <span x-text="opt"></span>
                                            </span>
                                            <button type="button" @click="subPreguntaVisible = null" class="text-gray-400 hover:text-gray-600">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </div>

                                        <div x-show="fieldForm.sub_preguntas[opt] && fieldForm.sub_preguntas[opt].length > 0"
                                             class="space-y-1.5 bg-gray-50 rounded-lg p-2">
                                            <p class="text-xs font-medium text-gray-600">Campos agregados:</p>
                                            <template x-for="(child, childIdx) in fieldForm.sub_preguntas[opt]" :key="childIdx">
                                                <div class="flex items-center justify-between bg-white rounded px-2 py-1 border border-gray-100">
                                                    <div class="flex items-center space-x-2 min-w-0">
                                                        <span class="text-xs text-gray-500 font-medium whitespace-nowrap" x-text="child.tipo.charAt(0).toUpperCase() + child.tipo.slice(1)"></span>
                                                        <span class="text-xs text-gray-700 truncate" x-text="'\"' + child.etiqueta + '\"'"></span>
                                                    </div>
                                                    <button type="button" @click="removeChildSubPregunta(opt, childIdx)"
                                                            class="text-red-400 hover:text-red-600 flex-shrink-0 ml-2">
                                                        <i class="fas fa-times text-xs"></i>
                                                    </button>
                                                </div>
                                            </template>
                                            <p x-show="editingIndex === null" class="text-xs text-amber-500">
                                                <i class="fas fa-info-circle mr-1"></i> Se guardarán al crear el campo
                                            </p>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Tipo de campo *</label>
                                            <select x-model="subPreguntaForm.tipo"
                                                    class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs">
                                                <option value="texto">Texto</option>
                                                <option value="numero">Número</option>
                                                <option value="textarea">Área de Texto</option>
                                                <option value="email">Correo Electrónico</option>
                                                <option value="telefono">Teléfono</option>
                                                <option value="fecha">Fecha</option>
                                                <option value="hora">Hora</option>
                                                <option value="checkbox">Checkbox</option>
                                                <option value="radio">Radio Button</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Etiqueta *</label>
                                            <input type="text" x-model="subPreguntaForm.etiqueta"
                                                   placeholder="Ej: Especificar motivo"
                                                   class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Nombre del campo *</label>
                                            <input type="text" x-model="subPreguntaForm.nombre"
                                                   placeholder="Se genera automáticamente si lo dejás vacío"
                                                   class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs">
                                        </div>

                                        <div x-show="['texto', 'numero', 'textarea', 'email', 'telefono'].includes(subPreguntaForm.tipo)">
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Placeholder</label>
                                            <input type="text" x-model="subPreguntaForm.placeholder"
                                                   class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs">
                                        </div>

                                        <div x-show="['checkbox', 'radio'].includes(subPreguntaForm.tipo)">
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Opciones (una por línea)</label>
                                            <textarea x-model="subPreguntaForm.opciones_text" rows="3"
                                                      placeholder="Opción 1&#10;Opción 2&#10;Opción 3"
                                                      class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs"></textarea>
                                        </div>

                                        <button type="button" @click="addConditionalField(opt)"
                                                class="w-full px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 text-xs font-medium">
                                            <i class="fas fa-plus mr-1"></i> Agregar Campo
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <p x-show="fieldForm.opciones_array.length === 0" class="text-xs text-amber-500">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Agregá opciones al campo primero
                            </p>
                        </div>
                    </div>

                    <!-- Add mode buttons -->
                    <template x-if="editingIndex === null">
                        <div class="space-y-2">
                            <button @click="addField()"
                                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                                <i class="fas fa-plus mr-1"></i> Agregar Campo
                            </button>
                        </div>
                    </template>

                    <!-- Edit mode buttons -->
                    <template x-if="editingIndex !== null">
                        <div class="space-y-2">
                            <button @click="updateField()"
                                    class="w-full px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 text-sm font-medium">
                                <i class="fas fa-check mr-1"></i> Actualizar Campo
                            </button>
                            <button @click="cancelEdit()"
                                    class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </button>
                        </div>
                    </template>

                    <!-- Save Button (always visible) -->
                    <button @click="saveFields()"
                            class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-semibold">
                        <i class="fas fa-save mr-2"></i> Guardar Todos los Campos
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function formBuilder() {
    const STORAGE_KEY = 'form_builder_<?= $form->id ?>';

    function loadDraft() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            return saved ? JSON.parse(saved) : null;
        } catch { return null; }
    }

    function saveDraft(fields) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify({ fields, timestamp: Date.now() }));
    }

    function clearDraft() {
        localStorage.removeItem(STORAGE_KEY);
    }

    function showDraftIndicator() {
        if (document.getElementById('builder-draft-indicator')) return;
        const indicator = document.createElement('div');
        indicator.id = 'builder-draft-indicator';
        indicator.className = 'fixed bottom-4 left-4 bg-green-600 text-white px-3 py-2 rounded-lg text-sm shadow-lg z-50 flex items-center space-x-2';
        indicator.innerHTML = '<i class="fas fa-save"></i><span>Borrador guardado</span><button onclick="this.parentElement.remove()" class="ml-2 text-white hover:text-gray-200"><i class="fas fa-times"></i></button>';
        document.body.appendChild(indicator);
    }

    const draft = loadDraft();
    const initialFields = draft?.fields ?? <?= json_encode(array_map(function($f) use ($fields) {
        $padreNombre = '';
        if ($f->condicion_campo_padre) {
            foreach ($fields as $pf) {
                if ($pf->id == $f->condicion_campo_padre) {
                    $padreNombre = $pf->nombre;
                    break;
                }
            }
        }
        return [
            'id' => $f->id,
            'tipo' => $f->tipo,
            'nombre' => $f->nombre,
            'etiqueta' => $f->etiqueta,
            'placeholder' => $f->placeholder,
            'ayuda' => $f->ayuda,
            'requerido' => (bool)$f->requerido,
            'opciones' => $f->opciones ? json_decode($f->opciones) : [],
            'condicion_campo_padre_id' => $padreNombre ?: '',
            'condicion_valor' => $f->condicion_valor ?? '',
        ];
    }, $fields ?? [])) ?>;

    return {
        fields: initialFields,
        formTitle: <?= json_encode($form->titulo) ?>,
        formDesc: <?= json_encode($form->descripcion ?? '') ?>,
        formSectionTitle: <?= json_encode($form->seccion_inicial_titulo ?? 'General') ?>,
        hasDraft: !!draft,
        editingIndex: null,

        subPreguntaVisible: null,
        subPreguntaTargetOpt: '',
        subPreguntaForm: {
            tipo: 'texto',
            etiqueta: '',
            nombre: '',
            placeholder: '',
            opciones_text: '',
        },

        fieldForm: {
            tipo: 'texto',
            etiqueta: '',
            nombre: '',
            placeholder: '',
            ayuda: '',
            opciones_text: '',
            requerido: false,
            opciones_array: [],
            sub_preguntas: {},
        },

        init() {
            this.fields = this.reorderFields(this.fields);
            if (this.hasDraft) showDraftIndicator();
            this.$watch('fields', (val) => {
                saveDraft(val);
                showDraftIndicator();
            }, { deep: true });
            this.$watch('fieldForm.opciones_text', (val) => {
                const newOpts = val ? val.split('\n').filter(o => o.trim()) : [];
                const oldOpts = this.fieldForm.opciones_array;
                const newSub = {};
                newOpts.forEach(opt => {
                    const oldEntry = this.fieldForm.sub_preguntas[opt];
                    newSub[opt] = (oldOpts.includes(opt) && oldEntry && Array.isArray(oldEntry))
                        ? oldEntry : [];
                });
                this.fieldForm.opciones_array = newOpts;
                this.fieldForm.sub_preguntas = newSub;
            });
        },

        getBlock(index) {
            const field = this.fields[index];
            if (!field) return { start: index, end: index };

            if (field.condicion_campo_padre_id) {
                const parentIdx = this.fields.findIndex(f =>
                    !f.condicion_campo_padre_id &&
                    (f.id == field.condicion_campo_padre_id || f.nombre === field.condicion_campo_padre_id)
                );
                if (parentIdx !== -1) return this.getBlock(parentIdx);
                return { start: index, end: index };
            }

            const parentKey = field.nombre || field.id;
            if (!parentKey) return { start: index, end: index };

            let end = index;
            const descendantKeys = new Set([parentKey]);
            for (let i = index + 1; i < this.fields.length; i++) {
                const f = this.fields[i];
                if (f.condicion_campo_padre_id && descendantKeys.has(f.condicion_campo_padre_id)) {
                    end = i;
                    const fKey = f.nombre || f.id;
                    if (fKey) descendantKeys.add(fKey);
                } else {
                    break;
                }
            }
            return { start: index, end };
        },

        moveField(index, direction) {
            const block = this.getBlock(index);
            const blockSize = block.end - block.start + 1;

            if (direction === -1 && block.start === 0) return;
            if (direction === 1 && block.end === this.fields.length - 1) return;

            const neighborIdx = direction === -1 ? block.start - 1 : block.end + 1;
            const neighborBlock = this.getBlock(neighborIdx);
            const neighborSize = neighborBlock.end - neighborBlock.start + 1;

            const editedField = this.editingIndex !== null ? this.fields[this.editingIndex] : null;

            const items = [...this.fields];
            const blockItems = items.splice(block.start, blockSize);

            if (direction === -1) {
                const neighborItems = items.splice(neighborBlock.start, neighborSize);
                items.splice(neighborBlock.start, 0, ...blockItems, ...neighborItems);
            } else {
                const neighborItems = items.splice(block.start, neighborSize);
                items.splice(block.start, 0, ...neighborItems, ...blockItems);
            }

            this.fields = items;

            if (editedField) {
                const newIdx = this.fields.findIndex(f =>
                    (f.id && editedField.id && f.id === editedField.id) ||
                    (f.nombre && editedField.nombre && f.nombre === editedField.nombre)
                );
                if (newIdx !== -1) this.editingIndex = newIdx;
            }
        },

        resetFieldForm() {
            this.fieldForm = {
                tipo: 'texto', etiqueta: '', nombre: '', placeholder: '', ayuda: '',
                opciones_text: '', requerido: false, opciones_array: [], sub_preguntas: {},
            };
        },

        addField() {
            const esSeparador = this.fieldForm.tipo === 'separador';
            
            if (!esSeparador && (!this.fieldForm.etiqueta || !this.fieldForm.nombre)) {
                Swal.fire('Error', 'La etiqueta y el nombre son obligatorios.', 'error');
                return;
            }
            if (esSeparador && !this.fieldForm.etiqueta) {
                Swal.fire('Error', 'La etiqueta es obligatoria para el separador.', 'error');
                return;
            }

            // Auto-generar nombre para separadores
            let nombre = this.fieldForm.nombre;
            if (esSeparador) {
                const count = this.fields.filter(f => f.tipo === 'separador').length + 1;
                nombre = 'seccion_' + count;
            }

            let opciones = [];
            if (!esSeparador && ['select', 'checkbox', 'radio'].includes(this.fieldForm.tipo) && this.fieldForm.opciones_text) {
                opciones = this.fieldForm.opciones_text.split('\n').filter(o => o.trim());
            }

            const parentField = {
                tipo: this.fieldForm.tipo,
                nombre: nombre,
                etiqueta: this.fieldForm.etiqueta,
                placeholder: esSeparador ? '' : this.fieldForm.placeholder,
                ayuda: esSeparador ? '' : this.fieldForm.ayuda,
                requerido: esSeparador ? false : this.fieldForm.requerido,
                opciones: opciones,
            };

            const fieldsToAdd = [parentField];

            if (!esSeparador && ['select', 'radio'].includes(this.fieldForm.tipo)) {
                const parentNombre = parentField.nombre;
                this.fieldForm.opciones_array.forEach(opt => {
                    const children = this.fieldForm.sub_preguntas[opt] || [];
                    children.forEach(childData => {
                        fieldsToAdd.push({
                            tipo: childData.tipo,
                            nombre: childData.nombre,
                            etiqueta: childData.etiqueta,
                            placeholder: childData.placeholder || '',
                            ayuda: '',
                            requerido: false,
                            opciones: childData.opciones || [],
                            condicion_campo_padre_id: parentNombre,
                            condicion_valor: opt,
                        });
                    });
                });
            }

            this.fields.push(...fieldsToAdd);

            this.resetFieldForm();
        },

        editField(index) {
            const field = this.fields[index];
            this.editingIndex = index;

            const opcionesArray = Array.isArray(field.opciones) ? field.opciones : [];
            const subPreguntas = {};

            if (['select', 'radio'].includes(field.tipo) && field.nombre) {
                opcionesArray.forEach(opt => { subPreguntas[opt] = []; });
                const parentNombre = field.nombre;
                this.fields.forEach(f => {
                    if (f.condicion_campo_padre_id === parentNombre && f.condicion_valor) {
                        if (!subPreguntas[f.condicion_valor]) subPreguntas[f.condicion_valor] = [];
                        subPreguntas[f.condicion_valor].push({
                            tipo: f.tipo,
                            nombre: f.nombre,
                            etiqueta: f.etiqueta,
                            placeholder: f.placeholder || '',
                            opciones: Array.isArray(f.opciones) ? f.opciones : [],
                        });
                    }
                });
            }

            const esSeparador = field.tipo === 'separador';

            this.fieldForm = {
                tipo: field.tipo,
                etiqueta: field.etiqueta,
                nombre: esSeparador ? '' : field.nombre,
                placeholder: esSeparador ? '' : (field.placeholder || ''),
                ayuda: esSeparador ? '' : (field.ayuda || ''),
                opciones_text: opcionesArray.join('\n'),
                requerido: esSeparador ? false : field.requerido,
                opciones_array: opcionesArray,
                sub_preguntas: subPreguntas,
            };
        },

        updateField() {
            if (this.editingIndex === null) return;
            const esSeparador = this.fieldForm.tipo === 'separador';
            
            if (!esSeparador && (!this.fieldForm.etiqueta || !this.fieldForm.nombre)) {
                Swal.fire('Error', 'La etiqueta y el nombre son obligatorios.', 'error');
                return;
            }
            if (esSeparador && !this.fieldForm.etiqueta) {
                Swal.fire('Error', 'La etiqueta es obligatoria para el separador.', 'error');
                return;
            }

            let opciones = [];
            if (!esSeparador && ['select', 'checkbox', 'radio'].includes(this.fieldForm.tipo) && this.fieldForm.opciones_text) {
                opciones = this.fieldForm.opciones_text.split('\n').filter(o => o.trim());
            }

            // Mantener nombre existente si es separador
            let nombre = this.fieldForm.nombre;
            if (esSeparador) {
                nombre = this.fields[this.editingIndex].nombre || 'seccion_1';
            }

            this.fields[this.editingIndex] = {
                ...this.fields[this.editingIndex],
                tipo: this.fieldForm.tipo,
                nombre: nombre,
                etiqueta: this.fieldForm.etiqueta,
                placeholder: esSeparador ? '' : this.fieldForm.placeholder,
                ayuda: esSeparador ? '' : this.fieldForm.ayuda,
                requerido: esSeparador ? false : this.fieldForm.requerido,
                opciones: opciones,
            };

            this.cancelEdit();
        },

        cancelEdit() {
            this.editingIndex = null;
            this.resetFieldForm();
        },

        openSubPreguntaForm(opt) {
            this.subPreguntaTargetOpt = opt;
            this.subPreguntaVisible = (this.subPreguntaVisible === opt) ? null : opt;
            this.subPreguntaForm = {
                tipo: 'texto', etiqueta: '', nombre: '', placeholder: '', opciones_text: '',
            };
        },

        addConditionalField(opt) {
            const form = this.subPreguntaForm;
            if (!form.etiqueta) {
                Swal.fire('Error', 'La etiqueta es obligatoria.', 'error');
                return;
            }
            if (['checkbox', 'radio'].includes(form.tipo) && !form.opciones_text.trim()) {
                Swal.fire('Error', 'Agregá al menos una opción para checkbox/radio.', 'error');
                return;
            }

            let nombre = form.nombre.trim();
            if (!nombre) {
                nombre = 'campo_' + opt.toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '')
                    + '_' + Date.now().toString(36);
            }

            let opciones = [];
            if (['checkbox', 'radio'].includes(form.tipo) && form.opciones_text) {
                opciones = form.opciones_text.split('\n').filter(o => o.trim());
            }

            const childEntry = {
                tipo: form.tipo,
                nombre: nombre,
                etiqueta: form.etiqueta,
                placeholder: form.placeholder || '',
                opciones: opciones,
            };

            if (this.editingIndex !== null) {
                const parentField = this.fields[this.editingIndex];
                const parentKey = parentField.nombre;

                const newField = {
                    ...childEntry,
                    ayuda: '',
                    requerido: false,
                    condicion_campo_padre_id: parentKey,
                    condicion_valor: opt,
                };

                let insertIdx = this.editingIndex + 1;
                const descendantKeys = new Set();
                while (insertIdx < this.fields.length) {
                    const f = this.fields[insertIdx];
                    if (f.condicion_campo_padre_id && (f.condicion_campo_padre_id === parentKey || descendantKeys.has(f.condicion_campo_padre_id))) {
                        const fKey = f.nombre;
                        if (fKey) descendantKeys.add(fKey);
                        insertIdx++;
                    } else {
                        break;
                    }
                }
                this.fields.splice(insertIdx, 0, newField);
            }

            if (!this.fieldForm.sub_preguntas[opt]) {
                this.fieldForm.sub_preguntas[opt] = [];
            }
            this.fieldForm.sub_preguntas[opt].push(childEntry);

            this.subPreguntaForm = {
                tipo: 'texto', etiqueta: '', nombre: '', placeholder: '', opciones_text: '',
            };
        },

        removeChildSubPregunta(opt, childIndex) {
            const children = this.fieldForm.sub_preguntas[opt];
            if (!children || childIndex >= children.length) return;

            const child = children[childIndex];

            if (this.editingIndex !== null) {
                const fieldIdx = this.fields.findIndex(f => f.nombre === child.nombre);
                if (fieldIdx !== -1) {
                    this.fields.splice(fieldIdx, 1);
                    if (this.editingIndex > fieldIdx) {
                        this.editingIndex--;
                    }
                }
            }

            const updated = [...children];
            updated.splice(childIndex, 1);
            this.fieldForm.sub_preguntas[opt] = updated;
        },

        removeField(index) {
            if (this.editingIndex === index) {
                this.cancelEdit();
            } else if (this.editingIndex !== null && this.editingIndex > index) {
                this.editingIndex--;
            }
            this.fields.splice(index, 1);
        },

        saveFields() {
            const toSave = this.fields.map(f => ({
                ...f,
                condicion_campo_padre: f.condicion_campo_padre_id || null,
                condicion_valor: f.condicion_valor || null,
            }));

            const allSubs = {};

            if (this.editingIndex !== null) {
                Object.assign(allSubs, this.fieldForm.sub_preguntas);
            }

            this.fields.forEach(f => {
                if (!['select', 'radio'].includes(f.tipo)) return;
                const parentNombre = f.nombre;
                if (!parentNombre) return;
                const idx = toSave.findIndex(tf => tf.nombre === parentNombre);
                if (idx === -1) return;
                const opciones = Array.isArray(toSave[idx].opciones) ? toSave[idx].opciones : [];
                opciones.forEach(opt => {
                    const children = allSubs[opt] || [];
                    children.forEach(childData => {
                        const childNombre = childData.nombre;
                        if (!childNombre) return;
                        const childIdx = toSave.findIndex(c => c.nombre === childNombre);
                        if (childIdx !== -1) {
                            toSave[childIdx].condicion_campo_padre = parentNombre;
                            toSave[childIdx].condicion_valor = opt;
                        }
                    });
                });
            });

            fetch('<?= APP_URL ?>/formularios/guardar-campos', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    form_id: <?= $form->id ?>,
                    titulo: this.formTitle,
                    descripcion: this.formDesc,
                    seccion_inicial_titulo: this.formSectionTitle,
                    fields: toSave.map(f => ({
                        tipo: f.tipo,
                        nombre: f.nombre,
                        etiqueta: f.etiqueta,
                        placeholder: f.placeholder || null,
                        ayuda: f.ayuda || null,
                        requerido: f.requerido ? 1 : 0,
                        opciones: f.opciones || null,
                        valor_defecto: null,
                        condicion_campo_padre: f.condicion_campo_padre || null,
                        condicion_valor: f.condicion_valor || null,
                    })),
                    _csrf_token: '<?= $csrf_token ?>',
                })
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    clearDraft();
                    this.hasDraft = false;
                    window.location.href = d.redirect || '<?= APP_URL ?>/formularios';
                } else {
                    Swal.fire('Error', d.message || 'Error al guardar campos.', 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Error de conexión.', 'error'));
        },

        clearDraft() {
            clearDraft();
            this.hasDraft = false;
            this.fields = initialFields;
            this.resetFieldForm();
        },

        showDraftIndicator() {
            if (!this.hasDraft) {
                this.hasDraft = true;
                this.$nextTick(() => {
                    if (!document.getElementById('builder-draft-indicator')) showDraftIndicator();
                });
            }
        },

        reorderFields(fields) {
            const ordered = [];
            const remaining = fields.filter(f => f.condicion_campo_padre_id);

            for (const f of fields) {
                if (!f.condicion_campo_padre_id) {
                    ordered.push(f);
                    const parentKey = f.nombre || f.id;
                    if (parentKey) {
                        let i = 0;
                        while (i < remaining.length) {
                            if (remaining[i].condicion_campo_padre_id == parentKey) {
                                ordered.push(remaining[i]);
                                remaining.splice(i, 1);
                            } else {
                                i++;
                            }
                        }
                    }
                }
            }
            ordered.push(...remaining);
            return ordered;
        },

        getParentLabel(field) {
            if (!field.condicion_campo_padre_id) return '';
            const parent = this.fields.find(f =>
                f.id == field.condicion_campo_padre_id || f.nombre === field.condicion_campo_padre_id
            );
            return parent ? parent.etiqueta : '(campo eliminado)';
        },

        get childFieldCandidates() {
            const currentId = this.editingIndex !== null ? this.fields[this.editingIndex]?.id : null;
            const currentNombre = this.editingIndex !== null ? this.fields[this.editingIndex]?.nombre : null;
            return this.fields.filter(f => {
                if (currentId && f.id === currentId) return false;
                if (currentNombre && f.nombre === currentNombre) return false;
                return true;
            });
        }
    };
}
</script>

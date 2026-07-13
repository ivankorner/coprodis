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
                        <div class="flex items-start border rounded-lg p-4 transition-colors bg-white"
                             :class="editingIndex === index ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300'">
                            <!-- Move arrows -->
                            <div class="flex-shrink-0 flex flex-col mr-3 mt-1">
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
                                    <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-0.5 rounded"
                                          x-text="field.tipo.charAt(0).toUpperCase() + field.tipo.slice(1)"></span>
                                    <span class="text-xs text-gray-500" x-show="field.requerido">* Requerido</span>
                                    <span class="text-xs text-amber-600 bg-amber-50 px-2 py-0.5 rounded" x-show="editingIndex === index">Editando</span>
                                </div>
                                <p class="text-sm font-medium text-gray-900" x-text="field.etiqueta || 'Sin etiqueta'"></p>
                                <p class="text-xs text-gray-500" x-show="field.placeholder" x-text="field.placeholder"></p>
                                <p class="text-xs text-gray-400 mt-1" x-show="field.ayuda" x-text="'Ayuda: ' + field.ayuda"></p>
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
                <form action="<?= APP_URL ?>/formularios/<?= $form->id ?>/editar" method="POST">
                    <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                            <input type="text" name="titulo" value="<?= $form->titulo ?>" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <textarea name="descripcion" rows="2"
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"><?= $form->descripcion ?></textarea>
                        </div>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                            <i class="fas fa-save mr-1"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add/Edit Field Panel -->
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 sticky top-24">
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
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Etiqueta *</label>
                        <input type="text" x-model="fieldForm.etiqueta" placeholder="Ej: Nombre completo"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nombre del campo *</label>
                        <input type="text" x-model="fieldForm.nombre" placeholder="Ej: nombre_completo"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Placeholder</label>
                        <input type="text" x-model="fieldForm.placeholder"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Texto de ayuda</label>
                        <input type="text" x-model="fieldForm.ayuda" placeholder="Texto informativo para el usuario"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>

                    <div x-show="['select', 'checkbox', 'radio'].includes(fieldForm.tipo)">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Opciones (una por línea)</label>
                        <textarea x-model="fieldForm.opciones_text" rows="4"
                                  placeholder="Opción 1&#10;Opción 2&#10;Opción 3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                    </div>

                    <label class="flex items-center space-x-2">
                        <input type="checkbox" x-model="fieldForm.requerido" class="rounded border-gray-300 text-blue-600">
                        <span class="text-sm text-gray-700">Campo requerido</span>
                    </label>

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
    return {
        fields: <?= json_encode(array_map(fn($f) => [
            'id' => $f->id,
            'tipo' => $f->tipo,
            'nombre' => $f->nombre,
            'etiqueta' => $f->etiqueta,
            'placeholder' => $f->placeholder,
            'ayuda' => $f->ayuda,
            'requerido' => (bool)$f->requerido,
            'opciones' => $f->opciones ? json_decode($f->opciones) : [],
        ], $fields ?? [])) ?>,

        editingIndex: null,

        fieldForm: {
            tipo: 'texto',
            etiqueta: '',
            nombre: '',
            placeholder: '',
            ayuda: '',
            opciones_text: '',
            requerido: false,
        },

        moveField(index, direction) {
            const newIndex = index + direction;
            if (newIndex < 0 || newIndex >= this.fields.length) return;
            const items = [...this.fields];
            [items[index], items[newIndex]] = [items[newIndex], items[index]];
            this.fields = items;
            if (this.editingIndex === index) {
                this.editingIndex = newIndex;
            } else if (this.editingIndex === newIndex) {
                this.editingIndex = index;
            }
        },

        resetFieldForm() {
            this.fieldForm = { tipo: 'texto', etiqueta: '', nombre: '', placeholder: '', ayuda: '', opciones_text: '', requerido: false };
        },

        addField() {
            if (!this.fieldForm.etiqueta || !this.fieldForm.nombre) {
                Swal.fire('Error', 'La etiqueta y el nombre son obligatorios.', 'error');
                return;
            }

            let opciones = [];
            if (['select', 'checkbox', 'radio'].includes(this.fieldForm.tipo) && this.fieldForm.opciones_text) {
                opciones = this.fieldForm.opciones_text.split('\n').filter(o => o.trim());
            }

            this.fields.push({
                tipo: this.fieldForm.tipo,
                nombre: this.fieldForm.nombre,
                etiqueta: this.fieldForm.etiqueta,
                placeholder: this.fieldForm.placeholder,
                ayuda: this.fieldForm.ayuda,
                requerido: this.fieldForm.requerido,
                opciones: opciones,
            });

            this.resetFieldForm();
        },

        editField(index) {
            const field = this.fields[index];
            this.editingIndex = index;
            this.fieldForm = {
                tipo: field.tipo,
                etiqueta: field.etiqueta,
                nombre: field.nombre,
                placeholder: field.placeholder || '',
                ayuda: field.ayuda || '',
                opciones_text: Array.isArray(field.opciones) ? field.opciones.join('\n') : '',
                requerido: field.requerido,
            };
        },

        updateField() {
            if (this.editingIndex === null) return;
            if (!this.fieldForm.etiqueta || !this.fieldForm.nombre) {
                Swal.fire('Error', 'La etiqueta y el nombre son obligatorios.', 'error');
                return;
            }

            let opciones = [];
            if (['select', 'checkbox', 'radio'].includes(this.fieldForm.tipo) && this.fieldForm.opciones_text) {
                opciones = this.fieldForm.opciones_text.split('\n').filter(o => o.trim());
            }

            this.fields[this.editingIndex] = {
                ...this.fields[this.editingIndex],
                tipo: this.fieldForm.tipo,
                nombre: this.fieldForm.nombre,
                etiqueta: this.fieldForm.etiqueta,
                placeholder: this.fieldForm.placeholder,
                ayuda: this.fieldForm.ayuda,
                requerido: this.fieldForm.requerido,
                opciones: opciones,
            };

            this.cancelEdit();
        },

        cancelEdit() {
            this.editingIndex = null;
            this.resetFieldForm();
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
            fetch('<?= APP_URL ?>/formularios/guardar-campos', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    form_id: <?= $form->id ?>,
                    fields: this.fields.map(f => ({
                        tipo: f.tipo,
                        nombre: f.nombre,
                        etiqueta: f.etiqueta,
                        placeholder: f.placeholder || null,
                        ayuda: f.ayuda || null,
                        requerido: f.requerido ? 1 : 0,
                        opciones: f.opciones || null,
                        valor_defecto: null,
                    })),
                    _csrf_token: '<?= $csrf_token ?>',
                })
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    Swal.fire('Guardado', 'Campos guardados exitosamente.', 'success');
                } else {
                    Swal.fire('Error', d.message || 'Error al guardar campos.', 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Error de conexión.', 'error'));
        }
    };
}
</script>

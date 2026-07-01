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

                <!-- Draggable field list -->
                <div class="space-y-3">
                    <template x-for="(field, index) in fields" :key="index">
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-0.5 rounded"
                                              x-text="field.tipo.charAt(0).toUpperCase() + field.tipo.slice(1)"></span>
                                        <span class="text-xs text-gray-500" x-show="field.requerido">* Requerido</span>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900" x-text="field.etiqueta || 'Sin etiqueta'"></p>
                                    <p class="text-xs text-gray-500" x-show="field.placeholder" x-text="field.placeholder"></p>
                                </div>
                                <div class="flex space-x-1">
                                    <button @click="removeField(index)" class="p-1 text-gray-400 hover:text-red-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
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

        <!-- Add Field Panel -->
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 sticky top-24">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Agregar Campo</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipo de campo</label>
                        <select x-model="newField.tipo" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
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
                        <input type="text" x-model="newField.etiqueta" placeholder="Ej: Nombre completo"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nombre del campo *</label>
                        <input type="text" x-model="newField.nombre" placeholder="Ej: nombre_completo"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Placeholder</label>
                        <input type="text" x-model="newField.placeholder"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>

                    <div x-show="['select', 'checkbox', 'radio'].includes(newField.tipo)">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Opciones (una por línea)</label>
                        <textarea x-model="newField.opciones_text" rows="4"
                                  placeholder="Opción 1&#10;Opción 2&#10;Opción 3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                    </div>

                    <label class="flex items-center space-x-2">
                        <input type="checkbox" x-model="newField.requerido" class="rounded border-gray-300 text-blue-600">
                        <span class="text-sm text-gray-700">Campo requerido</span>
                    </label>

                    <button @click="addField()"
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                        <i class="fas fa-plus mr-1"></i> Agregar Campo
                    </button>
                </div>
            </div>

            <!-- Save Button -->
            <button @click="saveFields()"
                    class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-semibold">
                <i class="fas fa-save mr-2"></i> Guardar Todos los Campos
            </button>
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

        newField: {
            tipo: 'texto',
            etiqueta: '',
            nombre: '',
            placeholder: '',
            opciones_text: '',
            requerido: false,
        },

        addField() {
            if (!this.newField.etiqueta || !this.newField.nombre) {
                Swal.fire('Error', 'La etiqueta y el nombre son obligatorios.', 'error');
                return;
            }

            let opciones = [];
            if (['select', 'checkbox', 'radio'].includes(this.newField.tipo) && this.newField.opciones_text) {
                opciones = this.newField.opciones_text.split('\n').filter(o => o.trim());
            }

            this.fields.push({
                tipo: this.newField.tipo,
                nombre: this.newField.nombre,
                etiqueta: this.newField.etiqueta,
                placeholder: this.newField.placeholder,
                requerido: this.newField.requerido,
                opciones: opciones,
            });

            this.newField = { tipo: 'texto', etiqueta: '', nombre: '', placeholder: '', opciones_text: '', requerido: false };
        },

        removeField(index) {
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

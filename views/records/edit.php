<div class="max-w-3xl mx-auto">
    <div class="flex items-center space-x-4 mb-6">
        <a href="<?= APP_URL ?>/registros/<?= $record->id ?>" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Editar Registro #<?= $record->id ?></h1>
            <p class="text-sm text-gray-500">Formulario: <?= $record->form_titulo ?></p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
        <form action="<?= APP_URL ?>/registros/<?= $record->id ?>/editar" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
            <div class="space-y-5">
                <?php foreach ($fields as $field): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <?= $field->etiqueta ?>
                        <?php if ($field->requerido): ?><span class="text-red-500">*</span><?php endif; ?>
                    </label>

                    <?php if (in_array($field->tipo, ['texto', 'numero', 'email'])): ?>
                        <input type="<?= $field->tipo === 'email' ? 'email' : ($field->tipo === 'numero' ? 'number' : 'text') ?>"
                               name="field_<?= $field->id ?>" value="<?= $field->valor ?? '' ?>"
                               <?= $field->requerido ? 'required' : '' ?>
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">

                    <?php elseif ($field->tipo === 'fecha'): ?>
                        <input type="date" name="field_<?= $field->id ?>" value="<?= $field->valor ?? '' ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">

                    <?php elseif ($field->tipo === 'hora'): ?>
                        <input type="time" name="field_<?= $field->id ?>" value="<?= $field->valor ?? '' ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">

                    <?php elseif ($field->tipo === 'textarea'): ?>
                        <textarea name="field_<?= $field->id ?>" rows="4"
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"><?= $field->valor ?? '' ?></textarea>

                    <?php elseif ($field->tipo === 'select' && $field->opciones): ?>
                        <select name="field_<?= $field->id ?>"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                            <?php foreach (json_decode($field->opciones) ?? [] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($field->valor ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>

                    <?php elseif ($field->tipo === 'imagen'): ?>
                        <?php if ($field->valor): ?>
                            <p class="text-sm text-gray-500 mb-2">
                                <i class="fas fa-paperclip mr-1"></i> Archivo actual:
                                <a href="<?= APP_URL ?>/<?= $field->valor ?>" target="_blank" class="text-blue-600 hover:underline"><?= basename($field->valor) ?></a>
                            </p>
                        <?php endif; ?>
                        <input type="file" name="field_<?= $field->id ?>" accept="image/*"
                               onchange="validateFileSize(this, 10)"
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-xs text-gray-400">Formatos: JPG, PNG, GIF, WebP, SVG. Máx: 10MB. Dejar vacío para mantener el actual.</p>

                    <?php elseif ($field->tipo === 'archivo'): ?>
                        <?php if ($field->valor): ?>
                            <p class="text-sm text-gray-500 mb-2">
                                <i class="fas fa-paperclip mr-1"></i> Archivo actual:
                                <a href="<?= APP_URL ?>/<?= $field->valor ?>" target="_blank" class="text-blue-600 hover:underline"><?= basename($field->valor) ?></a>
                            </p>
                        <?php endif; ?>
                        <input type="file" name="field_<?= $field->id ?>" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx,.csv,.txt"
                               onchange="validateFileSize(this, 10)"
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-xs text-gray-400">Formatos: PDF, DOC, XLS, imágenes. Máx: 10MB. Dejar vacío para mantener el actual.</p>

                    <?php elseif ($field->tipo === 'gps'): ?>
                        <div class="flex items-center space-x-2">
                            <input type="text" name="field_<?= $field->id ?>" value="<?= $field->valor ?? '' ?>"
                                   class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm">
                            <button type="button" onclick="getLocation('field_<?= $field->id ?>')"
                                    class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                                <i class="fas fa-location-dot"></i>
                            </button>
                        </div>

                    <?php elseif ($field->tipo === 'firma'): ?>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                            <?php if ($field->valor): ?>
                                <img src="<?= APP_URL ?>/<?= $field->valor ?>" class="h-20 mb-2" alt="Firma actual">
                            <?php endif; ?>
                            <canvas id="sig_edit_<?= $field->id ?>" class="w-full h-32 border border-gray-200 rounded touch-none"></canvas>
                            <input type="hidden" name="field_<?= $field->id ?>" id="input_edit_<?= $field->id ?>">
                            <button type="button" onclick="clearSignature('edit_<?= $field->id ?>')"
                                    class="text-xs text-red-600 hover:text-red-800 mt-2">
                                <i class="fas fa-eraser mr-1"></i> Limpiar firma
                            </button>
                        </div>

                    <?php else: ?>
                        <input type="text" name="field_<?= $field->id ?>" value="<?= $field->valor ?? '' ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200 flex items-center space-x-4">
                <button type="submit"
                        class="px-6 py-2.5 bg-amber-600 text-white rounded-lg hover:bg-amber-700 font-medium text-sm transition-colors">
                    <i class="fas fa-save mr-2"></i> Actualizar Registro
                </button>
                <a href="<?= APP_URL ?>/registros/<?= $record->id ?>"
                   class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@5.0.1/dist/signature_pad.umd.min.js"></script>
<script>
function validateFileSize(input, maxMB) {
    if (input.files && input.files[0]) {
        const maxBytes = maxMB * 1024 * 1024;
        if (input.files[0].size > maxBytes) {
            Swal.fire('Archivo muy grande', 'El archivo excede el tamaño máximo de ' + maxMB + 'MB.', 'error');
            input.value = '';
        }
    }
}
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('canvas[id^="sig_"]').forEach(canvas => {
        const sig = new SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });
        const input = document.getElementById(canvas.id.replace('sig_', 'input_'));
        canvas.parentElement._sigPad = sig;
        canvas.addEventListener('mouseup', () => { if (!sig.isEmpty()) input.value = sig.toDataURL(); });
        canvas.addEventListener('touchend', () => { if (!sig.isEmpty()) input.value = sig.toDataURL(); });
    });
});

function clearSignature(id) {
    const canvas = document.getElementById('sig_' + id);
    if (canvas && canvas.parentElement._sigPad) canvas.parentElement._sigPad.clear();
    document.getElementById('input_' + id).value = '';
}

function getLocation(id) {
    navigator.geolocation.getCurrentPosition(
        p => document.getElementById(id).value = p.coords.latitude + ', ' + p.coords.longitude,
        () => Swal.fire('Error', 'No se pudo obtener la ubicación.', 'error')
    );
}
</script>

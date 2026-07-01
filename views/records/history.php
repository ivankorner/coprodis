<div class="max-w-3xl mx-auto">
    <div class="flex items-center space-x-4 mb-6">
        <a href="<?= APP_URL ?>/registros/<?= $record->id ?>" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Historial - Registro #<?= $record->id ?></h1>
            <p class="text-sm text-gray-500"><?= $record->form_titulo ?></p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <?php if (empty($changes)): ?>
        <div class="text-center py-12">
            <i class="fas fa-history text-gray-300 text-4xl mb-3"></i>
            <p class="text-gray-500">Sin cambios registrados</p>
        </div>
        <?php else: ?>
        <div class="divide-y divide-gray-200">
            <?php foreach ($changes as $c): ?>
            <div class="px-6 py-4 hover:bg-gray-50">
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 mt-2 bg-blue-500 rounded-full flex-shrink-0"></div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-900"><?= $c->apellido . ' ' . $c->nombre ?></p>
                            <p class="text-xs text-gray-400"><?= date('d/m/Y H:i', strtotime($c->created_at)) ?></p>
                        </div>
                        <p class="text-sm text-gray-500 mt-0.5">Campo: <span class="font-medium"><?= $c->field_label ?? 'N/A' ?></span></p>
                        <div class="mt-2 grid grid-cols-2 gap-4 p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Valor anterior</p>
                                <p class="text-sm text-gray-700 line-through"><?= $c->valor_anterior ?? '(vacío)' ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Valor nuevo</p>
                                <p class="text-sm text-gray-900 font-medium"><?= $c->valor_nuevo ?? '(vacío)' ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

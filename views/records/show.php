<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4">
            <a href="<?= APP_URL ?>/registros" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Registro #<?= $record->id ?></h1>
                <p class="text-sm text-gray-500"><?= $record->form_titulo ?></p>
            </div>
        </div>
        <div class="flex space-x-2">
            <a href="<?= APP_URL ?>/registros/<?= $record->id ?>/editar"
               class="px-4 py-2 bg-amber-100 text-amber-700 rounded-lg hover:bg-amber-200 text-sm font-medium">
                <i class="fas fa-edit mr-1"></i> Editar
            </a>
            <a href="<?= APP_URL ?>/exportar/registros/pdf/<?= $record->id ?>"
               class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">
                <i class="fas fa-file-pdf mr-1"></i> PDF
            </a>
            <?php if ($record->estado === 'archivado'): ?>
            <form action="<?= APP_URL ?>/registros/<?= $record->id ?>/eliminar" method="POST" class="inline">
                <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
                <button type="submit" class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 text-sm font-medium"
                        onclick="event.preventDefault(); confirmSwal('Eliminar registro', '¿Eliminar este registro permanentemente?', () => this.closest('form').submit())">
                    <i class="fas fa-trash mr-1"></i> Eliminar
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <?php
    $personApellido = ''; $personNombre = ''; $personDNI = ''; $personEmail = '';
    foreach ($fields as $f) {
        $label = mb_strtolower(trim($f->etiqueta));
        if ($label === 'apellido') $personApellido = $f->valor ?? '';
        elseif ($label === 'nombre') $personNombre = $f->valor ?? '';
        elseif ($label === 'dni') $personDNI = $f->valor ?? '';
        elseif ($label === 'email') $personEmail = $f->valor ?? '';
    }
    ?>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Apellido:</span>
                    <span class="font-medium ml-1"><?= $personApellido ?: $record->apellido ?></span>
                </div>
                <div>
                    <span class="text-gray-500">Nombre:</span>
                    <span class="font-medium ml-1"><?= $personNombre ?: $record->nombre ?></span>
                </div>
                <div>
                    <span class="text-gray-500">DNI:</span>
                    <span class="font-medium ml-1"><?= $personDNI ?: '-' ?></span>
                </div>
                <div>
                    <span class="text-gray-500">Email:</span>
                    <span class="font-medium ml-1"><?= $personEmail ?: '-' ?></span>
                </div>
                <div>
                    <span class="text-gray-500">Estado:</span>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium ml-1
                        <?= $record->estado === 'activo' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' ?>">
                        <?= ucfirst($record->estado) ?>
                    </span>
                </div>
                <div>
                    <span class="text-gray-500">Fecha:</span>
                    <span class="font-medium ml-1"><?= date('d/m/Y H:i', strtotime($record->created_at)) ?></span>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-4">
            <?php foreach ($fields as $field): ?>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider"><?= $field->etiqueta ?></p>
                <?php if (in_array($field->tipo, ['imagen', 'firma'])): ?>
                    <?php if ($field->valor): ?>
                        <img src="<?= APP_URL ?>/<?= $field->valor ?>" class="mt-1 max-h-40 rounded border" alt="<?= $field->etiqueta ?>">
                    <?php else: ?>
                        <p class="mt-1 text-sm text-gray-400">-</p>
                    <?php endif; ?>
                <?php elseif ($field->tipo === 'archivo' && $field->valor): ?>
                    <a href="<?= APP_URL ?>/<?= $field->valor ?>" target="_blank"
                       class="mt-1 inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-sm hover:bg-blue-100">
                        <i class="fas fa-download mr-1"></i> <?= basename($field->valor) ?>
                    </a>
                <?php else: ?>
                    <p class="mt-1 text-sm text-gray-900"><?= nl2br($field->valor ?? '-') ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!empty($changes)): ?>
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-sm font-semibold text-gray-700">Historial de Cambios</h2>
        </div>
        <div class="divide-y divide-gray-200">
            <?php foreach ($changes as $c): ?>
            <div class="px-6 py-4">
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 mt-2 bg-amber-500 rounded-full flex-shrink-0"></div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-700">
                            <span class="font-medium"><?= $c->apellido . ' ' . $c->nombre ?></span>
                            modificó <span class="font-medium"><?= $c->field_label ?? 'un campo' ?></span>
                        </p>
                        <div class="mt-1 text-xs text-gray-500 space-y-0.5">
                            <?php if ($c->valor_anterior): ?>
                                <p>Anterior: <span class="line-through text-gray-400"><?= $c->valor_anterior ?></span></p>
                            <?php endif; ?>
                            <p>Nuevo: <span class="text-gray-700"><?= $c->valor_nuevo ?? '(vacio)' ?></span></p>
                        </div>
                        <p class="mt-1 text-xs text-gray-400"><?= date('d/m/Y H:i', strtotime($c->created_at)) ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

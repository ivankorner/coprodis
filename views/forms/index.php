<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900">Formularios</h1>
        <a href="<?= APP_URL ?>/formularios/crear"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
            <i class="fas fa-plus mr-2"></i> Nuevo Formulario
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($forms as $f): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-gray-900 truncate"><?= $f->titulo ?></h3>
                    <p class="text-xs text-gray-500 mt-0.5 truncate"><?= $f->descripcion ?? 'Sin descripción' ?></p>
                </div>
                <span class="ml-2 inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                    <?= $f->estado === 'publicado' ? 'bg-green-100 text-green-700' : ($f->estado === 'borrador' ? 'bg-gray-100 text-gray-600' : 'bg-amber-100 text-amber-700') ?>">
                    <?= ucfirst($f->estado) ?>
                </span>
            </div>

            <div class="flex items-center space-x-4 text-xs text-gray-500 mb-4">
                <span><i class="fas fa-list mr-1"></i> <?= $f->total_campos ?> campos</span>
                <span><i class="fas fa-file-alt mr-1"></i> <?= $f->total_registros ?> registros</span>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                <div class="flex space-x-1">
                    <a href="<?= APP_URL ?>/formularios/<?= $f->id ?>/editar"
                       class="p-1.5 text-gray-400 hover:text-blue-600 rounded">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="<?= APP_URL ?>/formularios/<?= $f->id ?>/toggle" method="POST" class="inline">
                        <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
                        <button type="submit" class="p-1.5 text-gray-400 hover:text-amber-600 rounded"
                                title="<?= $f->estado === 'publicado' ? 'Despublicar' : 'Publicar' ?>">
                            <i class="fas <?= $f->estado === 'publicado' ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                        </button>
                    </form>
                    <form action="<?= APP_URL ?>/formularios/<?= $f->id ?>/duplicar" method="POST" class="inline">
                        <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
                        <button type="submit" class="p-1.5 text-gray-400 hover:text-green-600 rounded" title="Duplicar">
                            <i class="fas fa-copy"></i>
                        </button>
                    </form>
                    <form action="<?= APP_URL ?>/formularios/<?= $f->id ?>/eliminar" method="POST" class="inline"
                          onsubmit="event.preventDefault(); confirmSwal('Eliminar formulario', '¿Eliminar este formulario?', () => this.submit())">
                        <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
                <span class="text-xs text-gray-400"><?= date('d/m/Y', strtotime($f->created_at)) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($forms)): ?>
        <div class="col-span-full text-center py-12">
            <i class="fas fa-layer-group text-gray-300 text-4xl mb-3"></i>
            <p class="text-gray-500">No hay formularios creados</p>
        </div>
        <?php endif; ?>
    </div>
</div>

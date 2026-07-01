<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Notificaciones</h1>
        <form action="<?= APP_URL ?>/notificaciones/marcar-todas" method="POST" class="inline">
            <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
            <button type="submit"
                    class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 text-sm font-medium">
                <i class="fas fa-check-double mr-1"></i> Marcar todas como leídas
            </button>
        </form>
    </div>

    <div class="space-y-3">
        <?php foreach ($data as $notif): ?>
        <div class="bg-white rounded-xl shadow-sm border <?= $notif->leido ? 'border-gray-200' : 'border-blue-200 bg-blue-50' ?> p-4 sm:p-5 flex items-start justify-between">
            <div class="flex items-start space-x-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                    <?= $notif->tipo === 'success' ? 'bg-green-100 text-green-600' : ($notif->tipo === 'warning' ? 'bg-amber-100 text-amber-600' : ($notif->tipo === 'error' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600')) ?>">
                    <i class="fas <?= $notif->tipo === 'success' ? 'fa-check-circle' : ($notif->tipo === 'warning' ? 'fa-exclamation-triangle' : ($notif->tipo === 'error' ? 'fa-times-circle' : 'fa-info-circle')) ?>"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-900"><?= $notif->titulo ?></h3>
                    <?php if ($notif->mensaje): ?>
                        <p class="text-sm text-gray-600 mt-0.5"><?= $notif->mensaje ?></p>
                    <?php endif; ?>
                    <p class="text-xs text-gray-400 mt-1"><?= date('d/m/Y H:i', strtotime($notif->created_at)) ?></p>
                </div>
            </div>
            <?php if (!$notif->leido): ?>
            <form action="<?= APP_URL ?>/notificaciones/marcar-leido/<?= $notif->id ?>" method="POST" class="ml-2">
                <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
                <button type="submit" class="p-1 text-gray-400 hover:text-blue-600" title="Marcar como leída">
                    <i class="fas fa-circle text-xs"></i>
                </button>
            </form>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php if (empty($data)): ?>
        <div class="text-center py-12">
            <i class="fas fa-bell text-gray-300 text-4xl mb-3"></i>
            <p class="text-gray-500">No tienes notificaciones</p>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Página <?= $page ?> de <?= $totalPages ?></p>
        <div class="flex space-x-2">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Anterior</a>
            <?php endif; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Siguiente</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

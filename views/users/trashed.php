<div class="space-y-6">
    <div class="flex items-center space-x-4">
        <a href="<?= APP_URL ?>/usuarios" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Usuarios Eliminados</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Eliminado</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-700"><?= $u->apellido . ', ' . $u->nombre ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= $u->email ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($u->deleted_at)) ?></td>
                        <td class="px-6 py-4 text-right">
                            <form action="<?= APP_URL ?>/usuarios/<?= $u->id ?>/restaurar" method="POST" class="inline">
                                <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
                                <button type="submit"
                                        class="px-3 py-1.5 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 text-xs font-medium">
                                    <i class="fas fa-trash-restore mr-1"></i> Restaurar
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                    <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">No hay usuarios eliminados</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

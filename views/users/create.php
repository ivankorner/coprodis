<div class="max-w-2xl mx-auto">
    <div class="flex items-center space-x-4 mb-6">
        <a href="<?= APP_URL ?>/usuarios" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Nuevo Usuario</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
        <form action="<?= APP_URL ?>/usuarios/crear" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Apellido *</label>
                    <input type="text" name="apellido" required maxlength="100"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input type="text" name="nombre" required maxlength="100"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">DNI *</label>
                    <input type="text" name="dni" required maxlength="8" pattern="\d{7,8}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico *</label>
                    <input type="email" name="email" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="text" name="telefono" maxlength="50"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Localidad</label>
                    <input type="text" name="localidad" maxlength="150"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol *</label>
                    <select name="rol_id" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="">Seleccionar...</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r->id ?>"><?= $r->nombre ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-sm text-gray-500 mb-4">
                    <i class="fas fa-info-circle mr-1"></i>
                    Se generará una contraseña temporal y se enviará al correo del usuario.
                </p>
                <div class="flex items-center space-x-4">
                    <button type="submit"
                            class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-sm transition-colors">
                        <i class="fas fa-save mr-2"></i> Crear Usuario
                    </button>
                    <a href="<?= APP_URL ?>/usuarios"
                       class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">
                        Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

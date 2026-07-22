<div class="max-w-2xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Mi Perfil</h1>

    <!-- Edit Profile -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
        <h2 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-200">Información Personal</h2>
        <form action="<?= APP_URL ?>/perfil" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
            <div class="flex items-center space-x-6 mb-6 pb-6 border-b border-gray-200">
                <div class="flex-shrink-0">
                    <?php if (!empty($user->avatar)): ?>
                        <img src="<?= APP_URL ?>/<?= $user->avatar ?>"
                             alt="Foto de perfil"
                             class="w-20 h-20 rounded-full object-cover border-2 border-gray-200">
                    <?php else: ?>
                        <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center text-white text-2xl font-medium">
                            <?= strtoupper(substr($user->nombre, 0, 1)) . strtoupper(substr($user->apellido, 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto de perfil</label>
                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="mt-1 text-xs text-gray-500">JPG, PNG, GIF o WebP. Máximo 10MB.</p>
                    <?php if (!empty($user->avatar)): ?>
                        <label class="inline-flex items-center mt-2 text-sm text-red-600 hover:text-red-700 cursor-pointer">
                            <input type="checkbox" name="_remove_avatar" value="1" class="mr-1.5 rounded border-gray-300 text-red-600 focus:ring-red-500">
                            Eliminar foto actual
                        </label>
                    <?php endif; ?>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                    <input type="text" name="apellido" value="<?= $user->apellido ?>" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="nombre" value="<?= $user->nombre ?>" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">DNI</label>
                    <input type="text" value="<?= $user->dni ?>" disabled
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                    <input type="email" value="<?= $user->email ?>" disabled
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="text" name="telefono" value="<?= $user->telefono ?? '' ?>"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Localidad</label>
                    <input type="text" name="localidad" value="<?= $user->localidad ?? '' ?>"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
            </div>
            <div class="mt-6">
                <p class="text-xs text-gray-500 mb-3"><i class="fas fa-info-circle mr-1"></i> Rol: <?= $user->rol_nombre ?></p>
                <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors">
                    <i class="fas fa-save mr-2"></i> Actualizar Perfil
                </button>
            </div>
        </form>
    </div>

    <!-- Change Password -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
        <h2 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-200">Cambiar Contraseña</h2>
        <form action="<?= APP_URL ?>/perfil/cambiar-password" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña Actual</label>
                    <div class="relative">
                        <input type="password" name="current_password" required
                               class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                        <button type="button" onclick="togglePassword(this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña</label>
                    <div class="relative">
                        <input type="password" name="password" required minlength="8"
                               class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                        <button type="button" onclick="togglePassword(this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" required minlength="8"
                               class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                        <button type="button" onclick="togglePassword(this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit"
                        class="px-6 py-2.5 bg-amber-600 text-white rounded-lg hover:bg-amber-700 text-sm font-medium transition-colors">
                    <i class="fas fa-key mr-2"></i> Cambiar Contraseña
                </button>
            </div>
        </form>
    </div>

    <!-- Sessions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Información de Sesión</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Último acceso:</span>
                <span class="font-medium ml-1"><?= $user->ultimo_acceso ? date('d/m/Y H:i', strtotime($user->ultimo_acceso)) : 'Nunca' ?></span>
            </div>
            <div>
                <span class="text-gray-500">Miembro desde:</span>
                <span class="font-medium ml-1"><?= date('d/m/Y', strtotime($user->created_at)) ?></span>
            </div>
            <div>
                <span class="text-gray-500">Última IP:</span>
                <span class="font-medium ml-1 font-mono"><?= $user->ip_acceso ?? '-' ?></span>
            </div>
        </div>
    </div>
</div>

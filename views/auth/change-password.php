<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Cambio de Contraseña Obligatorio</h2>
        <p class="text-sm text-gray-500 mb-6">Por seguridad, debes cambiar tu contraseña temporal antes de continuar.</p>
        <form action="<?= APP_URL ?>/change-password" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña Actual</label>
                    <div class="relative">
                        <input type="password" name="current_password" required
                               class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <button type="button" onclick="togglePassword(this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña</label>
                    <div class="relative">
                        <input type="password" name="password" required minlength="8"
                               class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <button type="button" onclick="togglePassword(this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nueva Contraseña</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" required minlength="8"
                               class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <button type="button" onclick="togglePassword(this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit"
                        class="w-full bg-blue-600 text-white py-2.5 rounded-lg hover:bg-blue-700 font-medium text-sm transition-colors">
                    Cambiar Contraseña
                </button>
            </div>
        </form>
    </div>
</div>

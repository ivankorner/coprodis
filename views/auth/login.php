<form action="<?= APP_URL ?>/login" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
    <input type="hidden" name="_csrf_token" value="<?= (new \App\Core\Request())->csrfToken() ?>">
    <div class="space-y-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
            <input type="email" name="email" required autofocus autocomplete="email"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
            <div class="relative">
                <input type="password" name="password" required autocomplete="current-password"
                       class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                <button type="button" onclick="togglePassword(this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors" tabindex="-1">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
        <div class="flex items-center justify-between">
            <label class="flex items-center">
                <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-600">Recordar sesión</span>
            </label>
            <a href="<?= APP_URL ?>/forgot-password" class="text-sm text-blue-600 hover:text-blue-800">¿Olvidaste tu contraseña?</a>
        </div>
        <button type="submit"
                class="w-full bg-blue-600 text-white py-2.5 rounded-lg hover:bg-blue-700 font-medium text-sm transition-colors">
            Iniciar Sesión
        </button>
    </div>
</form>

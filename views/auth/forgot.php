<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
    <h2 class="text-lg font-semibold text-gray-900 mb-2">Recuperar Contraseña</h2>
    <p class="text-sm text-gray-500 mb-6">Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>
    <form action="<?= APP_URL ?>/forgot-password" method="POST">
        <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
        <div class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                <input type="email" name="email" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
            </div>
            <button type="submit"
                    class="w-full bg-blue-600 text-white py-2.5 rounded-lg hover:bg-blue-700 font-medium text-sm transition-colors">
                Enviar Enlace
            </button>
            <p class="text-center">
                <a href="<?= APP_URL ?>/login" class="text-sm text-blue-600 hover:text-blue-800">Volver al inicio de sesión</a>
            </p>
        </div>
    </form>
</div>

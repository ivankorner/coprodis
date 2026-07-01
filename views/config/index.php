<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Configuración del Sistema</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
        <form action="<?= APP_URL ?>/configuracion" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">

            <div class="space-y-6">
                <div>
                    <h2 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-200">Información General</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Sistema</label>
                            <input type="text" name="nombre_sistema" value="<?= $config['nombre_sistema'] ?? APP_NAME ?>"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Zona Horaria</label>
                            <select name="zona_horaria"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                                <option value="America/Argentina/Buenos_Aires" <?= ($config['zona_horaria'] ?? '') === 'America/Argentina/Buenos_Aires' ? 'selected' : '' ?>>Argentina (Buenos Aires)</option>
                                <option value="America/Argentina/Cordoba" <?= ($config['zona_horaria'] ?? '') === 'America/Argentina/Cordoba' ? 'selected' : '' ?>>Argentina (Córdoba)</option>
                                <option value="America/Argentina/Mendoza" <?= ($config['zona_horaria'] ?? '') === 'America/Argentina/Mendoza' ? 'selected' : '' ?>>Argentina (Mendoza)</option>
                                <option value="America/Argentina/Salta" <?= ($config['zona_horaria'] ?? '') === 'America/Argentina/Salta' ? 'selected' : '' ?>>Argentina (Salta)</option>
                                <option value="America/Argentina/Jujuy" <?= ($config['zona_horaria'] ?? '') === 'America/Argentina/Jujuy' ? 'selected' : '' ?>>Argentina (Jujuy)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Registros por Página</label>
                            <input type="number" name="registros_por_pagina" value="<?= $config['registros_por_pagina'] ?? 25 ?>"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Logo del Sistema</label>
                            <?php if (!empty($config['logo'])): ?>
                                <div class="mb-2">
                                    <img src="<?= APP_URL ?>/<?= $config['logo'] ?>" class="h-12 rounded" alt="Logo">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="logo" accept="image/*"
                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700">
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-200">Configuración SMTP</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Host SMTP</label>
                            <input type="text" name="smtp_host" value="<?= $config['smtp_host'] ?? '' ?>"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Puerto SMTP</label>
                            <input type="number" name="smtp_port" value="<?= $config['smtp_port'] ?? '587' ?>"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Encriptación</label>
                            <select name="smtp_encryption"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                                <option value="tls" <?= ($config['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                <option value="ssl" <?= ($config['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                <option value="" <?= ($config['smtp_encryption'] ?? '') === '' ? 'selected' : '' ?>>Ninguna</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Usuario SMTP</label>
                            <input type="text" name="smtp_user" value="<?= $config['smtp_user'] ?? '' ?>"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña SMTP</label>
                            <div class="relative">
                                <input type="password" name="smtp_pass" value="<?= $config['smtp_pass'] ?? '' ?>"
                                       class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                                <button type="button" onclick="togglePassword(this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors" tabindex="-1">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Correo Remitente</label>
                            <input type="email" name="smtp_from_email" value="<?= $config['smtp_from_email'] ?? '' ?>"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Remitente</label>
                            <input type="text" name="smtp_from_name" value="<?= $config['smtp_from_name'] ?? 'CO.PRO.DIS' ?>"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200">
                <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-sm transition-colors">
                    <i class="fas fa-save mr-2"></i> Guardar Configuración
                </button>
            </div>
        </form>
    </div>
</div>

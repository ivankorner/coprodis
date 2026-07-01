<div class="max-w-2xl mx-auto">
    <div class="flex items-center space-x-4 mb-6">
        <a href="<?= APP_URL ?>/formularios" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Nuevo Formulario</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
        <form action="<?= APP_URL ?>/formularios/crear" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
                    <input type="text" name="titulo" required maxlength="255"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea name="descripcion" rows="3"
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
                </div>
            </div>
            <div class="mt-8 pt-6 border-t border-gray-200 flex items-center space-x-4">
                <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-sm">
                    <i class="fas fa-save mr-2"></i> Crear Formulario
                </button>
                <a href="<?= APP_URL ?>/formularios"
                   class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

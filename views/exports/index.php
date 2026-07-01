<div x-data="{ format: 'xlsx' }" class="max-w-md mx-auto text-center py-12">
    <i class="fas fa-file-export text-gray-300 text-5xl mb-4"></i>
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Exportar <?= ucfirst($modulo) ?></h1>
    <p class="text-sm text-gray-500 mb-6">Selecciona el formato de exportación</p>

    <div class="flex justify-center space-x-4">
        <form action="<?= APP_URL ?>/exportar/<?= $modulo ?>/excel" method="POST" class="inline">
            <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="format" value="xlsx">
            <button type="submit"
                    class="px-6 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 font-medium text-sm transition-colors">
                <i class="fas fa-file-excel text-lg mr-2"></i> Excel (.xlsx)
            </button>
        </form>
        <form action="<?= APP_URL ?>/exportar/<?= $modulo ?>/csv" method="POST" class="inline">
            <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
            <button type="submit"
                    class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium text-sm transition-colors">
                <i class="fas fa-file-csv text-lg mr-2"></i> CSV
            </button>
        </form>
    </div>

    <a href="javascript:window.print()"
       class="inline-flex items-center mt-4 px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 text-sm font-medium">
        <i class="fas fa-print mr-2"></i> Imprimir
    </a>
</div>

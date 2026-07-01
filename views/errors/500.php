<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Error del servidor</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 h-screen flex items-center justify-center">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-gray-300 mb-4">500</h1>
        <p class="text-xl text-gray-600 mb-6">Error interno del servidor</p>
        <p class="text-sm text-gray-500 mb-6">Ocurrió un error inesperado. Contacta al administrador del sistema.</p>
        <a href="<?= APP_URL ?>/dashboard"
           class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-arrow-left mr-2"></i> Volver al Dashboard
        </a>
    </div>
</body>
</html>

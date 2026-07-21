<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <script>tailwind={config:{darkMode:'class'}}</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/dark-mode.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        [x-cloak] { display: none !important; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body class="h-full bg-gray-50 antialiased" x-data="themeManager()">
    <div class="fixed top-4 right-4 z-50">
        <button @click="toggleDarkMode"
                class="w-9 h-9 flex items-center justify-center rounded-lg bg-white border border-gray-200 shadow-sm text-gray-500 hover:text-gray-700 transition-colors"
                :title="darkMode ? 'Modo claro' : 'Modo oscuro'">
            <i class="fas text-sm" :class="darkMode ? 'fa-sun' : 'fa-moon'"></i>
        </button>
    </div>

    <div class="min-h-screen flex flex-col justify-center items-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="mx-auto h-16 w-16 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg mb-4">
                    <i class="fas fa-wheelchair text-white text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900"><?= APP_NAME ?></h2>
                <p class="mt-1 text-sm text-gray-500">Consejo Provincial de Discapacidad</p>
            </div>

            <?php
            $_flash_error = \App\Core\Session::getFlash('error');
            $_flash_success = \App\Core\Session::getFlash('success');
            ?>

            <?= $content ?? '' ?>
        </div>

        <p class="mt-8 text-center text-xs text-gray-400">
            &copy; <?= date('Y') ?> <?= APP_NAME ?>. Todos los derechos reservados.
        </p>
    </div>

    <script>
        (function() {
            var d = localStorage.getItem('darkMode');
            if (d === 'true' || (!d && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('themeManager', () => ({
                darkMode: document.documentElement.classList.contains('dark'),
                init() {
                    this.$watch('darkMode', val => {
                        document.documentElement.classList.toggle('dark', val);
                        localStorage.setItem('darkMode', val);
                    });
                },
                toggleDarkMode() {
                    this.darkMode = !this.darkMode;
                }
            }));
        });
    </script>

    <script>
        function togglePassword(btn) {
            const input = btn.parentElement.querySelector('input');
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            <?php if ($_flash_success): ?>
            Swal.fire({ icon: 'success', title: 'Éxito', text: '<?= addslashes($_flash_success) ?>', timer: 3000, showConfirmButton: false, toast: true, position: 'top-end' });
            <?php endif; ?>
            <?php if ($_flash_error): ?>
            Swal.fire({ icon: 'error', title: 'Error', text: '<?= addslashes($_flash_error) ?>', timer: 5000, showConfirmButton: false, toast: true, position: 'top-end' });
            <?php endif; ?>
        });
    </script>
</body>
</html>

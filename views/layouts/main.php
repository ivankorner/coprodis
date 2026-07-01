<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - <?= $titulo ?? 'Dashboard' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        [x-cloak] { display: none !important; }
        @media print { .no-print { display: none !important; } }
        .sidebar-transition { transition: all 0.3s ease; }
    </style>
</head>
<body class="h-full bg-gray-50 antialiased" x-data="{ sidebarOpen: window.innerWidth >= 1024, mobileMenuOpen: false }"
      @resize.window="sidebarOpen = window.innerWidth >= 1024">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-30 w-64 bg-gray-900 sidebar-transition no-print"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-64'">
            <div class="flex items-center justify-between h-16 px-6 bg-gray-800">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-wheelchair text-white text-sm"></i>
                    </div>
                    <span class="text-white font-semibold text-sm"><?= APP_NAME ?></span>
                </div>
                <button @click="sidebarOpen = false" class="text-gray-400 hover:text-white lg:hidden">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <nav class="mt-2 px-3 space-y-1 overflow-y-auto" style="height: calc(100vh - 4rem);">
                <a href="<?= APP_URL ?>/dashboard"
                   class="flex items-center px-3 py-2.5 text-sm rounded-lg transition-colors <?= strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                    <i class="fas fa-chart-pie w-5 text-center mr-3"></i>
                    Dashboard
                </a>

                <?php if (in_array(\App\Core\Session::userRole(), ['super_usuario', 'administrador'])): ?>
                <a href="<?= APP_URL ?>/usuarios"
                   class="flex items-center px-3 py-2.5 text-sm rounded-lg transition-colors <?= strpos($_SERVER['REQUEST_URI'], '/usuarios') !== false ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                    <i class="fas fa-users w-5 text-center mr-3"></i>
                    Usuarios
                </a>
                <?php endif; ?>

                <?php if (\App\Core\Session::userRole() === 'super_usuario'): ?>
                <a href="<?= APP_URL ?>/formularios"
                   class="flex items-center px-3 py-2.5 text-sm rounded-lg transition-colors <?= strpos($_SERVER['REQUEST_URI'], '/formularios') !== false ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                    <i class="fas fa-layer-group w-5 text-center mr-3"></i>
                    Formularios
                </a>
                <?php endif; ?>

                <a href="<?= APP_URL ?>/registros"
                   class="flex items-center px-3 py-2.5 text-sm rounded-lg transition-colors <?= strpos($_SERVER['REQUEST_URI'], '/registros') !== false ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                    <i class="fas fa-file-alt w-5 text-center mr-3"></i>
                    Registros
                </a>

                <a href="<?= APP_URL ?>/notificaciones"
                   class="flex items-center px-3 py-2.5 text-sm rounded-lg transition-colors relative <?= strpos($_SERVER['REQUEST_URI'], '/notificaciones') !== false ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                    <i class="fas fa-bell w-5 text-center mr-3"></i>
                    Notificaciones
                    <span x-data="{ count: 0 }" x-init="fetch('<?= APP_URL ?>/api/notificaciones/no-leidas').then(r=>r.json()).then(d=>count=d.count)"
                          x-show="count > 0" x-text="count"
                          class="ml-auto bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5 min-w-[20px] text-center">
                    </span>
                </a>

                <?php if (in_array(\App\Core\Session::userRole(), ['super_usuario', 'administrador'])): ?>
                <div class="pt-4 mt-4 border-t border-gray-700">
                    <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Analítica</p>
                </div>

                <a href="<?= APP_URL ?>/reportes"
                   class="flex items-center px-3 py-2.5 text-sm rounded-lg transition-colors <?= strpos($_SERVER['REQUEST_URI'], '/reportes') !== false ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                    <i class="fas fa-chart-bar w-5 text-center mr-3"></i>
                    Reportes
                </a>
                <?php endif; ?>

                <?php if (\App\Core\Session::userRole() === 'super_usuario'): ?>
                <div class="pt-4 mt-4 border-t border-gray-700">
                    <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Administración</p>
                </div>

                <a href="<?= APP_URL ?>/auditoria"
                   class="flex items-center px-3 py-2.5 text-sm rounded-lg transition-colors <?= strpos($_SERVER['REQUEST_URI'], '/auditoria') !== false ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                    <i class="fas fa-history w-5 text-center mr-3"></i>
                    Auditoría
                </a>

                <a href="<?= APP_URL ?>/configuracion"
                   class="flex items-center px-3 py-2.5 text-sm rounded-lg transition-colors <?= strpos($_SERVER['REQUEST_URI'], '/configuracion') !== false ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                    <i class="fas fa-cog w-5 text-center mr-3"></i>
                    Configuración
                </a>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- Overlay for mobile -->
        <div x-show="mobileMenuOpen" @click="sidebarOpen = false; mobileMenuOpen = false"
             class="fixed inset-0 z-20 bg-black bg-opacity-50 lg:hidden no-print"
             x-cloak></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-screen"
             :class="sidebarOpen ? 'lg:ml-64' : ''">
            <!-- Top Navbar -->
            <header class="sticky top-0 z-10 bg-white border-b border-gray-200 shadow-sm no-print">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6">
                    <div class="flex items-center">
                        <button @click="sidebarOpen = !sidebarOpen; mobileMenuOpen = !mobileMenuOpen"
                                class="text-gray-500 hover:text-gray-700 focus:outline-none lg:mr-4">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h1 class="hidden sm:block text-lg font-semibold text-gray-800 ml-4"><?= $titulo ?? 'Dashboard' ?></h1>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="relative text-gray-500 hover:text-gray-700">
                                <i class="fas fa-bell text-lg"></i>
                                <span x-data="{ count: 0 }"
                                      x-init="fetch('<?= APP_URL ?>/api/notificaciones/no-leidas').then(r=>r.json()).then(d=>count=d.count)"
                                      x-show="count > 0" x-text="count"
                                      class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[10px] rounded-full px-1 py-0.5 min-w-[16px] text-center">
                                </span>
                            </button>

                            <div x-show="open" @click.outside="open = false"
                                 class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden"
                                 x-cloak>
                                <div class="p-3 border-b border-gray-100">
                                    <p class="text-sm font-semibold text-gray-700">Notificaciones</p>
                                </div>
                                <div x-data="{ notifs: [] }"
                                     x-init="fetch('<?= APP_URL ?>/api/notificaciones/ultimas').then(r=>r.json()).then(d=>notifs=d.notifications)">
                                    <template x-if="notifs.length === 0">
                                        <p class="text-sm text-gray-500 text-center py-6">Sin notificaciones</p>
                                    </template>
                                    <template x-for="n in notifs" :key="n.id">
                                        <a :href="'<?= APP_URL ?>/notificaciones'" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100"
                                           :class="{'bg-blue-50': !n.leido}">
                                            <p class="text-sm font-medium" x-text="n.titulo"></p>
                                            <p class="text-xs text-gray-500 mt-0.5" x-text="n.created_at"></p>
                                        </a>
                                    </template>
                                </div>
                                <a href="<?= APP_URL ?>/notificaciones"
                                   class="block px-4 py-2.5 text-center text-sm text-blue-600 hover:bg-blue-50 font-medium">
                                    Ver todas
                                </a>
                            </div>
                        </div>

                        <!-- User Menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="flex items-center space-x-2 text-gray-700 hover:text-gray-900">
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                    <?= strtoupper(substr($currentUser->nombre ?? 'U', 0, 1)) . strtoupper(substr($currentUser->apellido ?? 'U', 0, 1)) ?>
                                </div>
                                <span class="hidden sm:block text-sm font-medium">
                                    <?= ($currentUser->nombre ?? '') . ' ' . ($currentUser->apellido ?? '') ?>
                                </span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>

                            <div x-show="open" @click.outside="open = false"
                                 class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden"
                                 x-cloak>
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <p class="text-sm font-medium text-gray-900"><?= ($currentUser->nombre ?? '') . ' ' . ($currentUser->apellido ?? '') ?></p>
                                    <p class="text-xs text-gray-500"><?= ($currentUser->email ?? '') ?></p>
                                </div>
                                <a href="<?= APP_URL ?>/perfil" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-user mr-2"></i> Mi Perfil
                                </a>
                                <div class="border-t border-gray-100">
                                    <a href="<?= APP_URL ?>/logout"
                                       class="block px-4 py-2.5 text-sm text-red-600 hover:bg-red-50"
                                       onclick="event.preventDefault(); confirmSwal('Cerrar sesión', '¿Estás seguro?', () => location.href=this.href)">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <?php
                $_flash_success = \App\Core\Session::getFlash('success');
                $_flash_error = \App\Core\Session::getFlash('error');
                ?>

                <?= $content ?? '' ?>
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 px-6 py-3 no-print">
                <p class="text-center text-xs text-gray-400">
                    &copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash; Consejo Provincial de Discapacidad
                </p>
            </footer>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('notificationBadge', () => ({
                count: 0,
                init() {
                    fetch('<?= APP_URL ?>/api/notificaciones/no-leidas')
                        .then(r => r.json())
                        .then(d => this.count = d.count);
                }
            }));
        });

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

        function confirmSwal(title, text, callback) {
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí',
                cancelButtonText: 'Cancelar'
            }).then(result => { if (result.isConfirmed) callback(); });
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

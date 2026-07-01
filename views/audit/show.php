<div class="max-w-4xl mx-auto">
    <div class="flex items-center space-x-4 mb-6">
        <a href="<?= APP_URL ?>/auditoria" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detalle de Auditoría #<?= $audit->id ?></h1>
            <p class="text-sm text-gray-500"><?= date('d/m/Y H:i:s', strtotime($audit->created_at)) ?></p>
        </div>
    </div>

    <div class="grid gap-6">
        <!-- Severity Banner -->
        <?php
        $severityConfig = [
            'danger' => ['bg-red-50 border-red-200 text-red-800', 'bg-red-500', 'fa-exclamation-triangle', 'Crítico'],
            'warning' => ['bg-amber-50 border-amber-200 text-amber-800', 'bg-amber-500', 'fa-exclamation-circle', 'Advertencia'],
            'success' => ['bg-green-50 border-green-200 text-green-800', 'bg-green-500', 'fa-check-circle', 'Éxito'],
            'info' => ['bg-blue-50 border-blue-200 text-blue-800', 'bg-blue-500', 'fa-info-circle', 'Información'],
        ];
        $sev = $severityConfig[$audit->tipo_audit ?? 'info'];
        ?>
        <div class="rounded-xl border-2 p-4 <?= $sev[0] ?> flex items-center space-x-3">
            <i class="fas <?= $sev[2] ?> text-lg"></i>
            <div>
                <p class="font-semibold capitalize"><?= $sev[3] ?></p>
                <p class="text-sm opacity-75"><?= $audit->descripcion ?? 'Sin descripción' ?></p>
            </div>
        </div>

        <!-- Main Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">Información General</h2>
            </div>
            <div class="p-6">
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</p>
                        <p class="mt-1 font-medium text-gray-900"><?= ($audit->apellido ?? 'Sistema') . ' ' . ($audit->nombre ?? '') ?></p>
                        <?php if ($audit->email): ?>
                            <p class="text-gray-500"><?= $audit->email ?></p>
                        <?php endif; ?>
                        <?php if ($audit->user_rol): ?>
                            <p class="text-xs text-gray-400 mt-0.5 capitalize"><?= $audit->user_rol ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</p>
                        <p class="mt-1">
                            <span class="inline-flex px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                <?= $audit->accion ?>
                            </span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Módulo</p>
                        <p class="mt-1 font-medium text-gray-900 capitalize"><?= $audit->modulo ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">IP</p>
                        <p class="mt-1 font-mono text-gray-700"><?= $audit->ip ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha y Hora</p>
                        <p class="mt-1 text-gray-700"><?= date('d/m/Y', strtotime($audit->created_at)) ?></p>
                        <p class="text-gray-500"><?= date('H:i:s', strtotime($audit->created_at)) ?> hs</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">ID Auditoría</p>
                        <p class="mt-1 font-mono text-gray-700">#<?= $audit->id ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Agent -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">Información del Cliente</h2>
            </div>
            <div class="p-6">
                <div class="text-sm">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">User Agent</p>
                    <p class="mt-1 text-gray-700 break-words font-mono text-xs bg-gray-50 p-3 rounded-lg border border-gray-200"><?= $audit->user_agent ?? 'No registrado' ?></p>
                </div>
                <?php
                $ua = $audit->user_agent ?? '';
                $browser = 'Desconocido';
                $os = 'Desconocido';
                if (preg_match('/Firefox\/([\d.]+)/', $ua)) $browser = 'Firefox';
                elseif (preg_match('/Chrome\/([\d.]+)/', $ua)) $browser = 'Chrome';
                elseif (preg_match('/Safari\/([\d.]+)/', $ua)) $browser = 'Safari';
                elseif (preg_match('/MSIE|Trident/', $ua)) $browser = 'Internet Explorer';
                elseif (preg_match('/Edge\/([\d.]+)/', $ua)) $browser = 'Edge';
                if (preg_match('/Windows NT ([\d.]+)/', $ua)) $os = 'Windows';
                elseif (preg_match('/Mac OS X ([\d_]+)/', $ua)) $os = 'macOS';
                elseif (preg_match('/Linux/', $ua)) $os = 'Linux';
                elseif (preg_match('/Android/', $ua)) $os = 'Android';
                elseif (preg_match('/iPhone|iPad/', $ua)) $os = 'iOS';
                ?>
                <div class="grid grid-cols-2 gap-4 mt-4 text-sm">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Navegador</p>
                        <p class="mt-1 text-gray-700"><?= $browser ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Sistema Operativo</p>
                        <p class="mt-1 text-gray-700"><?= $os ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Entidad Relacionada -->
        <?php if ($audit->entidad): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">Entidad Relacionada</h2>
            </div>
            <div class="p-6 text-sm">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</p>
                        <p class="mt-1 text-gray-700 capitalize"><?= $audit->entidad ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">ID</p>
                        <p class="mt-1 font-mono text-gray-700">#<?= $audit->entidad_id ?></p>
                    </div>
                </div>
                <?php if ($audit->entidad === 'record'): ?>
                <div class="mt-4">
                    <a href="<?= APP_URL ?>/registros/<?= $audit->entidad_id ?>"
                       class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 text-sm font-medium">
                        <i class="fas fa-external-link-alt mr-2"></i> Ver Registro #<?= $audit->entidad_id ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Detalles (JSON) -->
        <?php if ($audit->detalles && is_array($audit->detalles)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">Detalles de la Operación</h2>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <?php foreach ($audit->detalles as $key => $val): ?>
                    <div class="grid grid-cols-3 gap-2 text-sm py-2 border-b border-gray-100 last:border-0">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider col-span-1"><?= $key ?></dt>
                        <dd class="text-gray-700 col-span-2"><?= is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : ($val ?? '-') ?></dd>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php elseif ($audit->detalles && is_string($audit->detalles)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">Detalles Adicionales</h2>
            </div>
            <div class="p-6">
                <pre class="text-sm text-gray-600 bg-gray-50 rounded-lg p-4 border border-gray-200 overflow-x-auto max-h-64"><?= $audit->detalles ?></pre>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

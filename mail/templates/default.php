<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 6px rgba(0,0,0,0.07);">
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#1e3a5f,#2563eb);padding:30px 40px;text-align:center;">
                            <h1 style="color:#ffffff;margin:0;font-size:24px;font-weight:700;"><?= APP_NAME ?></h1>
                            <p style="color:rgba(255,255,255,0.85);margin:8px 0 0;font-size:14px;">
                                Consejo Provincial de Discapacidad
                            </p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:40px;">
                            <h2 style="color:#1f2937;font-size:20px;margin:0 0 8px;">Hola, <?= $nombre ?? '' ?></h2>
                            <p style="color:#4b5563;font-size:15px;line-height:1.6;margin:0 0 20px;">
                                <?= $mensaje ?? '' ?>
                            </p>

                            <?php if (!empty($detalle)): ?>
                                <p style="color:#4b5563;font-size:14px;line-height:1.5;margin:0 0 16px;">
                                    <?= $detalle ?>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($items)): ?>
                                <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb;border-radius:8px;padding:16px;margin-bottom:24px;">
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td style="padding:6px 12px;font-size:13px;color:#6b7280;width:120px;vertical-align:top;">
                                                <?= $item['label'] ?>:
                                            </td>
                                            <td style="padding:6px 12px;font-size:14px;color:#1f2937;font-weight:600;">
                                                <?= $item['valor'] ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            <?php endif; ?>

                            <?php if (!empty($accion_url)): ?>
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td align="center" style="padding:8px 0 24px;">
                                            <a href="<?= $accion_url ?>"
                                               style="display:inline-block;background-color:#2563eb;color:#ffffff;text-decoration:none;padding:14px 32px;border-radius:8px;font-size:15px;font-weight:600;">
                                                <?= $accion_texto ?? 'Continuar' ?>
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            <?php endif; ?>

                            <?php if (!empty($nota)): ?>
                                <p style="color:#9ca3af;font-size:12px;line-height:1.5;margin:16px 0 0;border-top:1px solid #e5e7eb;padding-top:16px;">
                                    <?= $nota ?>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f9fafb;padding:20px 40px;text-align:center;border-top:1px solid #e5e7eb;">
                            <p style="color:#6b7280;font-size:12px;margin:0;">
                                &copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash; Todos los derechos reservados.
                            </p>
                            <p style="color:#9ca3af;font-size:11px;margin:4px 0 0;">
                                Este es un mensaje automático, por favor no respondas a este correo.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

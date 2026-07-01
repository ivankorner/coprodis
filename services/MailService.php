<?php

namespace App\Services;

use App\Core\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private static ?PHPMailer $mailer = null;

    private static function getMailer(): PHPMailer
    {
        if (self::$mailer === null) {
            self::$mailer = new PHPMailer(true);
            self::$mailer->isSMTP();
            self::$mailer->Host = SMTP_HOST;
            self::$mailer->Port = SMTP_PORT;
            self::$mailer->SMTPAuth = true;
            self::$mailer->Username = SMTP_USER;
            self::$mailer->Password = SMTP_PASS;
            self::$mailer->SMTPSecure = SMTP_ENCRYPTION;
            self::$mailer->CharSet = 'UTF-8';
            self::$mailer->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            self::$mailer->isHTML(true);
        }
        return self::$mailer;
    }

    public static function send(string $to, string $subject, string $body): bool
    {
        try {
            $mailer = self::getMailer();
            $mailer->clearAddresses();
            $mailer->addAddress($to);
            $mailer->Subject = $subject;
            $mailer->msgHTML($body);

            $result = $mailer->send();

            self::log($to, $subject, true);
            return $result;
        } catch (Exception $e) {
            self::log($to, $subject, false, $mailer->ErrorInfo ?? $e->getMessage());
            return false;
        }
    }

    public static function sendWithTemplate(string $to, string $subject, array $data, string $template): bool
    {
        $body = self::renderTemplate($template, $data);
        return self::send($to, $subject, $body);
    }

    public static function sendWelcome(string $to, string $nombre, string $password): bool
    {
        return self::sendWithTemplate($to, 'Bienvenido a COPRODIS', [
            'nombre' => $nombre,
            'mensaje' => 'Te damos la bienvenida al sistema COPRODIS.',
            'detalle' => "Tu cuenta ha sido creada exitosamente. Tus credenciales de acceso son:",
            'items' => [
                ['label' => 'Usuario', 'valor' => $to],
                ['label' => 'Contraseña temporal', 'valor' => $password],
            ],
            'accion_url' => APP_URL . '/login',
            'accion_texto' => 'Iniciar Sesión',
            'nota' => 'Por seguridad, deberás cambiar tu contraseña en el primer inicio de sesión.',
        ], 'default');
    }

    public static function sendPasswordReset(string $to, string $nombre, string $token): bool
    {
        $url = APP_URL . '/reset-password/' . $token;
        return self::sendWithTemplate($to, 'Recuperación de Contraseña - COPRODIS', [
            'nombre' => $nombre,
            'mensaje' => 'Has solicitado restablecer tu contraseña en el sistema COPRODIS.',
            'detalle' => 'Haz clic en el siguiente enlace para crear una nueva contraseña:',
            'accion_url' => $url,
            'accion_texto' => 'Restablecer Contraseña',
            'nota' => 'Este enlace expirará en 1 hora. Si no solicitaste este cambio, ignora este mensaje.',
        ], 'default');
    }

    public static function sendNewPassword(string $to, string $nombre, string $password): bool
    {
        return self::sendWithTemplate($to, 'Contraseña Restablecida - COPRODIS', [
            'nombre' => $nombre,
            'mensaje' => 'Tu contraseña ha sido restablecida exitosamente.',
            'detalle' => 'A continuación, tus nuevas credenciales de acceso:',
            'items' => [
                ['label' => 'Usuario', 'valor' => $to],
                ['label' => 'Nueva contraseña', 'valor' => $password],
            ],
            'accion_url' => APP_URL . '/login',
            'accion_texto' => 'Iniciar Sesión',
            'nota' => 'Te recomendamos cambiar esta contraseña después de iniciar sesión.',
        ], 'default');
    }

    public static function sendRegistrationConfirmation(
        string $to,
        string $nombreCliente,
        string $formTitulo,
        array $datosRegistro
    ): bool {
        $items = [];
        foreach ($datosRegistro as $label => $valor) {
            $items[] = ['label' => $label, 'valor' => $valor];
        }

        return self::sendWithTemplate($to, "Registro confirmado: {$formTitulo} - " . APP_NAME, [
            'nombre' => $nombreCliente,
            'mensaje' => "Te confirmamos que tu registro en el formulario \"{$formTitulo}\" ha sido recibido correctamente.",
            'detalle' => 'Datos registrados:',
            'items' => $items,
            'nota' => 'Este es un mensaje automático generado por el sistema. No respondas a este correo.',
        ], 'default');
    }

    public static function sendNotification(string $to, string $nombre, string $titulo, string $mensaje): bool
    {
        return self::sendWithTemplate($to, $titulo . ' - COPRODIS', [
            'nombre' => $nombre,
            'mensaje' => $mensaje,
            'accion_url' => APP_URL . '/notificaciones',
            'accion_texto' => 'Ver Notificaciones',
        ], 'default');
    }

    private static function renderTemplate(string $template, array $data): string
    {
        $file = BASE_PATH . "/mail/templates/{$template}.php";
        if (!file_exists($file)) {
            $file = BASE_PATH . '/mail/templates/default.php';
        }
        extract($data);
        ob_start();
        require $file;
        return ob_get_clean();
    }

    private static function log(string $to, string $subject, bool $success, string $error = null): void
    {
        $db = Database::getInstance();
        $logFile = BASE_PATH . '/storage/logs/mail.log';
        $status = $success ? 'ENVIADO' : 'FALLIDO';
        $errorMsg = $error ? " - Error: {$error}" : '';
        $logLine = "[" . date('Y-m-d H:i:s') . "] {$status} - Para: {$to} - Asunto: {$subject}{$errorMsg}" . PHP_EOL;
        file_put_contents($logFile, $logLine, FILE_APPEND);
    }
}

<style>
    body { font-family: 'Helvetica', 'Arial', sans-serif; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { padding: 8px 12px; border: 1px solid #ddd; text-align: left; }
    th { background-color: #f3f4f6; font-size: 10px; text-transform: uppercase; }
    td { font-size: 11px; }
</style>

<h1 style="font-size: 18px; text-align: center;"><?= $title ?? 'Documento' ?></h1>
<hr style="margin: 20px 0;">

<table>
    <?php foreach ($fields as $field): ?>
    <tr>
        <th style="width: 30%;"><?= $field->etiqueta ?></th>
        <td><?= $field->valor ?? '-' ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<p style="text-align: center; color: #999; font-size: 10px; margin-top: 30px;">
    Generado por <?= APP_NAME ?> - <?= date('d/m/Y H:i') ?>
</p>

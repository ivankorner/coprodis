<style>
    body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; }
    h1 { font-size: 16px; text-align: center; margin-bottom: 5px; }
    .subtitle { text-align: center; color: #666; font-size: 10px; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th { background-color: #1F2937; color: #fff; padding: 6px 8px; text-align: left; font-size: 8px; text-transform: uppercase; }
    td { padding: 5px 8px; border: 1px solid #ddd; }
    tr:nth-child(even) td { background-color: #f9fafb; }
    .footer { text-align: center; color: #999; font-size: 8px; margin-top: 20px; }
</style>

<h1><?= $title ?></h1>
<p class="subtitle">Generado el <?= date('d/m/Y H:i') ?></p>

<table>
    <thead>
        <tr>
            <?php foreach ($headers as $header): ?>
            <th><?= $header ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data as $row): ?>
        <tr>
            <?php foreach ($row as $cell): ?>
            <td><?= $cell ?? '-' ?></td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p class="footer">Generado por <?= APP_NAME ?> - <?= date('d/m/Y H:i') ?></p>

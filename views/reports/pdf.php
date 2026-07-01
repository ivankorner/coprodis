<style>
    body { font-family: 'Helvetica','Arial',sans-serif; font-size: 10pt; color: #333; }
    h1 { font-size: 16pt; color: #1e3a5f; margin-bottom: 5px; }
    h2 { font-size: 13pt; color: #1e3a5f; margin-bottom: 3px; }
    hr { border: none; border-top: 1px solid #ccc; margin: 10px 0; }
    .header { text-align: center; margin-bottom: 15px; }
    .header h1 { margin: 5px 0 2px; }
    .header p { font-size: 8pt; color: #888; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th { background: #1e3a5f; color: #fff; padding: 6px 8px; text-align: left; font-size: 9pt; }
    td { padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 9pt; }
    tr:nth-child(even) td { background: #f9fafb; }
    .stat-box { display: inline-block; width: 45%; margin: 5px 2%; padding: 8px; background: #f0f4f8; border-radius: 4px; text-align: center; }
    .stat-box .num { font-size: 18pt; font-weight: bold; color: #1e3a5f; }
    .stat-box .label { font-size: 8pt; color: #666; }
    .footer { text-align: center; font-size: 7pt; color: #aaa; margin-top: 20px; border-top: 1px solid #ddd; padding-top: 8px; }
</style>
<div class="header">
    <h1><?= $title ?? APP_NAME ?></h1>
    <p>Generado: <?= date('d/m/Y H:i') ?></p>
</div>
<hr>

<?php if (isset($stats)): ?>
<h2>Resumen Global</h2>
<div>
    <div class="stat-box">
        <div class="num"><?= number_format($stats['total']) ?></div>
        <div class="label">Total Registros</div>
    </div>
</div>
<?php endif; ?>

<?php if (isset($formsStats)): ?>
<h2>Registros por Formulario</h2>
<table>
    <tr><th>Formulario</th><th align="right">Registros</th></tr>
    <?php foreach ($formsStats as $fs): ?>
    <tr>
        <td><?= $fs->titulo ?></td>
        <td align="right"><?= number_format($fs->total) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<?php if (isset($form)): ?>
<h2>Formulario: <?= $form->titulo ?></h2>
<?php if (isset($fields)): ?>
<table>
    <tr><th>Campo</th><th>Valor</th></tr>
    <?php foreach ($fields as $ff): ?>
    <tr><td><?= $ff->etiqueta ?? $ff->nombre ?></td><td><?= $ff->valor ?? '' ?></td></tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<?php endif; ?>

<div class="footer">
    <?= APP_NAME ?> &mdash; Consejo Provincial de Discapacidad &mdash; Página 1/1
</div>

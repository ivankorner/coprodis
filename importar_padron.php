<?php

declare(strict_types=1);

define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('STORAGE_PATH', BASE_PATH . '/storage');

require_once BASE_PATH . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

require_once CONFIG_PATH . '/app.php';
require_once CONFIG_PATH . '/database.php';

use App\Core\Database;
use PhpOffice\PhpSpreadsheet\IOFactory;

$db = Database::getInstance();

// ============================================
// CONFIGURACIÓN
// ============================================
$FORM_ID = 1;
$USER_ID = 1;
$EXCEL_FILE = '/Users/ivanmaximilianokorner/Desktop/coprodis sistema/PADRON.xlsx';
$DNI_FIELD_ID = 17391;

// ============================================
// CARGAR CAMPOS DEL FORMULARIO
// ============================================
$formFields = $db->fetchAll(
    "SELECT id, tipo, nombre, etiqueta, opciones FROM form_fields WHERE form_id = :form_id AND deleted_at IS NULL ORDER BY orden",
    ['form_id' => $FORM_ID]
);

$fieldsById = [];
foreach ($formFields as $f) {
    $fieldsById[$f->id] = $f;
}

// ============================================
// MAPEO: columna Excel → field_id
// clave = nombre columna Excel, valor = [field_id, 'normalizer_func']
// ============================================
$mapping = [];

function addMapping(array &$m, string $excelCol, int $fieldId, ?callable $normalizer = null): void
{
    $m[$excelCol] = ['field_id' => $fieldId, 'normalizer' => $normalizer];
}

// Normalizadores básicos
$sn = function ($v) { // sí/no normalizer
    if ($v === null || trim($v) === '') return null;
    $v = trim($v);
    $upper = mb_strtoupper($v);
    if ($upper === 'SI') return 'Sí';
    if ($upper === 'NO') return 'No';
    if ($upper === 'TIENE') return 'Sí';
    if ($upper === 'NO TIENE') return 'No';
    return $v;
};

$sexoN = function ($v) {
    if ($v === null || trim($v) === '') return null;
    $v = trim($v);
    $upper = mb_strtoupper($v);
    if ($upper === 'MASCULINO') return 'Masculino';
    if ($upper === 'FEMENINO') return 'Femenino';
    return $v;
};

$zonaN = function ($v) {
    if ($v === null || trim($v) === '') return null;
    $v = trim($v);
    $upper = mb_strtoupper($v);
    if ($upper === 'URBANA') return 'Urbana';
    if ($upper === 'RURAL') return 'Rural';
    if ($upper === 'PERIURBANA') return 'Periurbana';
    return $v;
};

$cudN = function ($v) {
    if ($v === null || trim($v) === '') return null;
    $v = trim($v);
    $upper = mb_strtoupper($v);
    if ($upper === 'TIENE') return 'Sí';
    if ($upper === 'NO' || $upper === 'NO TIENE') return 'No';
    if ($upper === 'EN TRAMITE' || $upper === 'EN TRÁMITE') return 'En trámite';
    if ($upper === 'VENCIDO') return 'Vencido';
    return $v;
};

$pensionN = function ($v) {
    if ($v === null || trim($v) === '') return null;
    $v = trim($v);
    $upper = mb_strtoupper($v);
    if ($upper === 'SI') return 'Sí';
    if ($upper === 'NO') return 'No';
    if ($upper === 'EN TRAMITE' || $upper === 'EN TRÁMITE') return 'En trámite';
    return $v;
};

$estadoN = function ($v) {
    if ($v === null || trim($v) === '') return null;
    $v = trim($v);
    $upper = mb_strtoupper($v);
    if ($upper === 'ACTIVO') return 'Activo';
    if ($upper === 'FINALIZADO') return 'Finalizado';
    if ($upper === 'BAJA') return 'Baja';
    return $v;
};

$viviendaN = function ($v) {
    if ($v === null || trim($v) === '') return null;
    $v = trim($v);
    $upper = mb_strtoupper($v);
    if (in_array($upper, ['PROPIA', 'PROPIO'])) return 'Propia';
    if (in_array($upper, ['ALQUILADA', 'ALQUILADO'])) return 'Alquilada';
    if ($upper === 'CEDIDA' || $upper === 'CEDIDO') return 'Cedida';
    if ($upper === 'PRESTADA' || $upper === 'PRESTADO') return 'Prestada';
    return 'Otra';
};

$techoN = function ($v) {
    if ($v === null || trim($v) === '') return null;
    $v = trim($v);
    $upper = mb_strtoupper($v);
    if ($upper === 'ZINC') return 'Zinc';
    if ($upper === 'CHAPA') return 'Chapa';
    if ($upper === 'LOSA') return 'Losa';
    return 'Otro';
};

$pisoN = function ($v) {
    if ($v === null || trim($v) === '') return null;
    $v = trim($v);
    $upper = mb_strtoupper($v);
    if (mb_strpos($upper, 'CERAM') !== false || $upper === 'ALISADO') return 'Cerámico';
    if ($upper === 'CEMENTO') return 'Cemento';
    if ($upper === 'TIERRA') return 'Tierra';
    return 'Otro';
};

$banoN = function ($v) {
    if ($v === null || trim($v) === '') return null;
    $v = trim($v);
    $upper = mb_strtoupper($v);
    if (mb_strpos($upper, 'INSTAL') !== false) return 'Instalado';
    if ($upper === 'LETRINA') return 'Letrina';
    return 'Otro';
};

$construccionN = function ($v) {
    if ($v === null || trim($v) === '') return null;
    $v = trim($v);
    $upper = mb_strtoupper($v);
    if ($upper === 'MATERIAL') return 'Material';
    if ($upper === 'MADERA') return 'Madera';
    if (mb_strpos($upper, 'MIX') !== false) return 'Mixta';
    return 'Otro';
};

$parentescoN = function ($v) {
    if ($v === null || trim($v) === '') return null;
    $v = trim($v);
    $upper = mb_strtoupper($v);
    if (mb_strpos($upper, 'MADRE') !== false) return 'Madre';
    if (mb_strpos($upper, 'PADRE') !== false) return 'Padre';
    if ($upper === 'TUTOR' || $upper === 'TUTORA') return 'Tutor';
    if (mb_strpos($upper, 'HERMAN') !== false) return 'Hermano';
    if (mb_strpos($upper, 'CONYUGE') !== false || mb_strpos($upper, 'CÓNYUGE') !== false || $upper === 'ESPOSO' || $upper === 'ESPOSA') return 'Cónyuge';
    return 'Otro';
};

$nivelEducN = function ($v) {
    if ($v === null || trim($v) === '') return null;
    $v = trim($v);
    $upper = mb_strtoupper($v);
    if (mb_strpos($upper, 'SIN ESCOLAR') !== false) return 'Sin escolarización';
    if (mb_strpos($upper, 'INICIAL') !== false || mb_strpos($upper, 'JARDIN') !== false) return 'Inicial';
    if (mb_strpos($upper, 'PRIMARIO INCOMPLETO') !== false || mb_strpos($upper, 'PRIMARIO INC') !== false) return 'Primario incompleto';
    if (mb_strpos($upper, 'PRIMARIO COMPLETO') !== false || $upper === 'NIVEL PRIMARIO' || $upper === 'PRIMARIO') return 'Primario completo';
    if (mb_strpos($upper, 'SECUNDARIO INCOMPLETO') !== false || mb_strpos($upper, 'SECUNDARIO INC') !== false) return 'Secundario incompleto';
    if (mb_strpos($upper, 'SECUNDARIO COMPLETO') !== false || $upper === 'NIVEL SECUNDARIO' || $upper === 'SECUNDARIO') return 'Secundario completo';
    if (mb_strpos($upper, 'TERCIARIO INCOMPLETO') !== false || mb_strpos($upper, 'TERCIARIO INC') !== false) return 'Terciario incompleto';
    if (mb_strpos($upper, 'TERCIARIO COMPLETO') !== false || $upper === 'TERCIARIO') return 'Terciario completo';
    if (mb_strpos($upper, 'UNIVERSITARIO INCOMPLETO') !== false || mb_strpos($upper, 'UNIVERSITARIO INC') !== false) return 'Universitario incompleto';
    if (mb_strpos($upper, 'UNIVERSITARIO COMPLETO') !== false || $upper === 'UNIVERSITARIO') return 'Universitario completo';
    return 'Otro';
};

$discapacidadMap = [
    'FISICA O MOTORA' => 'Motora',
    'FISICA' => 'Motora',
    'MOTORA' => 'Motora',
    'MENTAL' => 'Intelectual',
    'INTELECTUAL' => 'Intelectual',
    'INTELECTUAL/COGNITIVO' => 'Intelectual',
    'COGNITIVO' => 'Intelectual',
    'SENSORIAL' => 'Sensorial',
    'VISCERAL' => 'Visceral',
    'MULTIPLE' => 'Múltiple',
    'MÚLTIPLE' => 'Múltiple',
    'PSICOSOCIAL' => 'Psicosocial',
];

$discapacidadN = function ($values) use ($discapacidadMap) {
    $result = [];
    foreach ($values as $v) {
        if ($v === null || trim($v) === '') continue;
        $v = trim($v);
        $upper = mb_strtoupper($v);
        if (isset($discapacidadMap[$upper])) {
            $result[$discapacidadMap[$upper]] = true;
        } else {
            $result[$v] = true;
        }
    }
    return empty($result) ? null : implode(', ', array_keys($result));
};

$optionMatch = function ($v, array $options) {
    if ($v === null || trim($v) === '') return null;
    $v = trim($v);
    $vUpper = mb_strtoupper($v);
    $vLower = mb_strtolower($v);
    $vNoAccent = str_replace(['Á','É','Í','Ó','Ú','Ü','á','é','í','ó','ú','ü'], ['A','E','I','O','U','U','a','e','i','o','u','u'], $vUpper);

    foreach ($options as $opt) {
        $optUpper = mb_strtoupper($opt);
        $optNoAccent = str_replace(['Á','É','Í','Ó','Ú','Ü','á','é','í','ó','ú','ü'], ['A','E','I','O','U','U','a','e','i','o','u','u'], $optUpper);
        if ($vUpper === $optUpper || $vNoAccent === $optNoAccent) {
            return $opt;
        }
    }
    return $v;
};

// Construir mapping
addMapping($mapping, 'Nombre', 17389);
addMapping($mapping, 'Apellido', 17390);
addMapping($mapping, 'DNI', 17391);
addMapping($mapping, 'CUIL', 17393);
addMapping($mapping, 'Fecha Nacimiento', 17392);
addMapping($mapping, 'Sexo', 17394, $sexoN);
addMapping($mapping, 'Municipio', 17396); // ya coincide con opciones del select
addMapping($mapping, 'Domicilio', 17397);
addMapping($mapping, 'Numero Calle', 17398);
addMapping($mapping, 'Barrio', 17399);
addMapping($mapping, 'Vive en zona', 17400, $zonaN);

// Discapacidad (se procesa aparte, combinando 5 columnas)
$mapping['_discapacidad_columnas'] = ['Tipo Discapacidad 1', 'Tipo Discapacidad 2', 'Tipo Discapacidad 3', 'Tipo Discapacidad 4', 'Tipo Discapacidad 5'];
$mapping['_discapacidad_field_id'] = 17402;

addMapping($mapping, 'Descripcion Discapacidad', 17404);
addMapping($mapping, 'Diagnostico Medico', 17405);
addMapping($mapping, 'Observacion Discapacidad', 17406);
addMapping($mapping, 'CUD', 17408, $cudN);
addMapping($mapping, 'Percibe Pension No Contributiva', 17410, $pensionN);
addMapping($mapping, 'Nro Expediente Pension', 17411);
addMapping($mapping, 'Percibe AUH', 17412, $pensionN);
addMapping($mapping, 'Percibe Tarjeta Alimentar', 17413, $pensionN);
addMapping($mapping, 'Percibe Otra Pension', 17414, $sn);
addMapping($mapping, 'PCD Trabaja', 17417, $sn);
addMapping($mapping, 'Donde Trabaja', 17418);
addMapping($mapping, 'PCD Autonoma Economicamente', 17420, $sn);
addMapping($mapping, 'Tiene Obra Social', 17423, $sn);
addMapping($mapping, 'Obra Social', 17424);
addMapping($mapping, 'Observaciones Salud', 17425);
addMapping($mapping, 'Asiste Centro Rehabilitacion', 17427, $sn);
addMapping($mapping, 'Periodicidad Control Medico', 17429);
addMapping($mapping, 'Vacuno COVID', 17430, $sn);
addMapping($mapping, 'Que Vacuna', 17431);
addMapping($mapping, 'Cuantas Dosis', 17432);
addMapping($mapping, 'Asiste Establecimiento', 17434, $sn);
addMapping($mapping, 'Establecimiento Educativo', 17435);
addMapping($mapping, 'Nivel Educativo', 17437, $nivelEducN);
addMapping($mapping, 'PCD Actividades Deportivas', 17439, $sn);
addMapping($mapping, 'Desc Actividad Deportiva', 17440);
addMapping($mapping, 'PCD Actividades Culturales', 17441, $sn);
addMapping($mapping, 'Desc Actividad Cultural', 17442);
addMapping($mapping, 'PCD a Cargo Persona Responsable', 17444, $sn);
addMapping($mapping, 'Apellido Responsable', 17445);
addMapping($mapping, 'Nombre Responsable', 17446);
addMapping($mapping, 'DNI Responsable', 17447);
addMapping($mapping, 'Relacion Parentesco PCD', 17448, $parentescoN);
addMapping($mapping, 'Otro Parentesco', 17449);
addMapping($mapping, 'Tiene a Cargo Otras Personas', 17451, $sn);
addMapping($mapping, 'Cuantas a Cargo', 17452);
addMapping($mapping, 'Parentesco Resp', 17453);
addMapping($mapping, 'Describir', 17454);
addMapping($mapping, 'Algunos Tienen Discapacidad', 17455, $sn);
addMapping($mapping, 'Cuantos Disc Resp', 17456);
addMapping($mapping, 'Cuantos Menores Edad', 17457);
addMapping($mapping, 'Cuantos Con Discapacidad Menores', 17458);
addMapping($mapping, 'Nombre Padre', 17460);
addMapping($mapping, 'DNI Padre', 17461);
addMapping($mapping, 'Nombre Madre', 17462);
addMapping($mapping, 'DNI Madre', 17463);
addMapping($mapping, 'Telefono PCD', 17465);
addMapping($mapping, 'Celular PCD', 17466);
addMapping($mapping, 'Email PCD', 17467);
addMapping($mapping, 'Telefono Responsable', 17469);
addMapping($mapping, 'Celular Responsable', 17470);
addMapping($mapping, 'Email Responsable', 17471);
addMapping($mapping, 'Otro Contacto', 17472);
addMapping($mapping, 'Monto Ingreso Familiar', 17474);
addMapping($mapping, 'Otros Ingresos', 17475);
addMapping($mapping, 'Tipo Trabajo', 17476);
addMapping($mapping, 'Quien del Grupo Familiar', 17477);
addMapping($mapping, 'Observacion Familiar', 17479);
addMapping($mapping, 'Percibe AUH Fam', 17480, $sn);
addMapping($mapping, 'Tiene Tarjeta Hambre Cero', 17482, $sn);
addMapping($mapping, 'Quienes Hambre', 17486);
addMapping($mapping, 'Tiene Ticket Social IPLyC', 17483, $sn);
addMapping($mapping, 'Tiene Ticket Feria Franca', 17484, $sn);
addMapping($mapping, 'Vivienda', 17489, $viviendaN);
addMapping($mapping, 'Techo', 17493, $techoN);
addMapping($mapping, 'Piso', 17495, $pisoN);
addMapping($mapping, 'Observaciones Habitacional', 17499);
addMapping($mapping, 'Bano', 17497, $banoN);
addMapping($mapping, 'Tiene Luz Electrica', 17503, $sn);
addMapping($mapping, 'Cual Luz', 17504);
addMapping($mapping, 'Tiene Agua Potable', 17501, $sn);
addMapping($mapping, 'Cual Agua', 17502);
addMapping($mapping, 'Tiene Necesidades', 17510, $sn);
addMapping($mapping, 'Primera Necesidad', 17511);
addMapping($mapping, 'Segunda Necesidad', 17512);
addMapping($mapping, 'Primera Necesidad Solicitada', 17514);
addMapping($mapping, 'Solicitado a', 17515);
addMapping($mapping, 'Fecha Solicitud', 17516);
addMapping($mapping, 'Resuelto por', 17517);
addMapping($mapping, 'Fecha Resolucion', 17518);
addMapping($mapping, 'Organismo Carga', 17520);
addMapping($mapping, 'Area Dependencia', 17521);
addMapping($mapping, 'Estado', 17523, $estadoN);
addMapping($mapping, 'Motivo Baja', 17524);
addMapping($mapping, 'Observaciones Generales', 17526);

// ============================================
// LEER EXCEL
// ============================================
echo "📂 Leyendo Excel...\n";
$spreadsheet = IOFactory::load($EXCEL_FILE);
$worksheet = $spreadsheet->getActiveSheet();
$data = $worksheet->toArray();
$headers = $data[0];
$totalRows = count($data) - 1;
echo "   {$totalRows} filas encontradas\n\n";

// Índice de columnas
$colIndex = [];
foreach ($headers as $i => $h) {
    $colIndex[trim((string)$h)] = $i;
}

// ============================================
// PROCESAR
// ============================================
$importados = 0;
$omitidos = 0;
$errores = 0;
$logFile = STORAGE_PATH . '/logs/importacion_' . date('Ymd_His') . '.log';

$startTime = microtime(true);

for ($rowIdx = 1; $rowIdx < count($data); $rowIdx++) {
    $row = $data[$rowIdx];
    $dni = isset($colIndex['DNI']) ? trim((string)($row[$colIndex['DNI']] ?? '')) : '';

    if (($rowIdx - 1) % 500 === 0 || $rowIdx === count($data) - 1) {
        $pct = round(($rowIdx - 1) / $totalRows * 100, 1);
        echo "   Progreso: {$rowIdx}/{$totalRows} ({$pct}%) | Importados: {$importados} | Omitidos: {$omitidos} | Errores: {$errores}\n";
    }

    try {
        // Saltar filas sin DNI
        if ($dni === '') {
            $omitidos++;
            continue;
        }

        // Verificar duplicado
        $existing = $db->fetch(
            "SELECT rd.record_id FROM record_data rd WHERE rd.field_id = :field_id AND rd.valor = :dni LIMIT 1",
            ['field_id' => $DNI_FIELD_ID, 'dni' => $dni]
        );
        if ($existing) {
            $omitidos++;
            continue;
        }

        $db->beginTransaction();

        // Insertar record
        $recordId = $db->insert('records', [
            'form_id' => $FORM_ID,
            'user_id' => $USER_ID,
        ]);

        $insertedFields = [];

        // Procesar campos normales
        foreach ($mapping as $excelCol => $mapInfo) {
            if ($excelCol[0] === '_') continue;

            $fieldId = $mapInfo['field_id'];
            $normalizer = $mapInfo['normalizer'];

            if (!isset($colIndex[$excelCol])) continue;

            $rawValue = $row[$colIndex[$excelCol]];
            if ($rawValue === null || trim((string)$rawValue) === '') continue;

            $value = $normalizer ? $normalizer($rawValue) : trim((string)$rawValue);
            if ($value === null || $value === '') continue;

            $db->insert('record_data', [
                'record_id' => $recordId,
                'field_id' => $fieldId,
                'valor' => $value,
            ]);
            $insertedFields[] = $fieldId;
        }

        // Procesar tipo de discapacidad (checkbox combinado)
        $discapColumns = isset($mapping['_discapacidad_columnas']) ? $mapping['_discapacidad_columnas'] : [];
        $discapFieldId = isset($mapping['_discapacidad_field_id']) ? $mapping['_discapacidad_field_id'] : 0;
        if ($discapFieldId && !empty($discapColumns)) {
            $discapValues = [];
            foreach ($discapColumns as $colName) {
                if (isset($colIndex[$colName])) {
                    $discapValues[] = $row[$colIndex[$colName]] ?? null;
                }
            }
            $discapResult = $discapacidadN($discapValues);
            if ($discapResult !== null) {
                $db->insert('record_data', [
                    'record_id' => $recordId,
                    'field_id' => $discapFieldId,
                    'valor' => $discapResult,
                ]);
            }
        }

        $db->commit();
        $importados++;

    } catch (\Throwable $e) {
        try { $db->rollback(); } catch (\Throwable $ignored) {}
        $errores++;
        $msg = "Fila {$rowIdx} (DNI: {$dni}): {$e->getMessage()}\n";
        file_put_contents($logFile, $msg, FILE_APPEND);
    }
}

$elapsed = round(microtime(true) - $startTime, 2);
$rate = $importados > 0 ? round($elapsed / $importados, 2) : 0;

echo "\n========================================\n";
echo "   IMPORTACIÓN FINALIZADA\n";
echo "========================================\n";
echo "  Total filas:       {$totalRows}\n";
echo "  Importados:        {$importados}\n";
echo "  Omitidos (dup/sin DNI): {$omitidos}\n";
echo "  Errores:           {$errores}\n";
echo "  Tiempo:            {$elapsed}s\n";
echo "  Promedio:          {$rate}s/registro\n";
if ($errores > 0) {
    echo "  Log errores:       {$logFile}\n";
}
echo "========================================\n";

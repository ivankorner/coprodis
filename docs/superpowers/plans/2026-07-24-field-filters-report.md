# Dynamic Field Filters for Form Report Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development or superpowers:executing-plans. Steps use checkbox (`- [ ]`) syntax.

**Goal:** Add dynamic filter controls to the form report page (`/reportes/formulario/{id}`) based on form field types.

**Architecture:** Filterable field types (select/radio/checkbox → dropdown with unique values, numero/moneda/porcentaje → range inputs) generate GET params that filter records via EXISTS subqueries before analytics are computed. Filters persist in URL and affect all charts/stats.

**Tech Stack:** PHP 8.3+, MySQL, Tailwind CSS

---

### Task 1: Controller — detect filterable fields, parse filters, apply to queries

**Files:**
- Modify: `controllers/ReportController.php:125-323`

- [ ] **Step 1: Add filterable field detection after loading fields**

After `$fields` is loaded (line 139), add filterable field detection and unique value fetching:

```php
$filterableTypes = ['select', 'radio', 'checkbox', 'numero', 'moneda', 'porcentaje'];
$filterableFields = array_filter($fields, fn($f) => in_array($f->tipo, $filterableTypes));

$fieldOptions = [];
foreach ($filterableFields as $field) {
    if (in_array($field->tipo, ['select', 'radio', 'checkbox'])) {
        $opts = $db->fetchAll(
            "SELECT DISTINCT rd.valor FROM record_data rd
             JOIN records r ON rd.record_id = r.id
             WHERE rd.field_id = :fid AND r.deleted_at IS NULL
             AND rd.valor IS NOT NULL AND rd.valor != ''
             ORDER BY rd.valor",
            ['fid' => $field->id]
        );
        $fieldOptions[$field->id] = array_map(fn($o) => $o->valor, $opts);
    }
}
```

- [ ] **Step 2: Parse field filter GET params after fecha params (after line 147)**

```php
$fieldFilters = [];
foreach ($filterableFields as $field) {
    $fid = $field->id;
    if (in_array($field->tipo, ['select', 'radio', 'checkbox'])) {
        $val = $request->query('f_' . $fid);
        if ($val !== null && $val !== '') {
            $fieldFilters[$fid] = ['type' => 'eq', 'value' => $val];
        }
    } elseif (in_array($field->tipo, ['numero', 'moneda', 'porcentaje'])) {
        $min = $request->query('f_' . $fid . '_min');
        $max = $request->query('f_' . $fid . '_max');
        if (($min !== null && $min !== '') || ($max !== null && $max !== '')) {
            $fieldFilters[$fid] = ['type' => 'range', 'min' => $min, 'max' => $max];
        }
    }
}
```

- [ ] **Step 3: Build fieldFilterWhere function and apply to $where**

Add a helper method to build field filter WHERE clauses. Append to `$where` after date filters:

```php
// After date filter block (after line 147)
if (!empty($fieldFilters)) {
    $ffIdx = 0;
    foreach ($fieldFilters as $fid => $filter) {
        $idx = $ffIdx++;
        if ($filter['type'] === 'eq') {
            $where .= " AND EXISTS (
                SELECT 1 FROM record_data rd_ff{$idx}
                WHERE rd_ff{$idx}.record_id = r.id
                AND rd_ff{$idx}.field_id = :ff_fid{$idx}
                AND rd_ff{$idx}.valor = :ff_val{$idx}
            )";
            $params["ff_fid{$idx}"] = $fid;
            $params["ff_val{$idx}"] = $filter['value'];
        } elseif ($filter['type'] === 'range') {
            $rangeParts = [];
            if ($filter['min'] !== null && $filter['min'] !== '') {
                $rangeParts[] = "CAST(rd_ff{$idx}.valor AS DECIMAL(14,2)) >= :ff_min{$idx}";
                $params["ff_min{$idx}"] = (float)$filter['min'];
            }
            if ($filter['max'] !== null && $filter['max'] !== '') {
                $rangeParts[] = "CAST(rd_ff{$idx}.valor AS DECIMAL(14,2)) <= :ff_max{$idx}";
                $params["ff_max{$idx}"] = (float)$filter['max'];
            }
            if (!empty($rangeParts)) {
                $where .= " AND EXISTS (
                    SELECT 1 FROM record_data rd_ff{$idx}
                    WHERE rd_ff{$idx}.record_id = r.id
                    AND rd_ff{$idx}.field_id = :ff_fid{$idx}
                    AND " . implode(' AND ', $rangeParts) . "
                )";
                $params["ff_fid{$idx}"] = $fid;
            }
        }
    }
}
```

- [ ] **Step 4: Pass new data to view (in the view call at line 314)**

```php
$this->view('reports.form', [
    'form' => $form,
    'fields' => $fields,
    'fieldAnalytics' => $fieldAnalytics,
    'totalRecords' => $totalRecords,
    'fechaDesde' => $fechaDesde,
    'fechaHasta' => $fechaHasta,
    'favorites' => $favorites,
    'filterableFields' => $filterableFields,
    'fieldOptions' => $fieldOptions,
    'fieldFilters' => $fieldFilters,
]);
```

### Task 2: View — render filter controls and active filter tags

**Files:**
- Modify: `views/reports/form.php:45-68`

- [ ] **Step 1: Add filterable field controls after date inputs**

In the filter form, after the Hasta date input and before the Filtrar button, insert:

```php
<?php if (!empty($filterableFields)): ?>
    <?php foreach ($filterableFields as $ff): $fid = $ff->id; ?>
        <?php if (in_array($ff->tipo, ['select', 'radio', 'checkbox']) && !empty($fieldOptions[$fid])): ?>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1"><?= htmlspecialchars($ff->etiqueta) ?></label>
            <select name="f_<?= $fid ?>" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value=""><?= $ff->tipo === 'checkbox' ? 'Todos' : 'Todos' ?></option>
                <?php foreach ($fieldOptions[$fid] as $opt): ?>
                <option value="<?= htmlspecialchars($opt) ?>" <?= ($fieldFilters[$fid]['value'] ?? '') === $opt ? 'selected' : '' ?>>
                    <?= htmlspecialchars($opt) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php elseif (in_array($ff->tipo, ['numero', 'moneda', 'porcentaje'])): ?>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1"><?= htmlspecialchars($ff->etiqueta) ?> (rango)</label>
            <div class="flex items-center gap-1">
                <input type="number" step="any" name="f_<?= $fid ?>_min" placeholder="Min"
                       value="<?= htmlspecialchars($fieldFilters[$fid]['min'] ?? '') ?>"
                       class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <span class="text-gray-400">—</span>
                <input type="number" step="any" name="f_<?= $fid ?>_max" placeholder="Max"
                       value="<?= htmlspecialchars($fieldFilters[$fid]['max'] ?? '') ?>"
                       class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
```

- [ ] **Step 2: Update "Limpiar filtros" visibility to include field filters**

Change the condition to show the clear link when any filter is active:

```php
<?php if ($fechaDesde || $fechaHasta || !empty($fieldFilters)): ?>
```

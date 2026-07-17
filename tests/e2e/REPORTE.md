# Informe de Tests E2E — COPRODIS

**Fecha:** 17/07/2026
**Total:** 24 tests | **Pasados:** 24 | **Fallidos:** 0 | **Duración:** 14.4s
**Navegador:** Chromium | **Workers:** 5

---

## 1. Autenticación (`auth.spec.js`)

| # | Test | Estado | Duración |
|---|------|--------|----------|
| 1 | Login exitoso redirige al dashboard | ✅ | 1.6s |
| 2 | Credenciales inválidas muestra error | ✅ | 1.0s |
| 3 | Logout redirige al login | ✅ | 0.9s |
| 4 | Acceso a dashboard sin autenticación redirige al login | ✅ | 0.8s |

**Page Object:** `LoginPage.js` — `goto()`, `login(email, password)`, `errorMessage`

---

## 2. Formularios dinámicos (`forms.spec.js`)

| # | Test | Estado | Duración |
|---|------|--------|----------|
| 1 | Listar formularios existentes | ✅ | 1.4s |
| 2 | Crear formulario con campos (texto, numero, email, select, textarea) | ✅ | 4.5s |
| 3 | Crear formulario con select y opciones | ✅ | 3.0s |
| 4 | Eliminar formulario | ✅ | 4.4s |

**Page Object:** `FormBuilderPage.js` — `createForm()`, `addField()`, `saveFields()`, `deleteForm()`
**Estrategia:** El formulario se publica automáticamente al crear; los campos se agregan vía Alpine.js x-model.

---

## 3. Gestión de Registros (`records.spec.js`)

| # | Test | Estado | Duración |
|---|------|--------|----------|
| 1 | Listar registros existentes | ✅ | 1.4s |
| 2 | Crear registro en formulario dinámico | ✅ | 1.4s |
| 3 | Ver detalle de registro | ✅ | 1.1s |
| 4 | Editar registro existente | ✅ | 1.3s |
| 5 | Archivar registro activo | ✅ | 2.1s |
| 6 | Eliminar registro archivado | ✅ | 2.0s |

**Page Object:** `RecordPage.js` — `fillField()`, `selectOption()`, `submitCreate()`, `submitEdit()`, `archiveRecordByRow()`, `deleteRecordByRow()`
**Particularidades:**
- El formulario de creación usa Alpine.js con envío AJAX (`submitForm()` → `fetch()` → `window.location.href`)
- Archivar/Eliminar usan SweetAlert2 para confirmación (`.swal2-confirm`)
- La columna "Persona" de la tabla usa `COALESCE` entre campo "Apellido" del formulario y `users.apellido`

---

## 4. Exportación (`exports.spec.js`)

| # | Test | Estado | Duración |
|---|------|--------|----------|
| 1 | Exportar registros a Excel desde la lista | ✅ | 1.9s |
| 2 | Exportar registros a CSV desde la lista | ✅ | 1.6s |
| 3 | Exportar registro individual a PDF | ✅ | 1.2s |

**Flujo:** Abrir dropdown "Exportar" → hacer clic en formato → esperar evento `download`
**Librerías:** PhpSpreadsheet (Excel), TCPDF (PDF), nativo (CSV con UTF-8 BOM)

---

## 5. Reportes (`reports.spec.js`)

| # | Test | Estado | Duración |
|---|------|--------|----------|
| 1 | Acceder a reportes como administrador | ✅ | 1.1s |
| 2 | Navegar entre pestañas (General, Por Formulario, Operadores, Guardados) | ✅ | 1.1s |
| 3 | Ver reporte por formulario | ✅ | 1.2s |
| 4 | Ver timeline de registros | ✅ | 1.1s |
| 5 | Acceso denegado a usuarios sin permisos (rol `usuario`) | ✅ | 1.0s |

**Page Object:** `ReportPage.js` — `goto()`, `gotoForm()`, `gotoTimeline()`, `switchTab()`
**Roles:** `super_usuario` y `administrador` pueden acceder; `usuario` redirige a `/dashboard`.

---

## 6. Cobertura adicional (`coverage.spec.js`)

| # | Test | Estado | Duración |
|---|------|--------|----------|
| 1 | Campos condicionales con Alpine x-show | ✅ | 2.0s |
| 2 | Historial de cambios al editar registro | ✅ | 1.3s |

**Campos condicionales:** Verifica que un campo con `x-show="fields['field_X'] === 'Valor'"` se oculta/muestra al cambiar el campo padre (select).
**Historial:** Edita un registro y verifica que el cambio anterior (tachado) y nuevo aparecen en la sección "Historial de Cambios".

---

## Resumen de Page Objects

| Archivo | Métodos principales |
|---------|-------------------|
| `pages/LoginPage.js` | `goto()`, `login()`, `errorMessage` |
| `pages/FormBuilderPage.js` | `createForm()`, `addField()`, `saveFields()`, `deleteForm()` |
| `pages/RecordPage.js` | `fillField()`, `selectOption()`, `submitCreate()`, `submitEdit()`, `archiveRecordByRow()`, `deleteRecordByRow()` |
| `pages/ReportPage.js` | `goto()`, `gotoForm()`, `gotoTimeline()`, `switchTab()` |

---

## Infraestructura

| Componente | Detalle |
|-----------|---------|
| **Framework** | Playwright 1.52+ (Node.js) |
| **Base URL** | `http://localhost/coprodis/` (con trailing slash) |
| **Base de datos** | MySQL `coprodis_test` en XAMPP |
| **Reset DB** | `reset-db.sh` — dropea/crea `coprodis_test`, aplica migrations + seed.sql |
| **Config test** | `.env.test` → se activa via `run-tests.sh` (backup/restore automático de `.env`) |
| **Seed data** | 3 usuarios, 2 formularios, 3 registros, campos condicionales |

---

## Cómo ejecutar

```bash
# Todos los tests
bash tests/e2e/run-tests.sh npx playwright test

# Tests específicos
bash tests/e2e/run-tests.sh npx playwright test tests/e2e/records.spec.js

# Reporte HTML
bash tests/e2e/run-tests.sh npx playwright test --reporter=html
open playwright-report/index.html
```

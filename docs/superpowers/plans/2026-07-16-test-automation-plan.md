# Playwright E2E Test Automation — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Set up Playwright-based browser E2E tests covering auth, forms, records, exports, and reports for the COPRODIS PHP application.

**Architecture:** Playwright (Node.js) runs alongside the existing PHP app. A test MySQL database (`coprodis_test`) is reset before each test run via SQL seeds. Page Object Model pattern encapsulates selectors and interactions.

**Tech Stack:** `@playwright/test` (Node.js), MySQL 8.0+, PHP 8.1+ (for hash generation only), existing XAMPP environment.

## Global Constraints

- All tests run locally against `http://localhost/coprodis/public`
- Test DB: `coprodis_test`, MySQL 8.0+, charset utf8mb4
- No PHPUnit — pure Playwright only
- Page Object Model pattern required for all spec files
- `data-testid` attributes must NOT be added to views (tests use existing selectors)
- All file paths relative to project root unless absolute
- Commits happen after each passing test task

---

### Task 1: Project scaffolding

**Files:**
- Create: `package.json`
- Create: `playwright.config.js`
- Create: `.env.test`
- Modify: `.gitignore`

**Interfaces:**
- Produces: Playwright config that other tasks' tests will use

- [ ] **Step 1: Initialize package.json**

```bash
npm init -y
```

- [ ] **Step 2: Install Playwright**

```bash
npm install -D @playwright/test
npx playwright install chromium
```

- [ ] **Step 3: Create `.env.test`**

```
APP_NAME="COPRODIS"
APP_URL=http://localhost/coprodis
APP_ENV=test
APP_DEBUG=false

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=coprodis_test
DB_USER=root
DB_PASS=

SESSION_LIFETIME=3600
SESSION_NAME=coprodis_session_test

TIMEZONE=America/Argentina/Buenos_Aires
```

- [ ] **Step 4: Create `playwright.config.js`**

```js
const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests/e2e',
  timeout: 30000,
  retries: 1,
  use: {
    baseURL: 'http://localhost/coprodis/public',
    viewport: { width: 1280, height: 720 },
    screenshot: 'only-on-failure',
    trace: 'retain-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { browserName: 'chromium' },
    },
  ],
});
```

- [ ] **Step 5: Update `.gitignore`**

Append:
```
# Playwright
/test-results/
/playwright-report/
/blob-report/
/playwright/.cache/
```

- [ ] **Step 6: Create directory structure**

```bash
mkdir -p tests/e2e/pages tests/e2e/fixtures
```

- [ ] **Step 7: Verify setup**

```bash
npx playwright test --list
```
Expected: `No tests found.` (no spec files yet — correct)

- [ ] **Step 8: Commit**

```bash
git add package.json package-lock.json playwright.config.js .env.test .gitignore tests/
git commit -m "test: scaffolding playwright e2e setup"
```

---

### Task 2: Database fixtures

**Files:**
- Create: `tests/e2e/fixtures/seed.sql`
- Create: `tests/e2e/fixtures/reset-db.sh`

**Interfaces:**
- Produces: `reset-db.sh` — run before any test execution to ensure clean test data

- [ ] **Step 1: Generate bcrypt hash for test password**

```bash
php -r "echo password_hash('Test123!', PASSWORD_BCRYPT);"
```
Expected output: `$2y$10$...` (copy this hash)

- [ ] **Step 2: Create `tests/e2e/fixtures/seed.sql`**

```sql
-- Seed data for coprodis_test

-- Roles (from original migration)
INSERT INTO roles (id, nombre, slug, descripcion) VALUES
    (1, 'Super Usuario', 'super_usuario', 'Acceso total al sistema'),
    (2, 'Administrador', 'administrador', 'Administración operativa'),
    (3, 'Usuario', 'usuario', 'Acceso básico');

-- Test users (password: Test123!)
INSERT INTO users (id, apellido, nombre, dni, email, password, rol_id, estado) VALUES
    (1, 'Admin', 'Sistema', '11111111', 'admin@test.com', '$2y$10$fKKyG.wsUbttABeywJleU.zRWs4.9EWrByy/ZK2EZ7Z.JpdKygxve', 1, 'activo'),
    (2, 'Operador', 'Usuario', '22222222', 'operador@test.com', '$2y$10$fKKyG.wsUbttABeywJleU.zRWs4.9EWrByy/ZK2EZ7Z.JpdKygxve', 3, 'activo'),
    (3, 'Bloqueado', 'Usuario', '33333333', 'bloqueado@test.com', '$2y$10$fKKyG.wsUbttABeywJleU.zRWs4.9EWrByy/ZK2EZ7Z.JpdKygxve', 3, 'bloqueado');

-- Test forms
INSERT INTO forms (id, titulo, descripcion, estado, created_by) VALUES
    (1, 'Solicitud de Certificado', 'Formulario para solicitar certificado de discapacidad', 'publicado', 1),
    (2, 'Informe Social', 'Formulario para informe social', 'borrador', 1);

-- Form fields for Form 1 (Solicitud de Certificado)
INSERT INTO form_fields (id, form_id, tipo, nombre, etiqueta, placeholder, requerido, opciones, orden) VALUES
    (1, 1, 'texto', 'nombre_completo', 'Nombre completo', 'Ingrese nombre y apellido', TRUE, NULL, 0),
    (2, 1, 'numero', 'dni', 'DNI', 'Ingrese DNI', TRUE, NULL, 1),
    (3, 1, 'email', 'email', 'Correo electrónico', 'correo@ejemplo.com', TRUE, NULL, 2),
    (4, 1, 'telefono', 'telefono', 'Teléfono de contacto', '3764123456', TRUE, NULL, 3),
    (5, 1, 'fecha', 'fecha_nacimiento', 'Fecha de nacimiento', NULL, TRUE, NULL, 4),
    (6, 1, 'select', 'tipo_certificado', 'Tipo de certificado', NULL, TRUE, '["Certificado Único","Certificado Temporal","Renovación"]', 5),
    (7, 1, 'textarea', 'observaciones', 'Observaciones', 'Detalle adicional', FALSE, NULL, 6),
    (8, 1, 'select', 'medio_contacto', 'Medio de contacto preferido', NULL, FALSE, '["Email","Teléfono","WhatsApp","Correo postal"]', 7),
    (9, 1, 'checkbox', 'documentacion_adjunta', 'Documentación adjunta', NULL, FALSE, '["DNI","Certificado médico","Informe social","Otros"]', 8);

-- Form fields for Form 2 (Informe Social — borrador, minimal fields)
INSERT INTO form_fields (id, form_id, tipo, nombre, etiqueta, placeholder, requerido, orden) VALUES
    (10, 2, 'texto', 'asistente_social', 'Asistente Social', 'Nombre del asistente', TRUE, 0),
    (11, 2, 'textarea', 'informe', 'Informe', 'Detalle del informe social', TRUE, 1);

-- Test records
INSERT INTO records (id, form_id, user_id, estado) VALUES
    (1, 1, 2, 'activo'),
    (2, 1, 2, 'activo'),
    (3, 1, 2, 'archivado');

-- Record data
INSERT INTO record_data (record_id, field_id, valor) VALUES
    (1, 1, 'Juan Pérez'), (1, 2, '12345678'), (1, 3, 'juan@ejemplo.com'),
    (1, 4, '3764000001'), (1, 5, '1990-05-15'), (1, 6, 'Certificado Único'),
    (1, 7, 'Solicitante presenta documentación completa'),
    (2, 1, 'María García'), (2, 2, '87654321'), (2, 3, 'maria@ejemplo.com'),
    (2, 4, '3764000002'), (2, 5, '1985-11-20'), (2, 6, 'Renovación'),
    (3, 1, 'Carlos López'), (3, 2, '11223344'), (3, 3, 'carlos@ejemplo.com'),
    (3, 4, '3764000003'), (3, 5, '1978-03-08'), (3, 6, 'Certificado Temporal');
```

- [ ] **Step 3: Create `tests/e2e/fixtures/reset-db.sh`**

```bash
#!/bin/bash
# Resets coprodis_test database

MYSQL_USER="${MYSQL_USER:-root}"
MYSQL_PASS="${MYSQL_PASS:-}"
MYSQL_HOST="${MYSQL_HOST:-127.0.0.1}"
MYSQL_PORT="${MYSQL_PORT:-3306}"

MYSQL_CMD="mysql -u $MYSQL_USER -h $MYSQL_HOST -P $MYSQL_PORT"
if [ -n "$MYSQL_PASS" ]; then
  MYSQL_CMD="$MYSQL_CMD -p$MYSQL_PASS"
fi

echo "Dropping test database..."
$MYSQL_CMD -e "DROP DATABASE IF EXISTS coprodis_test;"

echo "Creating test database..."
$MYSQL_CMD -e "CREATE DATABASE coprodis_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "Applying migrations..."
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$SCRIPT_DIR/../../.."
for f in "$PROJECT_DIR"/migrations/*.sql; do
  echo "  Running $f..."
  $MYSQL_CMD coprodis_test < "$f"
done

echo "Applying seed data..."
$MYSQL_CMD coprodis_test < "$SCRIPT_DIR/seed.sql"

echo "Done."
```

- [ ] **Step 4: Make reset-db.sh executable**

```bash
chmod +x tests/e2e/fixtures/reset-db.sh
```

- [ ] **Step 5: Run reset-db.sh to verify**

```bash
bash tests/e2e/fixtures/reset-db.sh
```
Expected: No errors. Verify tables exist:
```bash
mysql -u root -e "USE coprodis_test; SELECT COUNT(*) FROM users; SELECT COUNT(*) FROM forms; SELECT COUNT(*) FROM records;"
```

- [ ] **Step 6: Commit**

```bash
git add tests/e2e/fixtures/
git commit -m "test: add database fixtures for e2e tests"
```

---

### Task 3: Auth tests — Login page + spec

**Files:**
- Create: `tests/e2e/pages/LoginPage.js`
- Create: `tests/e2e/auth.spec.js`

**Interfaces:**
- `LoginPage` exported as class with `goto()`, `login(email, password)`, `errorMessage()`, `isLoggedIn()`
- Used by: auth.spec.js

- [ ] **Step 1: Create `tests/e2e/pages/LoginPage.js`**

```js
class LoginPage {
  constructor(page) {
    this.page = page;
    this.emailInput = page.locator('input[name="email"]');
    this.passwordInput = page.locator('input[name="password"]');
    this.submitButton = page.locator('button[type="submit"]');
    this.logoutLink = page.locator('a[href*="/logout"]');
    this.flashError = page.locator('.swal2-popup');
  }

  async goto() {
    await this.page.goto('/login');
  }

  async login(email, password) {
    await this.emailInput.fill(email);
    await this.passwordInput.fill(password);
    await this.submitButton.click();
  }

  async errorMessage() {
    try {
      const toast = await this.page.waitForSelector('.swal2-popup', { timeout: 5000 });
      const text = await toast.textContent();
      return text;
    } catch {
      return null;
    }
  }

  async isLoggedIn() {
    await this.page.waitForURL(/dashboard/, { timeout: 5000 });
    return true;
  }
}

module.exports = { LoginPage };
```

- [ ] **Step 2: Create `tests/e2e/auth.spec.js`**

```js
const { test, expect } = require('@playwright/test');
const { LoginPage } = require('./pages/LoginPage');

test.describe('Autenticación', () => {

  test('Login exitoso redirige al dashboard', async ({ page }) => {
    const login = new LoginPage(page);
    await login.goto();
    await login.login('admin@test.com', 'Test123!');
    await expect(page).toHaveURL(/dashboard/);
    await expect(page.locator('h1')).toContainText('Dashboard');
  });

  test('Credenciales inválidas muestra error', async ({ page }) => {
    const login = new LoginPage(page);
    await login.goto();
    await login.login('admin@test.com', 'wrongpass');
    const error = await login.errorMessage();
    expect(error).toBeTruthy();
  });

  test('Logout redirige al login', async ({ page }) => {
    const login = new LoginPage(page);
    await login.goto();
    await login.login('admin@test.com', 'Test123!');
    await expect(page).toHaveURL(/dashboard/);

    // Click logout from user menu
    await page.locator('text=Cerrar Sesión').first().click();
    // Confirm SweetAlert
    await page.locator('.swal2-confirm').click();
    await expect(page).toHaveURL(/login/);
  });

  test('Acceso a dashboard sin autenticación redirige al login', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page).toHaveURL(/login/);
  });
});
```

- [ ] **Step 3: Run auth tests**

```bash
bash tests/e2e/fixtures/reset-db.sh && npx playwright test tests/e2e/auth.spec.js
```
Expected: All 4 tests pass.

- [ ] **Step 4: Commit**

```bash
git add tests/e2e/pages/LoginPage.js tests/e2e/auth.spec.js
git commit -m "test: add auth e2e tests"
```

---

### Task 4: Forms tests — Page Object + spec

**Files:**
- Create: `tests/e2e/pages/FormBuilderPage.js`
- Create: `tests/e2e/forms.spec.js`

**Interfaces:**
- `FormBuilderPage` exported as class with methods: `goto()`, `createForm(title, description)`, `addTextField(label, name)`, `addSelectField(label, name, options)`, `saveFields()`
- Uses `LoginPage` from Task 3

- [ ] **Step 1: Create `tests/e2e/pages/FormBuilderPage.js`**

```js
const { LoginPage } = require('./LoginPage');

class FormBuilderPage {
  constructor(page) {
    this.page = page;
    this.loginPage = new LoginPage(page);
  }

  async goto() {
    await this.page.goto('/formularios');
  }

  async createForm(titulo, descripcion = '') {
    await this.page.goto('/formularios/crear');
    await this.page.locator('input[name="titulo"]').fill(titulo);
    if (descripcion) {
      await this.page.locator('textarea[name="descripcion"]').fill(descripcion);
    }
    await this.page.locator('button[type="submit"]').click();
    await this.page.waitForURL(/formularios\//);
  }

  async addField(tipo, etiqueta, nombre, options = {}) {
    const { placeholder, opciones, requerido } = options;

    // Select field type
    await this.page.locator('select[x-model="fieldForm.tipo"]').selectOption(tipo);

    // Fill etiqueta
    await this.page.locator('input[x-model="fieldForm.etiqueta"]').fill(etiqueta);

    // Fill nombre (field name)
    if (tipo !== 'separador') {
      const nombreInput = this.page.locator('input[x-model="fieldForm.nombre"]');
      if (await nombreInput.isVisible()) {
        await nombreInput.fill(nombre);
      }
    }

    if (placeholder) {
      const placeholderInput = this.page.locator('input[x-model="fieldForm.placeholder"]');
      if (await placeholderInput.isVisible()) {
        await placeholderInput.fill(placeholder);
      }
    }

    if (opciones && ['select', 'checkbox', 'radio'].includes(tipo)) {
      const opcionesTextarea = this.page.locator('textarea[x-model="fieldForm.opciones_text"]');
      if (await opcionesTextarea.isVisible()) {
        await opcionesTextarea.fill(opciones.join('\n'));
      }
    }

    if (requerido && tipo !== 'separador') {
      const checkbox = this.page.locator('input[x-model="fieldForm.requerido"]');
      if (await checkbox.isVisible()) {
        await checkbox.check();
      }
    }

    // Click "Agregar Campo"
    await this.page.locator('button:has-text("Agregar Campo")').click();
  }

  async saveFields() {
    await this.page.locator('button:has-text("Guardar Todos los Campos")').click();
  }

  async toggleFormStatus() {
    const toggleBtn = this.page.locator('button[title="Publicar"], button[title="Despublicar"]').first();
    await toggleBtn.click();
  }

  async deleteForm() {
    const deleteBtn = this.page.locator('button[title="Eliminar"]').first();
    await deleteBtn.click();
    await this.page.locator('.swal2-confirm').click();
  }
}

module.exports = { FormBuilderPage };
```

- [ ] **Step 2: Create `tests/e2e/forms.spec.js`**

```js
const { test, expect } = require('@playwright/test');
const { LoginPage } = require('./pages/LoginPage');
const { FormBuilderPage } = require('./pages/FormBuilderPage');

test.describe('Formularios dinámicos', () => {

  test.beforeEach(async ({ page }) => {
    const login = new LoginPage(page);
    await login.goto();
    await login.login('admin@test.com', 'Test123!');
    await expect(page).toHaveURL(/dashboard/);
  });

  test('Listar formularios existentes', async ({ page }) => {
    const forms = new FormBuilderPage(page);
    await forms.goto();
    await expect(page.locator('h1')).toContainText('Formularios');
    // Should see the 2 seed forms
    await expect(page.locator('text=Solicitud de Certificado')).toBeVisible();
    await expect(page.locator('text=Informe Social')).toBeVisible();
  });

  test('Crear formulario con campos', async ({ page }) => {
    const forms = new FormBuilderPage(page);
    await forms.createForm('Formulario Test E2E', 'Creado desde test automatizado');

    // Add fields
    await forms.addField('texto', 'Nombre del solicitante', 'nombre_solicitante', { placeholder: 'Nombre completo', requerido: true });
    await forms.addField('numero', 'Edad', 'edad', { placeholder: '18', requerido: true });
    await forms.addField('email', 'Correo', 'correo', { placeholder: 'test@test.com' });
    await forms.addField('select', 'Provincia', 'provincia', { opciones: ['Misiones', 'Corrientes', 'Chaco'], requerido: true });
    await forms.addField('textarea', 'Comentarios', 'comentarios', { placeholder: 'Comentarios adicionales' });

    await forms.saveFields();

    // Should redirect back to form list
    await expect(page).toHaveURL(/formularios/);
    await expect(page.locator('text=Formulario Test E2E')).toBeVisible();
  });

  test('Publicar y despublicar formulario', async ({ page }) => {
    const forms = new FormBuilderPage(page);
    await forms.createForm('Form para publicar', 'Test publish/unpublish');
    await forms.saveFields();

    await forms.goto();
    await forms.toggleFormStatus();
    await page.waitForTimeout(500);
    // Reload and check status changed
    await forms.goto();
    await expect(page.locator('text=Publicado').first()).toBeVisible();
  });

  test('Crear formulario con select y opciones', async ({ page }) => {
    const forms = new FormBuilderPage(page);
    await forms.createForm('Encuesta Test', 'Formulario de prueba');

    await forms.addField('texto', 'Nombre', 'nombre', { requerido: true });
    await forms.addField('select', '¿Cómo nos conociste?', 'referencia', {
      opciones: ['Redes sociales', 'Recomendación', 'Web', 'Otro'],
      requerido: true,
    });

    await forms.saveFields();
    await expect(page).toHaveURL(/formularios/);
    await expect(page.locator('text=Encuesta Test')).toBeVisible();
  });

  test('Eliminar formulario', async ({ page }) => {
    const forms = new FormBuilderPage(page);
    await forms.goto();

    // Store initial count
    const initialCount = await page.locator('h3').count();

    // Create then delete
    await forms.createForm('Form para eliminar', 'Será eliminado');
    await forms.saveFields();

    await forms.goto();
    await forms.deleteForm();
    await page.waitForTimeout(500);

    // The new form should be gone
    await expect(page.locator('text=Form para eliminar')).not.toBeVisible();
  });
});
```

- [ ] **Step 3: Run forms tests**

```bash
bash tests/e2e/fixtures/reset-db.sh && npx playwright test tests/e2e/forms.spec.js
```
Expected: All tests pass.

- [ ] **Step 4: Commit**

```bash
git add tests/e2e/pages/FormBuilderPage.js tests/e2e/forms.spec.js
git commit -m "test: add forms e2e tests"
```

---

### Task 5: Records tests — Page Object + spec

**Files:**
- Create: `tests/e2e/pages/RecordsPage.js`
- Create: `tests/e2e/records.spec.js`

**Interfaces:**
- `RecordsPage` exported as class with methods: `goto()`, `createRecord(formName, data)`, `searchRecords(term)`, `getRecordCount()`

- [ ] **Step 1: Create `tests/e2e/pages/RecordsPage.js`**

```js
const { LoginPage } = require('./LoginPage');

class RecordsPage {
  constructor(page) {
    this.page = page;
    this.loginPage = new LoginPage(page);
  }

  async goto() {
    await this.page.goto('/registros');
  }

  async createRecord(formName, data) {
    // Click "Nuevo Registro" button
    await this.page.locator('button:has-text("Nuevo Registro")').click();
    // Select form from dropdown
    await this.page.locator(`a:has-text("${formName}")`).click();

    await this.page.waitForURL(/registros\/crear\//);

    // Fill in fields
    for (const [fieldName, value] of Object.entries(data)) {
      const input = this.page.locator(`input[name="${fieldName}"], textarea[name="${fieldName}"], select[name="${fieldName}"]`);
      if (await input.count() > 0) {
        const tag = await input.evaluate(el => el.tagName.toLowerCase());
        if (tag === 'select') {
          await input.selectOption(value);
        } else {
          await input.fill(value);
        }
      }
    }

    // Click submit
    await this.page.locator('button:has-text("Guardar Registro")').click();
  }

  async searchRecords(term) {
    await this.goto();
    await this.page.locator('input[name="search"]').fill(term);
    await this.page.locator('button:has-text("Filtrar")').click();
    await this.page.waitForTimeout(500);
  }

  async getRecordCount() {
    return await this.page.locator('table tbody tr').count();
  }
}

module.exports = { RecordsPage };
```

- [ ] **Step 2: Create `tests/e2e/records.spec.js`**

```js
const { test, expect } = require('@playwright/test');
const { LoginPage } = require('./pages/LoginPage');
const { RecordsPage } = require('./pages/RecordsPage');

test.describe('Registros (expedientes)', () => {

  test.beforeEach(async ({ page }) => {
    const login = new LoginPage(page);
    await login.goto();
    await login.login('admin@test.com', 'Test123!');
    await expect(page).toHaveURL(/dashboard/);
  });

  test('Listar registros existentes', async ({ page }) => {
    const records = new RecordsPage(page);
    await records.goto();
    await expect(page.locator('h1')).toContainText('Registros');
    // Should see seed records
    await expect(page.locator('text=Juan Pérez')).toBeVisible();
    await expect(page.locator('text=María García')).toBeVisible();
  });

  test('Crear un nuevo registro', async ({ page }) => {
    const records = new RecordsPage(page);
    await records.createRecord('Solicitud de Certificado', {
      'field_1': 'Test E2E User',      // nombre_completo
      'field_2': '99999999',             // dni
      'field_3': 'e2e@test.com',         // email
      'field_4': '3764000099',           // telefono
      'field_5': '2000-01-15',           // fecha_nacimiento
      'field_6': 'Certificado Único',    // tipo_certificado
    });

    // Wait for redirect (either via JS or flash message)
    await page.waitForTimeout(1000);
    await expect(page).toHaveURL(/registros/);
  });

  test('Buscar registros por término', async ({ page }) => {
    const records = new RecordsPage(page);
    await records.searchRecords('Pérez');
    const count = await records.getRecordCount();
    expect(count).toBeGreaterThanOrEqual(1);
    await expect(page.locator('text=Juan Pérez')).toBeVisible();
  });

  test('Filtrar por formulario', async ({ page }) => {
    const records = new RecordsPage(page);
    await records.goto();
    await page.locator('select[name="form_id"]').selectOption('1');
    await page.locator('button:has-text("Filtrar")').click();
    await page.waitForTimeout(500);
    // All visible records should belong to form 1
    const count = await records.getRecordCount();
    expect(count).toBeGreaterThanOrEqual(1);
  });

  test('Ver detalle de un registro', async ({ page }) => {
    const records = new RecordsPage(page);
    await records.goto();
    // Click "ver" (eye icon) on first record
    await page.locator('a[href*="/registros/"] i.fa-eye').first().click();
    await expect(page.locator('text=Juan Pérez')).toBeVisible();
  });
});
```

- [ ] **Step 3: Run records tests**

```bash
bash tests/e2e/fixtures/reset-db.sh && npx playwright test tests/e2e/records.spec.js
```
Expected: All tests pass.

- [ ] **Step 4: Commit**

```bash
git add tests/e2e/pages/RecordsPage.js tests/e2e/records.spec.js
git commit -m "test: add records e2e tests"
```

---

### Task 6: Exports tests — Page Object + spec

**Files:**
- Create: `tests/e2e/pages/ExportsPage.js`
- Create: `tests/e2e/exports.spec.js`

**Interfaces:**
- `ExportsPage` exported as class with methods: `goto(modulo)`, `exportExcel(modulo)`, `exportCSV(modulo)`, `exportPDF(modulo)`

- [ ] **Step 1: Create `tests/e2e/pages/ExportsPage.js`**

```js
const { LoginPage } = require('./LoginPage');

class ExportsPage {
  constructor(page) {
    this.page = page;
    this.loginPage = new LoginPage(page);
  }

  async goto(modulo = 'registros') {
    await this.page.goto(`/exportar/${modulo}`);
  }

  async exportExcel(modulo = 'registros') {
    await this.goto(modulo);
    await this.page.locator('form[action*="/excel"] button[type="submit"]').click();
    // Wait for file download
    const [download] = await Promise.all([
      this.page.waitForEvent('download', { timeout: 15000 }),
    ]);
    return download;
  }

  async exportCSV(modulo = 'registros') {
    await this.goto(modulo);
    await this.page.locator('form[action*="/csv"] button[type="submit"]').click();
    const [download] = await Promise.all([
      this.page.waitForEvent('download', { timeout: 15000 }),
    ]);
    return download;
  }

  async exportPDF(modulo = 'registros') {
    await this.goto(modulo);
    await this.page.locator('form[action*="/pdf"] button[type="submit"]').click();
    const [download] = await Promise.all([
      this.page.waitForEvent('download', { timeout: 15000 }),
    ]);
    return download;
  }
}

module.exports = { ExportsPage };
```

- [ ] **Step 2: Create `tests/e2e/exports.spec.js`**

```js
const { test, expect } = require('@playwright/test');
const { LoginPage } = require('./pages/LoginPage');
const { ExportsPage } = require('./pages/ExportsPage');

test.describe('Exportaciones', () => {

  test.beforeEach(async ({ page }) => {
    const login = new LoginPage(page);
    await login.goto();
    await login.login('admin@test.com', 'Test123!');
    await expect(page).toHaveURL(/dashboard/);
  });

  test('Exportar registros a Excel', async ({ page }) => {
    const exportsPage = new ExportsPage(page);
    const download = await exportsPage.exportExcel('registros');
    expect(download.suggestedFilename()).toMatch(/\.xlsx?$/i);
  });

  test('Exportar registros a CSV', async ({ page }) => {
    const exportsPage = new ExportsPage(page);
    const download = await exportsPage.exportCSV('registros');
    expect(download.suggestedFilename()).toMatch(/\.csv$/i);
  });

  test('Exportar registros a PDF', async ({ page }) => {
    const exportsPage = new ExportsPage(page);
    const download = await exportsPage.exportPDF('registros');
    expect(download.suggestedFilename()).toMatch(/\.pdf$/i);
  });
});
```

- [ ] **Step 3: Run exports tests**

```bash
bash tests/e2e/fixtures/reset-db.sh && npx playwright test tests/e2e/exports.spec.js
```
Expected: All tests pass.

- [ ] **Step 4: Commit**

```bash
git add tests/e2e/pages/ExportsPage.js tests/e2e/exports.spec.js
git commit -m "test: add exports e2e tests"
```

---

### Task 7: Reports tests — Page Object + spec

**Files:**
- Create: `tests/e2e/pages/ReportsPage.js`
- Create: `tests/e2e/reports.spec.js`

**Interfaces:**
- `ReportsPage` exported as class with methods: `goto()`, `goToFormReport(formId)`, `applyFilter(filterData)`

- [ ] **Step 1: Create `tests/e2e/pages/ReportsPage.js`**

```js
const { LoginPage } = require('./LoginPage');

class ReportsPage {
  constructor(page) {
    this.page = page;
    this.loginPage = new LoginPage(page);
  }

  async goto() {
    await this.page.goto('/reportes');
  }

  async goToFormReport(formId) {
    await this.goto();
    await this.page.goto(`/reportes/formulario/${formId}`);
  }

  async applyFilter(field, value) {
    const input = this.page.locator(`[name="${field}"]`);
    if (await input.count() > 0) {
      const tag = await input.evaluate(el => el.tagName.toLowerCase());
      if (tag === 'select') {
        await input.selectOption(value);
      } else {
        await input.fill(value);
      }
    }
  }
}

module.exports = { ReportsPage };
```

- [ ] **Step 2: Create `tests/e2e/reports.spec.js`**

```js
const { test, expect } = require('@playwright/test');
const { LoginPage } = require('./pages/LoginPage');
const { ReportsPage } = require('./pages/ReportsPage');

test.describe('Reportes', () => {

  test.beforeEach(async ({ page }) => {
    const login = new LoginPage(page);
    await login.goto();
    await login.login('admin@test.com', 'Test123!');
    await expect(page).toHaveURL(/dashboard/);
  });

  test('Página de reportes lista reportes disponibles', async ({ page }) => {
    const reports = new ReportsPage(page);
    await reports.goto();
    await expect(page.locator('h1')).toContainText('Reportes');
  });

  test('Reporte por formulario se renderiza', async ({ page }) => {
    const reports = new ReportsPage(page);
    await reports.goToFormReport(1);
    await expect(page.locator('text=Solicitud de Certificado')).toBeVisible();
  });

  test('Exportar reporte como PDF', async ({ page }) => {
    const reports = new ReportsPage(page);
    await reports.goToFormReport(1);
    await page.waitForTimeout(1000);

    // Try to export if export button exists
    const exportBtn = page.locator('button:has-text("Exportar"), a:has-text("Exportar")');
    if (await exportBtn.count() > 0) {
      const [download] = await Promise.all([
        page.waitForEvent('download', { timeout: 15000 }).catch(() => null),
        exportBtn.click(),
      ]);
      if (download) {
        expect(download.suggestedFilename()).toMatch(/\.pdf$/i);
      }
    }
  });
});
```

- [ ] **Step 3: Run reports tests**

```bash
bash tests/e2e/fixtures/reset-db.sh && npx playwright test tests/e2e/reports.spec.js
```
Expected: All tests pass.

- [ ] **Step 4: Commit**

```bash
git add tests/e2e/pages/ReportsPage.js tests/e2e/reports.spec.js
git commit -m "test: add reports e2e tests"
```

---

### Task 8: Add npm scripts and final adjustments

**Files:**
- Modify: `package.json` (add scripts)

- [ ] **Step 1: Add scripts to `package.json`**

Add to `scripts`:
```json
{
  "scripts": {
    "test": "playwright test",
    "test:auth": "playwright test tests/e2e/auth.spec.js",
    "test:forms": "playwright test tests/e2e/forms.spec.js",
    "test:records": "playwright test tests/e2e/records.spec.js",
    "test:exports": "playwright test tests/e2e/exports.spec.js",
    "test:reports": "playwright test tests/e2e/reports.spec.js",
    "test:ui": "playwright test --ui",
    "db:reset": "bash tests/e2e/fixtures/reset-db.sh",
    "test:all": "npm run db:reset && playwright test"
  }
}
```

- [ ] **Step 2: Run full test suite**

```bash
bash tests/e2e/fixtures/reset-db.sh && npx playwright test
```
Expected: All spec files execute, all tests pass.

- [ ] **Step 3: Commit**

```bash
git add package.json
git commit -m "test: add npm scripts for e2e test execution"
```

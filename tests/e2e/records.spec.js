const { test, expect } = require('@playwright/test');
const { LoginPage } = require('./pages/LoginPage');
const { RecordPage } = require('./pages/RecordPage');

test.describe('Gestión de Registros', () => {

  test.beforeEach(async ({ page }) => {
    const login = new LoginPage(page);
    await login.goto();
    await login.login('admin@test.com', 'Test123!');
    await expect(page).toHaveURL(/dashboard/);
  });

  test('Listar registros existentes', async ({ page }) => {
    const records = new RecordPage(page);
    await records.goto();
    await expect(page.locator('main h1')).toContainText('Registros');
    await expect(page.locator('table tbody tr')).toHaveCount(3);
    await expect(page.locator('text=#1')).toBeVisible();
    await expect(page.locator('text=#2')).toBeVisible();
    await expect(page.locator('text=#3')).toBeVisible();
  });

  test('Crear registro en formulario', async ({ page }) => {
    const records = new RecordPage(page);
    await records.gotoCreate(1);

    await records.fillField('Nombre completo', 'Test Crear E2E');
    await records.fillField('DNI', '99999999');
    await records.fillField('Correo electrónico', 'test@e2e.com');
    await records.fillField('Teléfono de contacto', '3764000999');
    await records.fillField('Fecha de nacimiento', '2000-01-15');
    await records.selectOption('Tipo de certificado', 'Certificado Único');
    await records.fillField('Observaciones', 'Creado desde test E2E');

    await records.submitCreate();
    await expect(page.locator('main h1')).toContainText('Registros');
    await expect(page.locator('table tbody tr')).toHaveCount(4);
  });

  test('Ver detalle de registro', async ({ page }) => {
    const records = new RecordPage(page);
    await records.viewRecord(1);

    await expect(page.locator('main h1')).toContainText('Registro #1');
    await expect(page.locator('text=Juan Pérez')).toBeVisible();
    await expect(page.locator('text=12345678')).toBeVisible();
    await expect(page.locator('text=juan@ejemplo.com')).toBeVisible();
    await expect(page.locator('text=Certificado Único')).toBeVisible();
  });

  test('Editar registro existente', async ({ page }) => {
    const records = new RecordPage(page);
    await records.gotoEdit(2);

    await records.fillField('Nombre completo', 'María Editada E2E');

    await records.submitEdit();
    await expect(page.locator('main h1')).toContainText('Registro #2');
    await expect(page.locator('text=María Editada E2E').first()).toBeVisible();
  });

  test('Archivar registro activo', async ({ page }) => {
    const records = new RecordPage(page);
    await records.goto();
    await expect(page.locator('text=#1')).toBeVisible();

    await records.archiveRecordByRow(1);

    await records.goto();
    await expect(page.locator('text=#1')).toBeVisible();
  });

  test('Eliminar registro archivado', async ({ page }) => {
    const records = new RecordPage(page);
    await records.goto();
    await expect(page.locator('text=#3')).toBeVisible();

    await records.deleteRecordByRow(3);

    await records.goto();
    await expect(page.locator('text=#3')).not.toBeVisible();
  });
});

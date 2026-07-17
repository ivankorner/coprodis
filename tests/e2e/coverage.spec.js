const { test, expect } = require('@playwright/test');
const { LoginPage } = require('./pages/LoginPage');
const { RecordPage } = require('./pages/RecordPage');

test.describe('Cobertura adicional', () => {

  test.beforeEach(async ({ page }) => {
    const login = new LoginPage(page);
    await login.goto();
    await login.login('admin@test.com', 'Test123!');
    await expect(page).toHaveURL(/dashboard/);
  });

  test('Campos condicionales con Alpine x-show', async ({ page }) => {
    const records = new RecordPage(page);
    await records.gotoCreate(1);

    const condField = page.locator('label:has-text("Dirección de email")').locator('..');

    await expect(condField).toBeHidden();

    await records.selectOption('Medio de contacto preferido', 'Email');
    await page.waitForTimeout(300);

    await expect(condField).toBeVisible();
    await condField.locator('input').fill('test@condicional.com');

    await records.selectOption('Medio de contacto preferido', 'Teléfono');
    await page.waitForTimeout(300);

    await expect(condField).toBeHidden();
  });

  test('Historial de cambios al editar registro', async ({ page }) => {
    const records = new RecordPage(page);
    await records.viewRecord(1);
    const oldName = await page.locator('p.text-gray-900:has-text("Juan Pérez")').textContent();
    expect(oldName).toContain('Juan Pérez');

    await records.gotoEdit(1);
    await records.fillField('Nombre completo', 'Juan Editado Historial');

    await records.submitEdit();

    await expect(page.locator('text=Juan Editado Historial').first()).toBeVisible();
    await expect(page.locator('text=Historial de Cambios')).toBeVisible();
    await expect(page.locator('text=Juan Pérez')).toBeVisible();
  });
});

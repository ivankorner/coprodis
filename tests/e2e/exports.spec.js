const { test, expect } = require('@playwright/test');
const { LoginPage } = require('./pages/LoginPage');
const { RecordPage } = require('./pages/RecordPage');

test.describe('Exportación de Registros', () => {

  test.beforeEach(async ({ page }) => {
    const login = new LoginPage(page);
    await login.goto();
    await login.login('admin@test.com', 'Test123!');
    await expect(page).toHaveURL(/dashboard/);
  });

  test('Exportar registros a Excel desde la lista', async ({ page }) => {
    const records = new RecordPage(page);
    await records.goto();

    await page.locator('button:has-text("Exportar")').click();
    await page.waitForTimeout(300);

    const [download] = await Promise.all([
      page.waitForEvent('download', { timeout: 15000 }),
      page.locator('button:has-text("Excel")').last().click(),
    ]);

    expect(download.suggestedFilename()).toMatch(/registros.*\.xlsx$/);
  });

  test('Exportar registros a CSV desde la lista', async ({ page }) => {
    const records = new RecordPage(page);
    await records.goto();

    await page.locator('button:has-text("Exportar")').click();
    await page.waitForTimeout(300);

    const [download] = await Promise.all([
      page.waitForEvent('download', { timeout: 15000 }),
      page.locator('button:has-text("CSV")').last().click(),
    ]);

    expect(download.suggestedFilename()).toMatch(/registros.*\.csv$/);
  });

  test('Exportar registro individual a PDF', async ({ page }) => {
    const records = new RecordPage(page);
    await records.viewRecord(1);

    const [download] = await Promise.all([
      page.waitForEvent('download', { timeout: 15000 }),
      page.locator('a:has-text("PDF")').click(),
    ]);

    expect(download.suggestedFilename()).toContain('.pdf');
  });
});

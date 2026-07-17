const { test, expect } = require('@playwright/test');
const { LoginPage } = require('./pages/LoginPage');
const { ReportPage } = require('./pages/ReportPage');

test.describe('Reportes', () => {

  test('Acceder a reportes como administrador', async ({ page }) => {
    const login = new LoginPage(page);
    await login.goto();
    await login.login('admin@test.com', 'Test123!');
    await expect(page).toHaveURL(/dashboard/);

    const reports = new ReportPage(page);
    await reports.goto();
    await expect(page.locator('main h1')).toContainText('Reportes');
    await expect(page.locator('text=Total Registros')).toBeVisible();
    const cards = page.locator('main .grid.grid-cols-2 div');
    await expect(cards.filter({ hasText: 'Formularios' })).toBeVisible();
    await expect(cards.filter({ hasText: 'Usuarios' })).toBeVisible();
  });

  test('Navegar entre pestañas de reportes', async ({ page }) => {
    const login = new LoginPage(page);
    await login.goto();
    await login.login('admin@test.com', 'Test123!');
    await expect(page).toHaveURL(/dashboard/);

    const reports = new ReportPage(page);
    await reports.goto();
    await reports.switchTab('Por Formulario');
    await expect(page.locator('text=Solicitud de Certificado').first()).toBeVisible();

    await reports.switchTab('Operadores');
    await expect(page.locator('text=Operador Usuario')).toBeVisible();

    await reports.switchTab('Guardados');
    await expect(page.locator('text=No hay reportes guardados')).toBeVisible();
  });

  test('Ver reporte por formulario', async ({ page }) => {
    const login = new LoginPage(page);
    await login.goto();
    await login.login('admin@test.com', 'Test123!');
    await expect(page).toHaveURL(/dashboard/);

    const reports = new ReportPage(page);
    await reports.goto();
    await reports.switchTab('Por Formulario');
    await page.locator('a:has-text("Solicitud de Certificado")').first().click();
    await expect(page.locator('main h1')).toContainText('Solicitud de Certificado');
    await expect(page.locator('text=Total Registros')).toBeVisible();
  });

  test('Ver timeline de registros', async ({ page }) => {
    const login = new LoginPage(page);
    await login.goto();
    await login.login('admin@test.com', 'Test123!');
    await expect(page).toHaveURL(/dashboard/);

    const reports = new ReportPage(page);
    await reports.gotoTimeline();
    await expect(page.locator('main h1')).toContainText('Timeline de Registros');
    await expect(page.locator('text=Gráfico')).toBeVisible();
    await expect(page.locator('text=Tabla')).toBeVisible();
  });

  test('Acceso denegado a usuarios sin permisos', async ({ page }) => {
    const login = new LoginPage(page);
    await login.goto();
    await login.login('operador@test.com', 'Test123!');
    await expect(page).toHaveURL(/dashboard/);

    await page.goto('reportes');
    await expect(page).toHaveURL(/dashboard/);
  });
});

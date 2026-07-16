const { test, expect } = require('@playwright/test');
const { LoginPage } = require('./pages/LoginPage');

test.describe('Autenticación', () => {

  test('Login exitoso redirige al dashboard', async ({ page }) => {
    const login = new LoginPage(page);
    await login.goto();
    await login.login('admin@test.com', 'Test123!');
    await expect(page).toHaveURL(/dashboard/);
    await expect(page.locator('main h1')).toContainText('Dashboard');
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

    await page.goto('logout');
    await expect(page).toHaveURL(/login/);
  });

  test('Acceso a dashboard sin autenticación redirige al login', async ({ page }) => {
    await page.goto('dashboard');
    await expect(page).toHaveURL(/login/);
  });
});

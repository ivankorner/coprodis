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
    await expect(page.locator('main h1')).toContainText('Formularios');
    await expect(page.locator('h3').first()).toBeVisible();
  });

  test('Crear formulario con campos', async ({ page }) => {
    const forms = new FormBuilderPage(page);
    await forms.createForm('Formulario Test E2E', 'Creado desde test automatizado');

    await forms.addField('texto', 'Nombre del solicitante', 'nombre_solicitante', { placeholder: 'Nombre completo', requerido: true });
    await forms.addField('numero', 'Edad', 'edad', { placeholder: '18', requerido: true });
    await forms.addField('email', 'Correo', 'correo', { placeholder: 'test@test.com' });
    await forms.addField('select', 'Provincia', 'provincia', { opciones: ['Misiones', 'Corrientes', 'Chaco'], requerido: true });
    await forms.addField('textarea', 'Comentarios', 'comentarios', { placeholder: 'Comentarios adicionales' });

    await forms.saveFields();
    await forms.goto();
    await expect(page.locator('h3:has-text("Formulario Test E2E")').first()).toBeVisible();
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
    await forms.goto();
    await expect(page.locator('h3:has-text("Encuesta Test")').first()).toBeVisible();
  });

  test('Radio button con sub-pregunta preserva enlace al guardar', async ({ page }) => {
    const forms = new FormBuilderPage(page);
    await forms.createForm('Test Radio Subpregunta', 'Verificar enlace padre-hijo');

    await forms.fillFieldForm('radio', 'Género', 'genero', {
      opciones: ['Masculino', 'Femenino', 'Otro'],
    });

    await forms.addSubPregunta('Masculino', 'texto', 'Especificar Masculino', 'especificar_masculino');
    await forms.addSubPregunta('Otro', 'textarea', 'Especificar Otro', 'especificar_otro');

    await forms.clickAddFieldButton();
    await forms.saveFields();

    await expect(page.locator('.border-l-4.border-green-400')).toHaveCount(2);
    await expect(page.locator('span:has-text("Sub-pregunta")').first()).toBeVisible();
    await expect(page.locator('text=Sub-pregunta de: Género').first()).toBeVisible();
    await expect(page.locator('p:has-text("Género")').first()).toBeVisible();
  });

  test('Dos radios con la misma opción mantienen sus sub-preguntas separadas', async ({ page }) => {
    const forms = new FormBuilderPage(page);
    await forms.createForm('Test Radios Independientes', 'Evitar cruces entre respuestas Sí/No');

    await forms.fillFieldForm('radio', '¿Tiene agua potable?', 'tiene_agua', {
      opciones: ['Sí', 'No'],
    });
    await forms.addSubPregunta('Sí', 'textarea', 'Proveedor', 'proveedor_agua');
    await forms.clickAddFieldButton();

    await forms.fillFieldForm('radio', '¿Tiene energía eléctrica?', 'tiene_energia', {
      opciones: ['Sí', 'No'],
    });
    await forms.addSubPregunta('Sí', 'textarea', 'Proveedor', 'proveedor_energia');
    await forms.clickAddFieldButton();
    await forms.saveFields();

    await expect(page.locator('.border-l-4.border-green-400')).toHaveCount(2);
    await expect(page.locator('p.text-green-600', { hasText: 'Sub-pregunta de: ¿Tiene agua potable?' })).toHaveCount(1);
    await expect(page.locator('p.text-green-600', { hasText: 'Sub-pregunta de: ¿Tiene energía eléctrica?' })).toHaveCount(1);
  });

  test('Eliminar formulario', async ({ page }) => {
    const forms = new FormBuilderPage(page);
    await forms.createForm('Form para eliminar', 'Será eliminado');
    await forms.addField('texto', 'Nombre', 'nombre', { requerido: true });
    await forms.saveFields();

    await forms.goto();
    await expect(page.locator('h3:has-text("Form para eliminar")').first()).toBeVisible();

    await forms.deleteForm();
    await page.waitForTimeout(500);

    await expect(page.locator('h3:has-text("Form para eliminar")').first()).not.toBeVisible();
  });
});

class FormBuilderPage {
  constructor(page) {
    this.page = page;
  }

  async goto() {
    await this.page.goto('formularios');
  }

  async createForm(titulo, descripcion = '') {
    await this.page.goto('formularios/crear');
    await this.page.locator('input[name="titulo"]').fill(titulo);
    if (descripcion) {
      await this.page.locator('textarea[name="descripcion"]').fill(descripcion);
    }
    await this.page.locator('button[type="submit"]').click();
    await this.page.waitForURL(/formularios\/\d+\/editar/);
  }

  async fillFieldForm(tipo, etiqueta, nombre, options = {}) {
    const { placeholder, opciones, requerido } = options;

    await this.page.locator('select[x-model="fieldForm.tipo"]').selectOption(tipo);
    await this.page.locator('input[x-model="fieldForm.etiqueta"]').fill(etiqueta);

    if (tipo !== 'separador') {
      await this.page.locator('input[x-model="fieldForm.nombre"]').fill(nombre);
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
      const checkbox = this.page.locator('input[type="checkbox"][x-model="fieldForm.requerido"]');
      if (await checkbox.isVisible()) {
        await checkbox.check();
      }
    }
  }

  async addField(tipo, etiqueta, nombre, options = {}) {
    await this.fillFieldForm(tipo, etiqueta, nombre, options);
    await this.clickAddFieldButton();
  }

  async clickAddFieldButton() {
    await this.page.locator('button.bg-blue-600').filter({ hasText: 'Agregar Campo' }).click();
    await this.page.waitForTimeout(300);
  }

  async addSubPregunta(optionText, tipo, etiqueta, nombre) {
    const optionRow = this.page.locator('.flex.items-center.space-x-2.bg-gray-50.rounded-lg.p-2')
      .filter({ hasText: `Si: ${optionText}` });
    await optionRow.locator('button').click();
    await this.page.waitForTimeout(300);

    const miniForm = this.page.locator(`text=Nuevo campo para: ${optionText}`)
      .locator('xpath=../..');

    await miniForm.locator('select').first().selectOption(tipo);
    await miniForm.locator('input[x-model="subPreguntaForm.etiqueta"]').fill(etiqueta);
    await miniForm.locator('input[x-model="subPreguntaForm.nombre"]').fill(nombre);

    await miniForm.locator('button.bg-green-600').click();
    await this.page.waitForTimeout(300);
  }

  async saveFields() {
    await this.page.locator('button:has-text("Guardar Todos los Campos")').click();
    await this.page.waitForURL(/formularios\/\d+\/editar/);
    await this.page.waitForTimeout(1000);
  }

  async toggleFormStatus() {
    await this.page.locator('button[title="Publicar"], button[title="Despublicar"]').first().click();
  }

  async deleteForm() {
    await this.page.locator('button[title="Eliminar"]').first().click();
    await this.page.waitForTimeout(500);
    await this.page.locator('.swal2-confirm').click();
    await this.page.waitForTimeout(500);
  }
}

module.exports = { FormBuilderPage };

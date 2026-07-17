class RecordPage {
  constructor(page) {
    this.page = page;
  }

  async goto() {
    await this.page.goto('registros');
  }

  async gotoCreate(formId) {
    await this.page.goto(`registros/crear/${formId}`);
  }

  async fillField(label, value) {
    const container = this.page.locator('label').filter({ hasText: label }).locator('..');
    await container.locator('input, select, textarea').first().fill(value);
  }

  async selectOption(label, option) {
    const container = this.page.locator('label').filter({ hasText: label }).locator('..');
    await container.locator('select').first().selectOption(option);
  }

  async submitCreate() {
    await this.page.locator('button:has-text("Guardar Registro")').click();
    await this.page.waitForURL(/\/registros$/, { timeout: 15000 });
  }

  async submitEdit() {
    await this.page.locator('button:has-text("Actualizar Registro")').click();
    await this.page.waitForURL(/\/registros\/\d+$/, { timeout: 15000 });
  }

  async viewRecord(recordId) {
    await this.page.goto(`registros/${recordId}`);
  }

  async gotoEdit(recordId) {
    await this.page.goto(`registros/${recordId}/editar`);
  }

  async archiveRecordByRow(recordId) {
    const row = this.page.locator(`tr:has(td:text-is("#${recordId}"))`);
    await row.locator('.fa-archive').click();
    await this.page.locator('.swal2-confirm').waitFor({ timeout: 3000 });
    await this.page.locator('.swal2-confirm').click();
    await this.page.waitForURL(/\/registros$/, { timeout: 10000 });
  }

  async deleteRecordByRow(recordId) {
    const row = this.page.locator(`tr:has(td:text-is("#${recordId}"))`);
    await row.locator('.fa-trash').click();
    await this.page.locator('.swal2-confirm').waitFor({ timeout: 3000 });
    await this.page.locator('.swal2-confirm').click();
    await this.page.waitForURL(/\/registros$/, { timeout: 10000 });
  }

  async getRecordIdLink(recordId) {
    const row = this.page.locator(`tr:has(td:text-is("#${recordId}"))`);
    return row.locator('a[href*="registros/"]').first();
  }

  async createRecord(formId, fieldValues) {
    await this.gotoCreate(formId);
    for (const { label, value, type } of fieldValues) {
      if (type === 'select') {
        await this.selectOption(label, value);
      } else {
        await this.fillField(label, value);
      }
    }
    await this.submitCreate();
  }
}

module.exports = { RecordPage };

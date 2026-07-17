class ReportPage {
  constructor(page) {
    this.page = page;
  }

  async goto() {
    await this.page.goto('reportes');
  }

  async gotoForm(formId) {
    await this.page.goto(`reportes/formulario/${formId}`);
  }

  async gotoTimeline() {
    await this.page.goto('reportes/timeline');
  }

  async switchTab(tabName) {
    await this.page.locator('button').filter({ hasText: tabName }).click();
  }
}

module.exports = { ReportPage };

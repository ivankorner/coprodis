class LoginPage {
  constructor(page) {
    this.page = page;
    this.emailInput = page.locator('input[name="email"]');
    this.passwordInput = page.locator('input[name="password"]');
    this.submitButton = page.locator('button[type="submit"]');
  }

  async goto() {
    await this.page.goto('login');
  }

  async login(email, password) {
    await this.emailInput.fill(email);
    await this.passwordInput.fill(password);
    await this.submitButton.click();
  }

  async errorMessage() {
    try {
      await this.page.waitForSelector('.swal2-popup', { timeout: 5000 });
      return await this.page.locator('.swal2-popup').textContent();
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

# COPRODIS Test Automation — Design Spec

## 1. Goal

Automate browser-based regression testing for COPRODIS, a custom PHP MVC application with MySQL and Alpine.js frontend. Tests run **locally** on the developer's machine (XAMPP).

## 2. Tool

**Playwright** (pure, no PHPUnit). Single test runner, real browser execution covering JS-driven UI (Alpine.js, DataTables, Chart.js).

## 3. Project Structure

```
tests/e2e/
├── fixtures/
│   ├── seed.sql               # Controlled test data
│   └── reset-db.sh            # Drop + recreate + seed test DB
├── pages/                     # Page Object Models
│   ├── LoginPage.js
│   ├── DashboardPage.js
│   ├── FormBuilderPage.js
│   ├── RecordsPage.js
│   ├── ExportsPage.js
│   └── ReportsPage.js
├── auth.spec.js
├── forms.spec.js
├── records.spec.js
├── exports.spec.js
└── reports.spec.js
```

Root-level additions:
- `playwright.config.js`
- `package.json` (with `@playwright/test`)
- `.env.test` (points to `coprodis_test` DB)

## 4. Database Strategy

- **Test DB:** `coprodis_test` (MySQL, same schema via existing migrations)
- **Resets before each run:** `reset-db.sh` runs migrations + seed SQL
- **Seed data:**
  - 1 admin user (`admin@test.com` / `Admin123!`)
  - 1 operator user, 1 supervisor
  - 2-3 test forms (tipos de trámite)
  - 5-10 records linked to those forms
  - Data sufficient for reports & exports
- **Config:** `.env.test` overrides `DB_DATABASE`, `DB_HOST`, etc.

## 5. Playwright Configuration

- Base URL: `http://localhost/coprodis/public`
- Browser: Chromium (expandable to Firefox/WebKit)
- Viewport: 1280x720
- Screenshot: only on failure
- Trace: retain on failure
- Timeout: 30s per test
- Retries: 1
- globalSetup: runs `reset-db.sh` before suite

## 6. Page Objects

Each page object encapsulates selectors and actions, keeping specs readable:

| Page Object | Key Methods |
|---|---|
| `LoginPage` | `goto()`, `login(email, password)`, `logout()` |
| `DashboardPage` | `isLoaded()`, `getWelcomeText()` |
| `FormBuilderPage` | `createForm(name)`, `addField(type, label)`, `publishForm()`, `deleteForm()` |
| `RecordsPage` | `createRecord(formName, data)`, `searchRecords(term)`, `deleteRecord()` |
| `ExportsPage` | `exportExcel()`, `exportCSV()`, `exportPDF()`, `getDownloadedFile()` |
| `ReportsPage` | `applyFilter(field, value)`, `verifyChartRendered()` |

## 7. Test Scenarios

### Auth (`auth.spec.js`)
- Successful login redirects to dashboard
- Invalid credentials show error message
- Logout redirects to login page
- Blocked user after X failed attempts

### Forms (`forms.spec.js`)
- Create a new form with a name
- Add text, number, date, select fields
- Preview form
- Edit existing form fields
- Publish/unpublish form
- Delete form

### Records (`records.spec.js`)
- Create a record from a form
- View record details
- Edit record fields
- Search/filter records (DataTables)
- Delete record (soft)
- View change history

### Exports (`exports.spec.js`)
- Export records as Excel
- Export records as CSV
- Export record as PDF
- Verify file is downloaded and contains expected data

### Reports (`reports.spec.js`)
- Generate report with date filter
- Generate report with category filter
- Verify Chart.js chart renders
- Export report as PDF

## 8. Execution Workflow

```bash
# 1. Reset test DB
bash tests/e2e/fixtures/reset-db.sh

# 2. Run all tests
npx playwright test

# 3. Run specific suite
npx playwright test tests/e2e/auth.spec.js

# 4. Run with UI mode (debug)
npx playwright test --ui
```

## 9. Future Extensions (Not in Scope)

- CI/CD integration (GitHub Actions)
- PHPUnit for unit/integration tests
- Visual regression testing (Playwright screenshot diff)
- API mocking for external services (PHPMailer)

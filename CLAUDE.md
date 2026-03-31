# MyAdmin Softaculous Licensing

PHP library for managing Softaculous, Webuzo, Virtualizor, and SiteMush licenses via the Softaculous NOC API. Part of the `detain/myadmin-*` plugin ecosystem.

## Commands

```bash
composer install                              # install dependencies
vendor/bin/phpunit --configuration phpunit.xml.dist  # run all tests
vendor/bin/phpunit --filter SoftaculousNOCTest       # run single test class
```

```bash
php -l src/Plugin.php                         # syntax check a single file
php -l src/SoftaculousNOC.php                 # syntax check API client
```

```bash
vendor/bin/phpunit --filter PluginTest         # run plugin hook tests
vendor/bin/phpunit --filter ArrayToXMLTest     # run XML utility tests
vendor/bin/phpunit --filter FileExistenceTest  # verify all expected files exist
```

## Architecture

**Namespace**: `Detain\MyAdminSoftaculous\` → `src/` · **Tests**: `Detain\MyAdminSoftaculous\Tests\` → `tests/`

**Core classes**:
- `src/SoftaculousNOC.php` — API client wrapping `https://www.softaculous.com/noc`. Methods: `buy()`, `cancel()`, `refund()`, `licenses()`, `editips()`, `licenselogs()`, `autorenewals()`, `addautorenewal()`, `removeautorenewal()`, `invoicedetails()`. Each product (Softaculous, Webuzo, Virtualizor, SiteMush) has prefixed variants (`webuzo_buy()`, `virt_buy()`, `sitemush_buy()`). All methods build `$this->params` then call `req()` which does the cURL request.
- `src/ArrayToXML.php` — XML↔array conversion utility (`toXML()`, `toArray()`, `isAssoc()`)
- `src/Plugin.php` — MyAdmin event-driven plugin. Registers hooks via `getHooks()`: `licenses.activate`, `licenses.deactivate`, `licenses.change_ip`, `licenses.settings`, `function.requirements`, `ui.menu`. Uses `Symfony\Component\EventDispatcher\GenericEvent`.

**Procedural files** (loaded via `function_requirements()`):
- `src/activate_softaculous.php` · `src/activate_webuzo.php` — license purchase/reuse logic
- `src/deactivate_softaculous.php` · `src/deactivate_webuzo.php` — cancel with refund
- `src/softaculous_list.php` · `src/webuzo_list.php` — admin UI license list pages

**CLI scripts** (`bin/`): `bin/cancel_by_ip.php`, `bin/cancel_by_key.php`, `bin/invoice_details.php`, `bin/license_by_ip.php`, `bin/license_logs.php`, `bin/license_refund.php`, `bin/licenses.php`, `bin/update_data.php`. All require `include/functions.inc.php` from the parent MyAdmin installation and instantiate `SoftaculousNOC` with constants `SOFTACULOUS_USERNAME`/`SOFTACULOUS_PASSWORD`.

## Testing

- PHPUnit 9 · Config: `phpunit.xml.dist` · Bootstrap: `tests/bootstrap.php`
- `tests/bootstrap.php` handles a quirk: `SoftaculousNOC.php` contains a duplicate `ArrayToXML` class at the bottom — bootstrap truncates it before loading
- Tests use reflection to inspect private properties and method signatures without making live API calls
- Test files: `tests/ArrayToXMLTest.php`, `tests/SoftaculousNOCTest.php`, `tests/PluginTest.php`, `tests/FileExistenceTest.php`
- CI: `.github/workflows/tests.yml` runs on PHP 8.2, 8.3, 8.4 via GitHub Actions

## Conventions

- Credentials via constants: `SOFTACULOUS_USERNAME`, `SOFTACULOUS_PASSWORD`, `WEBUZO_USERNAME`, `WEBUZO_PASSWORD`
- Never commit credentials — `tests/.env.example` shows expected env vars
- Logging: `myadmin_log($module, $level, $message, __LINE__, __FILE__)`
- API pattern: build `$this->params` array → call `$this->req()` → parse response
- Product method naming: base methods for Softaculous, `webuzo_*` for Webuzo, `virt_*` for Virtualizor, `sitemush_*` for SiteMush
- Plugin hooks: static methods accepting `GenericEvent`, check `get_service_define()` to match category, call `stopPropagation()` after handling
- Commit messages: lowercase, descriptive

## CI & Quality

- `.scrutinizer.yml` — static analysis and code coverage
- `.codeclimate.yml` — duplication, phpmd checks
- `.travis.yml` — legacy CI (PHP 5.4–7.1), superseded by GitHub Actions
- `.bettercodehub.yml` — BetterCodeHub language config

<!-- caliber:managed:pre-commit -->
## Before Committing

**IMPORTANT:** Before every git commit, you MUST ensure Caliber syncs agent configs with the latest code changes.

First, check if the pre-commit hook is already installed:
```bash
grep -q "caliber" .git/hooks/pre-commit 2>/dev/null && echo "hook-active" || echo "no-hook"
```

- If **hook-active**: the hook handles sync automatically — just commit normally. Tell the user: "Caliber will sync your agent configs automatically via the pre-commit hook."
- If **no-hook**: run Caliber manually before committing:
  1. Tell the user: "Caliber: Syncing agent configs with your latest changes..."
  2. Run: `caliber refresh && git add CLAUDE.md .claude/ .cursor/ .cursorrules .github/copilot-instructions.md .github/instructions/ AGENTS.md CALIBER_LEARNINGS.md .agents/ .opencode/ 2>/dev/null`
  3. After it completes, briefly tell the user what Caliber updated. Then proceed with the commit.

If `caliber` is not found, tell the user: "This project uses Caliber for agent config sync. Run /setup-caliber to get set up."
<!-- /caliber:managed:pre-commit -->

<!-- caliber:managed:learnings -->
## Session Learnings

Read `CALIBER_LEARNINGS.md` for patterns and anti-patterns learned from previous sessions.
These are auto-extracted from real tool usage — treat them as project-specific rules.
<!-- /caliber:managed:learnings -->

<!-- caliber:managed:sync -->
## Context Sync

This project uses [Caliber](https://github.com/caliber-ai-org/ai-setup) to keep AI agent configs in sync across Claude Code, Cursor, Copilot, and Codex.
Configs update automatically before each commit via `caliber refresh`.
If the pre-commit hook is not set up, run `/setup-caliber` to configure everything automatically.
<!-- /caliber:managed:sync -->

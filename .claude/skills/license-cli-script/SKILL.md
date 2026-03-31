---
name: license-cli-script
description: Creates a new CLI script in bin/ for Softaculous/Webuzo/Virtualizor/SiteMush license operations. Use when user says 'add CLI script', 'new bin command', 'create license tool', or 'add a script to bin/'. Do NOT use for src/ classes, test files, or Plugin hooks.
---
# License CLI Script

## Critical

- All CLI scripts go in `bin/` — never in `src/` or `tests/`.
- Every script MUST start with `#!/usr/bin/env php` shebang line.
- Require the parent MyAdmin bootstrap: `require_once __DIR__.'/../../../../include/functions.inc.php';`
- Use credential constants `SOFTACULOUS_USERNAME` / `SOFTACULOUS_PASSWORD` (or `WEBUZO_USERNAME` / `WEBUZO_PASSWORD` for Webuzo-specific scripts). Never hardcode credentials.
- CLI arguments come from `$_SERVER['argv']`, not `$argc`/`$argv`.
- Never add argument parsing libraries or option parsers — keep scripts minimal.

## Instructions

1. **Determine the API method to call.** Check `src/SoftaculousNOC.php` for available methods. Common ones:
   - `licenses($key, $ip)` — list/search licenses
   - `cancel($key, $ip)` — cancel a license
   - `refund($actid)` — refund a license
   - `licenselogs($key)` — get license logs
   - `invoicedetails($invoid)` — get invoice details
   - `editips($lid, $ips)` — change license IP
   - `autorenewals()` / `addautorenewal($key)` / `removeautorenewal($key)`
   - Prefixed variants: `webuzo_*`, `virt_*`, `sitemush_*`
   
   Verify the method exists and note its parameters before proceeding.

2. **Create the script file.** Name it `bin/<name>.php` using snake_case. Match the naming pattern of existing scripts:
   - `bin/cancel_by_ip.php`, `bin/cancel_by_key.php`, `bin/license_by_ip.php`
   - `bin/license_logs.php`, `bin/license_refund.php`, `bin/invoice_details.php`
   - `bin/licenses.php`, `bin/update_data.php`
   
   Verify the filename does not already exist in `bin/`.

3. **Write the script** following this exact template:

```php
#!/usr/bin/env php
<?php

require_once __DIR__.'/../../../../include/functions.inc.php';

$noc = new \Detain\MyAdminSoftaculous\SoftaculousNOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);

print_r($noc->methodName($_SERVER['argv'][1]));
print_r($noc->response);

//$GLOBALS['tf']->session->destroy();
```

   Key rules:
   - Use fully-qualified class name `\Detain\MyAdminSoftaculous\SoftaculousNOC` (no `use` statement).
   - If the method returns the result directly (like `licenses()`), just `print_r()` the return value — skip `print_r($noc->response)`. See `bin/licenses.php` for this pattern.
   - If the method requires side-effect inspection, print both the return value AND `$noc->response` (see `bin/cancel_by_key.php`, `bin/license_refund.php`).
   - CLI arguments map positionally: `$_SERVER['argv'][1]` is the first arg, `$_SERVER['argv'][2]` is the second, etc.
   - Keep the commented-out `$GLOBALS['tf']->session->destroy();` line at the bottom — this is the project convention.

4. **Verify the script.** Run `php -l bin/<name>.php` to check for syntax errors.

## Examples

### User says: "Add a CLI script to check auto-renewals for a license key"

**Actions:**
1. Confirm `autorenewals($key)` exists in `src/SoftaculousNOC.php`.
2. Create a new file in `bin/` (e.g., `bin/auto_renewals.php`):

```php
#!/usr/bin/env php
<?php

require_once __DIR__.'/../../../../include/functions.inc.php';

$noc = new \Detain\MyAdminSoftaculous\SoftaculousNOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);

print_r($noc->autorenewals($_SERVER['argv'][1]));
print_r($noc->response);

//$GLOBALS['tf']->session->destroy();
```

3. Run `php -l` on the new file — no errors.

**Result:** New script in `bin/` — usage: `php bin/auto_renewals.php <license_key>`

### User says: "Create a script to edit IPs on a Virtualizor license"

**Actions:**
1. Confirm `virt_editips($lid, $ips)` exists in `src/SoftaculousNOC.php`.
2. Create a new file in `bin/` (e.g., `bin/virt_edit_ips.php`):

```php
#!/usr/bin/env php
<?php

require_once __DIR__.'/../../../../include/functions.inc.php';

$noc = new \Detain\MyAdminSoftaculous\SoftaculousNOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);

print_r($noc->virt_editips($_SERVER['argv'][1], $_SERVER['argv'][2]));
print_r($noc->response);

//$GLOBALS['tf']->session->destroy();
```

3. Run `php -l` on the new file — no errors.

**Result:** New script in `bin/` — usage: `php bin/virt_edit_ips.php <license_id> <new_ip>`

## Common Issues

- **`PHP Fatal error: Uncaught Error: Undefined constant "SOFTACULOUS_USERNAME"`**: The `functions.inc.php` path is wrong. The `bin/` scripts use `__DIR__.'/../../../../include/functions.inc.php'` because the package is installed at `vendor/detain/myadmin-softaculous-licensing/`. Count the directory levels: `bin/` → package root → `detain/` → `vendor/` → project root → `include/`. Verify with `ls $(dirname bin/new_script.php)/../../../../include/functions.inc.php`.

- **`Undefined offset: 1` when running the script**: The user forgot to pass a CLI argument. The existing scripts do not validate arguments — this is intentional to keep them minimal. Do not add argument validation unless the user specifically asks for it.

- **Script runs but prints empty output**: Check `$noc->response` — the API may have returned an error. Also verify the credential constants are defined and non-empty in the MyAdmin configuration.

- **Using Webuzo credentials for Webuzo methods**: If calling `webuzo_*` methods, use `WEBUZO_USERNAME` and `WEBUZO_PASSWORD` instead of the Softaculous constants. Check `tests/.env.example` for the full list of expected credential constants.

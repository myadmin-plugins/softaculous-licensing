---
name: noc-api-method
description: Creates a new API method in SoftaculousNOC class following the existing pattern of building $this->params and calling $this->req(). Use when user says 'add API method', 'new NOC endpoint', or 'add softaculous/webuzo/virtualizor/sitemush method'. Do NOT use for modifying existing methods.
---
# NOC API Method

## Critical

- Every new method MUST follow the pattern: set `$this->params` entries then `return $this->req()`. No direct cURL calls.
- The `$this->params['ca']` value is the NOC action identifier. It follows the naming convention `{product}_{action}` (e.g., `softaculous_buy`, `webuzo_cancel`, `virtualizor_refund`, `sitemush_licenselogs`). The base Softaculous methods use `softaculous_` prefix, except `licenses()` which uses just `softaculous` and `autorenewals()`/`addautorenewal()`/`removeautorenewal()` which use `softaculous_renewals`.
- Product prefixes for `ca` values: `softaculous_`, `webuzo_`, `virtualizor_`, `sitemush_`.
- Methods for non-Softaculous products use a method name prefix: `webuzo_`, `virt_`, `sitemush_`.
- Return type is always `FALSE|array` for simple methods, or `bool|mixed` for compound methods like `refund_and_cancel()`.
- Never reset `$this->params` before setting values — the class expects each method to set its own keys fresh (the `req()` method reads `$this->params` directly).
- If adding a method for one product, you likely need the equivalent for all four products (Softaculous, Webuzo, Virtualizor, SiteMush) unless the endpoint is product-specific.

## Instructions

1. **Identify the NOC API action and product(s).** Determine the `ca` value from the Softaculous NOC API documentation. Map it to the correct product prefix.
   - Verify: Confirm the `ca` value doesn't already exist by searching `src/SoftaculousNOC.php` for it.

2. **Determine method placement in `src/SoftaculousNOC.php`.** Methods are grouped by product in this order:
   - Softaculous methods (lines ~126–349, no prefix)
   - Webuzo methods (after `// WEBUZO Functions` comment)
   - Virtualizor methods (after `// Virtualizor Functions` comment)
   - SiteMush methods (after `// SiteMush Functions` comment)
   - Invoice/misc methods at the end
   - Verify: Find the correct section comment before inserting.

3. **Write the method following this exact template:**

   ```php
   /**
    * Brief description of what this method does
    * NOTE: Any important caveats or constraints
    *
    * @param type $paramName Description
    * @return FALSE|array
    */
   public function method_name($param1, $param2 = '')
   {
       $this->params['ca'] = '{product}_{action}';
       $this->params['paramkey'] = $param1;
       $this->params['paramkey2'] = $param2;
       return $this->req();
   }
   ```

   Key patterns from existing code:
   - Simple getters (e.g., `licenses()`, `autorenewals()`): all params have defaults, return `$this->req()`
   - Action methods (e.g., `buy()`, `refund()`): required params have no defaults, return `$this->req()`
   - Conditional params: use `if (!empty($param)) { $this->params['key'] = $param; }` — see `licenselogs()` for the `$limit` pattern and `cancel()` for the `$force` pattern
   - Verify: PHPDoc `@param` and `@return` annotations match the method signature exactly.

4. **If the method is for all four products, create all four variants.** Follow the naming convention:

   | Product      | Method prefix | `ca` prefix       | Example method        | Example `ca`              |
   |-------------|--------------|-------------------|-----------------------|---------------------------|
   | Softaculous | *(none)*     | `softaculous_`    | `refund($actid)`      | `softaculous_refund`      |
   | Webuzo      | `webuzo_`    | `webuzo_`         | `webuzo_refund($actid)`| `webuzo_refund`           |
   | Virtualizor | `virt_`      | `virtualizor_`    | `virt_refund($actid)` | `virtualizor_refund`      |
   | SiteMush    | `sitemush_`  | `sitemush_`       | `sitemush_refund($actid)`| `sitemush_refund`       |

   Note: Virtualizor uses `virt_` for method names but `virtualizor_` for `ca` values.
   - Verify: Each variant is placed in its correct product section.

5. **Add the method(s) to the test file `tests/SoftaculousNOCTest.php`.**

   a. Add the method name to the `publicMethodProvider()` data provider array (around line 207):
   ```php
   'method_name' => ['method_name'],
   ```

   b. Add a parameter wiring test using the stubbed NOC pattern:
   ```php
   /**
    * Test method_name() sets correct parameters.
    */
   public function testMethodNameSetsCorrectParams(): void
   {
       $noc = $this->createStubbedNoc([]);
       $noc->method_name('value1', 'value2');

       $this->assertSame('product_action', $noc->params['ca']);
       $this->assertSame('value1', $noc->params['paramkey']);
       $this->assertSame('value2', $noc->params['paramkey2']);
   }
   ```

   c. For methods with conditional params (like `$force` or `$limit`), add a second test verifying the conditional:
   ```php
   public function testMethodNameWithOptionalParam(): void
   {
       $noc = $this->createStubbedNoc([]);
       $noc->method_name('value1', 10);

       $this->assertSame(10, $noc->params['limit']);
   }
   ```

   - Verify: Test names follow the pattern `test{PascalCaseMethodName}SetsCorrectParams`.

6. **Run the tests:**
   ```bash
   vendor/bin/phpunit --configuration phpunit.xml.dist --filter SoftaculousNOCTest
   ```
   - Verify: All tests pass, including the new ones and all existing tests.

## Examples

### Adding a `showlicense` method for all products

User says: "Add a showlicense method that retrieves license details by license ID"

Actions taken:
1. Check `src/SoftaculousNOC.php` — no existing `showlicense()` method (note: `editips()` uses `softaculous_showlicense` as the `ca` but is an edit operation).
2. Add to Softaculous section:
```php
/**
 * Show details of a License
 *
 * @param int $lid The License ID (NOT the license key)
 * @return FALSE|array
 */
public function showlicense($lid)
{
    $this->params['ca'] = 'softaculous_showlicense';
    $this->params['lid'] = $lid;
    return $this->req();
}
```
3. Add `webuzo_showlicense()`, `virt_showlicense()`, `sitemush_showlicense()` in their respective sections with `ca` values `webuzo_showlicense`, `virtualizor_showlicense`, `sitemush_showlicense`.
4. Add all four to `publicMethodProvider()`.
5. Add parameter wiring tests for each variant.
6. Run `vendor/bin/phpunit --configuration phpunit.xml.dist --filter SoftaculousNOCTest` — all pass.

### Adding a compound method (refund_and_cancel pattern)

User says: "Add a method that suspends and then cancels a license"

Actions taken:
1. The compound method calls other methods internally (like `refund_and_cancel()` calls `licenses()`, `licenselogs()`, `refund()`, then `cancel()`).
2. Follow the exact pattern from `refund_and_cancel()` in `src/SoftaculousNOC.php:224`:
   - Accept `$key` and `$ip` params
   - If `$ip` provided, look up license via `licenses('', $ip)`
   - Validate `$key` is not empty, push to `$this->error[]` and return `false` if missing
   - Call the action methods
   - Return type is `bool|mixed`
3. Create all four product variants, each calling its own product-specific sub-methods.

## Common Issues

**If you see `Call to undefined method SoftaculousNOC::method_name()`:**
1. Verify the method is inside the `SoftaculousNOC` class braces in `src/SoftaculousNOC.php`
2. Check for syntax errors above the new method that might break the class — `php -l src/SoftaculousNOC.php`

**If tests fail with `Failed asserting that two strings are identical` on `$noc->params['ca']`:**
1. You likely have a typo in the `ca` value. Cross-check against the product prefix table in Step 4.
2. Virtualizor is a common mistake: method prefix is `virt_` but `ca` prefix is `virtualizor_` (with an 'o').

**If `publicMethodProvider` test fails with `Method X should exist`:**
1. Verify the method name string in the data provider matches the actual method name exactly (case-sensitive).

**If `bootstrap.php` causes class loading errors:**
1. The bootstrap file (`tests/bootstrap.php`) truncates a duplicate `ArrayToXML` class from the bottom of `SoftaculousNOC.php`. Do NOT add methods after the duplicate `ArrayToXML` class definition at the bottom of the file — always add them inside the `SoftaculousNOC` class body above it.
2. Check the file ends correctly: `php -l src/SoftaculousNOC.php`

**If the `req()` stub doesn't capture your params:**
1. Ensure your method sets `$this->params` BEFORE calling `$this->req()`. The stubbed `req()` in tests doesn't read params, but the real `req()` builds the URL from `$this->params` — if params are set after `req()`, the API call will be wrong.
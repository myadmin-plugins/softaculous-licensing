---
name: plugin-hook
description: Wires a new event hook into src/Plugin.php following the GenericEvent pattern with get_service_define() category checks and stopPropagation(). Use when user says 'add hook', 'new event handler', 'add plugin event', or 'register license event'. Do NOT use for CLI scripts or API client methods.
---
# Plugin Hook

Add event hooks to `src/Plugin.php` following the established GenericEvent dispatcher pattern used across the `detain/myadmin-*` plugin ecosystem.

## Critical

- Every handler method MUST be `public static` and accept exactly one `\Symfony\Component\EventDispatcher\GenericEvent $event` parameter.
- Every handler MUST check `$event['category'] == get_service_define('CONSTANT')` before doing any work. If the category does not match, the method MUST silently return — never call `stopPropagation()` on a non-matching category.
- `$event->stopPropagation()` MUST be the last call inside each matched category block. This prevents other plugins from double-handling the same event.
- The hook MUST be registered in `getHooks()` before the handler method will be called. Forgetting this step is the #1 cause of "my handler never fires."
- Use `self::$module` (value: `'licenses'`) when building event names — never hardcode the module string.

## Instructions

### Step 1: Register the hook in `getHooks()`

Open `src/Plugin.php` and add an entry to the array returned by `getHooks()`.

Event name format: `self::$module.'.event_name'` for module-scoped events, or a bare string like `'function.requirements'` or `'ui.menu'` for cross-module events.

Handler format: `[__CLASS__, 'methodName']`

```php
public static function getHooks()
{
    return [
        // existing hooks...
        self::$module.'.your_event' => [__CLASS__, 'getYourEvent'],
    ];
}
```

If multiple event names should share one handler (like `activate` and `reactivate`), add separate entries pointing to the same method:

```php
self::$module.'.activate' => [__CLASS__, 'getActivate'],
self::$module.'.reactivate' => [__CLASS__, 'getActivate'],
```

**Verify:** The new key exists in the returned array. Run `vendor/bin/phpunit --filter testGetHooksContainsRequiredEvents` — it will fail until the test is also updated (Step 4), but confirms the hook array parses without syntax errors.

### Step 2: Write the handler method

Add a new `public static` method to the `Plugin` class. Follow the pattern established in `src/Plugin.php` by existing handlers like `getActivate` and `getDeactivate`:

```php
/**
 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
 */
public static function getYourEvent(GenericEvent $event)
{
    $serviceClass = $event->getSubject();
    if ($event['category'] == get_service_define('SOFTACULOUS')) {
        myadmin_log(self::$module, 'info', 'Softaculous YourEvent', __LINE__, __FILE__, self::$module, $serviceClass->getId());
        // ... your logic here ...
        $event->stopPropagation();
    } elseif ($event['category'] == get_service_define('WEBUZO')) {
        myadmin_log(self::$module, 'info', 'Webuzo YourEvent', __LINE__, __FILE__, self::$module, $serviceClass->getId());
        // ... your logic here ...
        $event->stopPropagation();
    }
}
```

Key patterns from existing handlers:

| Pattern | Example from codebase |
|---|---|
| Get the service object | `$serviceClass = $event->getSubject();` |
| Check product category | `$event['category'] == get_service_define('SOFTACULOUS')` |
| Log the action | `myadmin_log(self::$module, 'info', 'Description', __LINE__, __FILE__, self::$module, $serviceClass->getId());` |
| Load a procedural file | `function_requirements('activate_softaculous');` |
| Call the loaded function | `$response = activate_softaculous($serviceClass->getIp(), $event['field1'], $event['email']);` |
| Update the service | `$serviceClass->setKey($response)->save();` |
| Set event result | `$event['success'] = deactivate_softaculous($serviceClass->getIp());` |
| Stop other plugins | `$event->stopPropagation();` |

**Verify:** The method signature is `public static function getYourEvent(GenericEvent $event)`. Run `php -l src/Plugin.php` to check for syntax errors.

### Step 3: Add procedural implementation file (if needed)

If the handler delegates to a procedural function, create a new file in `src/` following the pattern of existing files like `src/activate_softaculous.php` and `src/deactivate_softaculous.php`:

```php
<?php
/**
 * @param string $ip
 * @return string|false  license key or false on failure
 */
function your_event_softaculous($ip)
{
    $noc = new \Detain\MyAdminSoftaculous\SoftaculousNOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);
    // API call logic...
}
```

Then register it in the `getRequirements` handler in `src/Plugin.php`:

```php
$loader->add_requirement('your_event_softaculous', '/../vendor/detain/myadmin-softaculous-licensing/src/your_event_softaculous.php');
```

**Verify:** The file path in `add_requirement()` matches the actual file location relative to the MyAdmin include directory.

### Step 4: Update tests

Edit `tests/PluginTest.php`:

1. Add the new event key to `testGetHooksContainsRequiredEvents`:

```php
$expectedKeys = [
    // existing keys...
    'licenses.your_event',
];
```

2. Add the method name to `hookMethodProvider`:

```php
public function hookMethodProvider(): array
{
    return [
        // existing entries...
        'getYourEvent' => ['getYourEvent'],
    ];
}
```

3. Add a static analysis test for the new method:

```php
public function testGetYourEventReferencesExpectedFunctions(): void
{
    $ref = new \ReflectionMethod(Plugin::class, 'getYourEvent');
    $source = $this->getMethodSource($ref);

    $this->assertStringContainsString('get_service_define', $source);
    $this->assertStringContainsString('SOFTACULOUS', $source);
    $this->assertStringContainsString('stopPropagation', $source);
    $this->assertStringContainsString('myadmin_log', $source);
}
```

**Verify:** Run `vendor/bin/phpunit --configuration phpunit.xml.dist` — all tests should pass.

## Examples

### User says: "Add a hook for license renewal"

**Actions taken:**

1. In `src/Plugin.php`, add to `getHooks()`:
   ```php
   self::$module.'.renew' => [__CLASS__, 'getRenew'],
   ```

2. Add handler method:
   ```php
   public static function getRenew(GenericEvent $event)
   {
       $serviceClass = $event->getSubject();
       if ($event['category'] == get_service_define('SOFTACULOUS')) {
           myadmin_log(self::$module, 'info', 'Softaculous Renewal', __LINE__, __FILE__, self::$module, $serviceClass->getId());
           $noc = new \Detain\MyAdminSoftaculous\SoftaculousNOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);
           $response = $noc->buy($serviceClass->getIp(), '1M', 1, $event['email'], 1);
           if ($response !== false) {
               $event['success'] = true;
           }
           $event->stopPropagation();
       } elseif ($event['category'] == get_service_define('WEBUZO')) {
           myadmin_log(self::$module, 'info', 'Webuzo Renewal', __LINE__, __FILE__, self::$module, $serviceClass->getId());
           $noc = new \Detain\MyAdminSoftaculous\SoftaculousNOC(WEBUZO_USERNAME, WEBUZO_PASSWORD);
           $response = $noc->webuzo_buy($serviceClass->getIp(), '1M', 0, $event['email']);
           if ($response !== false) {
               $event['success'] = true;
           }
           $event->stopPropagation();
       }
   }
   ```

3. Update `tests/PluginTest.php` — add `'licenses.renew'` to expected keys, `'getRenew'` to hookMethodProvider, and a static analysis test.

**Result:** `vendor/bin/phpunit --configuration phpunit.xml.dist` passes. The `licenses.renew` event is now handled by this plugin for both Softaculous and Webuzo product types.

## Common Issues

**Handler never fires:**
1. Check `getHooks()` in `src/Plugin.php` — is the event name exactly `self::$module.'.event_name'`? A missing dot or typo means the dispatcher never connects the handler.
2. Verify `get_service_define('CONSTANT')` returns the expected integer. If the constant is not defined, the category check silently fails. Test with: `php -r "require 'include/functions.inc.php'; var_dump(get_service_define('SOFTACULOUS'));"`

**Handler fires but does nothing:**
1. Check that `$event['category']` is populated. Some events may not set this key — inspect the caller with `var_dump($event->getArguments());`.
2. If using `function_requirements('func_name')`, verify the function is registered in `getRequirements`. Missing registration causes a fatal "Call to undefined function" error.

**"Call to undefined function function_requirements()":**
This function is only available in the full MyAdmin runtime. Unit tests use reflection-based static analysis instead of executing handlers directly. Never call `function_requirements()` in test code.

**stopPropagation() called but other plugins still run:**
The event dispatcher processes listeners in priority order. If another plugin registered with a higher priority (lower number), it runs first. Check `plugins.json` for listener ordering.

**Test fails with "Hook 'licenses.xyz' should be registered":**
You added the handler method but forgot to add the entry in `getHooks()`. Add the `self::$module.'.xyz' => [__CLASS__, 'getXyz']` line to the returned array.

**PHPUnit error "Method getXyz should exist":**
The `hookMethodProvider` data provider references a method that doesn't exist yet. Ensure the method name in `hookMethodProvider` exactly matches the method name in `src/Plugin.php` (case-sensitive).

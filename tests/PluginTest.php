<?php

declare(strict_types=1);

namespace Detain\MyAdminSoftaculous\Tests;

use Detain\MyAdminSoftaculous\Plugin;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Unit tests for the Plugin class.
 *
 * Tests class structure, static properties, hook registration,
 * and event handler method signatures. DB/global-dependent logic
 * is tested via static analysis rather than execution.
 *
 * @covers \Detain\MyAdminSoftaculous\Plugin
 */
class PluginTest extends TestCase
{
    // ---------------------------------------------------------------
    //  Instantiation
    // ---------------------------------------------------------------

    /**
     * Test the Plugin class can be instantiated.
     */
    public function testCanBeInstantiated(): void
    {
        $plugin = new Plugin();

        $this->assertInstanceOf(Plugin::class, $plugin);
    }

    // ---------------------------------------------------------------
    //  Static properties
    // ---------------------------------------------------------------

    /**
     * Test $name static property is set correctly.
     */
    public function testNameProperty(): void
    {
        $this->assertSame('Softaculous Licensing', Plugin::$name);
    }

    /**
     * Test $description static property is a non-empty string.
     */
    public function testDescriptionProperty(): void
    {
        $this->assertIsString(Plugin::$description);
        $this->assertNotEmpty(Plugin::$description);
    }

    /**
     * Test $help static property is a non-empty string.
     */
    public function testHelpProperty(): void
    {
        $this->assertIsString(Plugin::$help);
        $this->assertNotEmpty(Plugin::$help);
    }

    /**
     * Test $module static property.
     */
    public function testModuleProperty(): void
    {
        $this->assertSame('licenses', Plugin::$module);
    }

    /**
     * Test $type static property.
     */
    public function testTypeProperty(): void
    {
        $this->assertSame('service', Plugin::$type);
    }

    // ---------------------------------------------------------------
    //  getHooks()
    // ---------------------------------------------------------------

    /**
     * Test getHooks() returns an array.
     */
    public function testGetHooksReturnsArray(): void
    {
        $hooks = Plugin::getHooks();

        $this->assertIsArray($hooks);
        $this->assertNotEmpty($hooks);
    }

    /**
     * Test getHooks() contains required event keys.
     */
    public function testGetHooksContainsRequiredEvents(): void
    {
        $hooks = Plugin::getHooks();

        $expectedKeys = [
            'licenses.settings',
            'licenses.activate',
            'licenses.reactivate',
            'licenses.deactivate',
            'licenses.deactivate_ip',
            'licenses.change_ip',
            'function.requirements',
            'ui.menu',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $hooks, "Hook '{$key}' should be registered");
        }
    }

    /**
     * Test getHooks() values are valid callable references (class + method arrays).
     */
    public function testGetHooksValuesAreCallableReferences(): void
    {
        $hooks = Plugin::getHooks();

        foreach ($hooks as $event => $handler) {
            $this->assertIsArray($handler, "Handler for '{$event}' should be an array");
            $this->assertCount(2, $handler, "Handler for '{$event}' should have class and method");
            $this->assertSame(Plugin::class, $handler[0], "Handler class for '{$event}' should be Plugin");
            $this->assertIsString($handler[1], "Handler method for '{$event}' should be a string");
        }
    }

    /**
     * Test activate and reactivate map to the same handler.
     */
    public function testActivateAndReactivateUseSameHandler(): void
    {
        $hooks = Plugin::getHooks();

        $this->assertSame(
            $hooks['licenses.activate'],
            $hooks['licenses.reactivate']
        );
    }

    /**
     * Test deactivate and deactivate_ip map to the same handler.
     */
    public function testDeactivateAndDeactivateIpUseSameHandler(): void
    {
        $hooks = Plugin::getHooks();

        $this->assertSame(
            $hooks['licenses.deactivate'],
            $hooks['licenses.deactivate_ip']
        );
    }

    // ---------------------------------------------------------------
    //  Method existence and signatures
    // ---------------------------------------------------------------

    /**
     * Verify all hook handler methods exist as static methods.
     *
     * @dataProvider hookMethodProvider
     */
    public function testHookMethodExists(string $methodName): void
    {
        $ref = new ReflectionClass(Plugin::class);

        $this->assertTrue($ref->hasMethod($methodName), "Method {$methodName} should exist");
        $this->assertTrue($ref->getMethod($methodName)->isStatic(), "Method {$methodName} should be static");
        $this->assertTrue($ref->getMethod($methodName)->isPublic(), "Method {$methodName} should be public");
    }

    /**
     * Data provider for hook handler methods.
     *
     * @return array<string, array{string}>
     */
    public function hookMethodProvider(): array
    {
        return [
            'getSettings' => ['getSettings'],
            'getActivate' => ['getActivate'],
            'getDeactivate' => ['getDeactivate'],
            'getChangeIp' => ['getChangeIp'],
            'getRequirements' => ['getRequirements'],
            'getMenu' => ['getMenu'],
        ];
    }

    /**
     * Verify event handler methods accept GenericEvent parameter.
     *
     * @dataProvider hookMethodProvider
     */
    public function testHookMethodsAcceptGenericEvent(string $methodName): void
    {
        $ref = new \ReflectionMethod(Plugin::class, $methodName);
        $params = $ref->getParameters();

        $this->assertCount(1, $params, "Method {$methodName} should accept exactly one parameter");
        $this->assertNotNull(
            $params[0]->getType(),
            "Parameter of {$methodName} should have a type hint"
        );
    }

    // ---------------------------------------------------------------
    //  Static analysis: method internals reference expected functions
    // ---------------------------------------------------------------

    /**
     * Test getActivate references expected external functions.
     */
    public function testGetActivateReferencesExpectedFunctions(): void
    {
        $ref = new \ReflectionMethod(Plugin::class, 'getActivate');
        $startLine = $ref->getStartLine();
        $endLine = $ref->getEndLine();
        $filename = $ref->getFileName();

        $source = implode('', array_slice(
            file($filename),
            $startLine - 1,
            $endLine - $startLine + 1
        ));

        $this->assertStringContainsString('get_service_define', $source);
        $this->assertStringContainsString('SOFTACULOUS', $source);
        $this->assertStringContainsString('WEBUZO', $source);
        $this->assertStringContainsString('activate_softaculous', $source);
        $this->assertStringContainsString('activate_webuzo', $source);
        $this->assertStringContainsString('myadmin_log', $source);
        $this->assertStringContainsString('stopPropagation', $source);
    }

    /**
     * Test getDeactivate references expected external functions.
     */
    public function testGetDeactivateReferencesExpectedFunctions(): void
    {
        $ref = new \ReflectionMethod(Plugin::class, 'getDeactivate');
        $source = $this->getMethodSource($ref);

        $this->assertStringContainsString('deactivate_softaculous', $source);
        $this->assertStringContainsString('deactivate_webuzo', $source);
        $this->assertStringContainsString('stopPropagation', $source);
    }

    /**
     * Test getChangeIp references expected classes and functions.
     */
    public function testGetChangeIpReferencesExpectedItems(): void
    {
        $ref = new \ReflectionMethod(Plugin::class, 'getChangeIp');
        $source = $this->getMethodSource($ref);

        $this->assertStringContainsString('SoftaculousNOC', $source);
        $this->assertStringContainsString('editips', $source);
        $this->assertStringContainsString('stopPropagation', $source);
    }

    /**
     * Test getRequirements registers all expected files.
     */
    public function testGetRequirementsRegistersExpectedFiles(): void
    {
        $ref = new \ReflectionMethod(Plugin::class, 'getRequirements');
        $source = $this->getMethodSource($ref);

        $this->assertStringContainsString('activate_softaculous', $source);
        $this->assertStringContainsString('activate_webuzo', $source);
        $this->assertStringContainsString('deactivate_softaculous', $source);
        $this->assertStringContainsString('deactivate_webuzo', $source);
        $this->assertStringContainsString('softaculous_list', $source);
        $this->assertStringContainsString('webuzo_list', $source);
    }

    /**
     * Test getSettings references expected setting types.
     */
    public function testGetSettingsReferencesExpectedSettings(): void
    {
        $ref = new \ReflectionMethod(Plugin::class, 'getSettings');
        $source = $this->getMethodSource($ref);

        $this->assertStringContainsString('add_text_setting', $source);
        $this->assertStringContainsString('add_password_setting', $source);
        $this->assertStringContainsString('add_dropdown_setting', $source);
        $this->assertStringContainsString('SOFTACULOUS_USERNAME', $source);
        $this->assertStringContainsString('SOFTACULOUS_PASSWORD', $source);
        $this->assertStringContainsString('WEBUZO_USERNAME', $source);
        $this->assertStringContainsString('WEBUZO_PASSWORD', $source);
    }

    /**
     * Test getMenu references expected menu items.
     */
    public function testGetMenuReferencesExpectedItems(): void
    {
        $ref = new \ReflectionMethod(Plugin::class, 'getMenu');
        $source = $this->getMethodSource($ref);

        $this->assertStringContainsString('softaculous_list', $source);
        $this->assertStringContainsString('webuzo_list', $source);
        $this->assertStringContainsString('add_link', $source);
    }

    // ---------------------------------------------------------------
    //  Helper
    // ---------------------------------------------------------------

    /**
     * Extract the source code of a method as a string.
     *
     * @param \ReflectionMethod $ref
     * @return string
     */
    private function getMethodSource(\ReflectionMethod $ref): string
    {
        $startLine = $ref->getStartLine();
        $endLine = $ref->getEndLine();
        $filename = $ref->getFileName();

        return implode('', array_slice(
            file($filename),
            $startLine - 1,
            $endLine - $startLine + 1
        ));
    }
}

<?php

declare(strict_types=1);

namespace Detain\MyAdminSoftaculous\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Verifies that all expected source files exist in the package.
 *
 * @coversNothing
 */
class FileExistenceTest extends TestCase
{
    /** @var string */
    private static $srcDir;

    /**
     * Set up the source directory path.
     */
    public static function setUpBeforeClass(): void
    {
        self::$srcDir = dirname(__DIR__) . '/src';
    }

    /**
     * Verify expected source files exist.
     *
     * @dataProvider sourceFileProvider
     */
    public function testSourceFileExists(string $filename): void
    {
        $path = self::$srcDir . '/' . $filename;

        $this->assertFileExists($path, "Source file {$filename} should exist");
    }

    /**
     * Data provider for expected source files.
     *
     * @return array<string, array{string}>
     */
    public function sourceFileProvider(): array
    {
        return [
            'Plugin.php' => ['Plugin.php'],
            'SoftaculousNOC.php' => ['SoftaculousNOC.php'],
            'ArrayToXML.php' => ['ArrayToXML.php'],
            'activate_softaculous.php' => ['activate_softaculous.php'],
            'activate_webuzo.php' => ['activate_webuzo.php'],
            'deactivate_softaculous.php' => ['deactivate_softaculous.php'],
            'deactivate_webuzo.php' => ['deactivate_webuzo.php'],
            'softaculous_list.php' => ['softaculous_list.php'],
            'webuzo_list.php' => ['webuzo_list.php'],
        ];
    }

    /**
     * Verify composer.json exists in the package root.
     */
    public function testComposerJsonExists(): void
    {
        $this->assertFileExists(dirname(__DIR__) . '/composer.json');
    }

    /**
     * Verify the PSR-4 namespace maps to the src directory.
     */
    public function testPsr4NamespaceMapping(): void
    {
        $composerJson = json_decode(
            file_get_contents(dirname(__DIR__) . '/composer.json'),
            true
        );

        $this->assertArrayHasKey('autoload', $composerJson);
        $this->assertArrayHasKey('psr-4', $composerJson['autoload']);
        $this->assertArrayHasKey(
            'Detain\\MyAdminSoftaculous\\',
            $composerJson['autoload']['psr-4']
        );
        $this->assertSame(
            'src/',
            $composerJson['autoload']['psr-4']['Detain\\MyAdminSoftaculous\\']
        );
    }

    /**
     * Verify all source files are readable and non-empty.
     *
     * @dataProvider sourceFileProvider
     */
    public function testSourceFilesAreReadable(string $filename): void
    {
        $path = self::$srcDir . '/' . $filename;

        $this->assertFileIsReadable($path, "Source file {$filename} should be readable");
        $this->assertGreaterThan(
            0,
            filesize($path),
            "Source file {$filename} should not be empty"
        );
    }

    /**
     * Verify all source files start with a valid PHP opening tag.
     *
     * @dataProvider sourceFileProvider
     */
    public function testSourceFilesStartWithPhpTag(string $filename): void
    {
        $path = self::$srcDir . '/' . $filename;
        $content = file_get_contents($path);

        $this->assertStringStartsWith(
            '<?php',
            $content,
            "Source file {$filename} should start with <?php tag"
        );
    }
}

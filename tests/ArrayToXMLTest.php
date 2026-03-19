<?php

declare(strict_types=1);

namespace Detain\MyAdminSoftaculous\Tests;

use Detain\MyAdminSoftaculous\ArrayToXML;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the ArrayToXML class.
 *
 * @covers \Detain\MyAdminSoftaculous\ArrayToXML
 */
class ArrayToXMLTest extends TestCase
{
    /** @var ArrayToXML */
    private $converter;

    /**
     * Set up test fixtures.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->converter = new ArrayToXML();
    }

    // ---------------------------------------------------------------
    //  Class structure
    // ---------------------------------------------------------------

    /**
     * Verify the class can be instantiated.
     */
    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(ArrayToXML::class, $this->converter);
    }

    /**
     * Verify all expected public methods exist.
     */
    public function testExpectedMethodsExist(): void
    {
        $ref = new \ReflectionClass(ArrayToXML::class);
        $methods = array_map(
            static fn(\ReflectionMethod $m): string => $m->getName(),
            $ref->getMethods(\ReflectionMethod::IS_PUBLIC)
        );

        $this->assertContains('toXML', $methods);
        $this->assertContains('toArray', $methods);
        $this->assertContains('isAssoc', $methods);
    }

    // ---------------------------------------------------------------
    //  toXML()
    // ---------------------------------------------------------------

    /**
     * Test toXML produces valid XML from a simple associative array.
     */
    public function testToXmlSimpleAssociativeArray(): void
    {
        $data = ['name' => 'John', 'age' => '30'];
        $xml = $this->converter->toXML($data);

        $this->assertIsString($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('<name>John</name>', $xml);
        $this->assertStringContainsString('<age>30</age>', $xml);
    }

    /**
     * Test toXML uses the custom root node name.
     */
    public function testToXmlUsesCustomRootNodeName(): void
    {
        $data = ['item' => 'test'];
        $xml = $this->converter->toXML($data, 'CustomRoot');

        $this->assertStringContainsString('<CustomRoot', $xml);
    }

    /**
     * Test toXML defaults to ResultSet root node.
     */
    public function testToXmlDefaultsToResultSetRoot(): void
    {
        $data = ['key' => 'val'];
        $xml = $this->converter->toXML($data);

        $this->assertStringContainsString('<ResultSet', $xml);
    }

    /**
     * Test toXML with nested arrays.
     */
    public function testToXmlNestedArray(): void
    {
        $data = [
            'person' => [
                'name' => 'Alice',
                'email' => 'alice@example.com',
            ],
        ];
        $xml = $this->converter->toXML($data);

        $this->assertStringContainsString('<person>', $xml);
        $this->assertStringContainsString('<name>Alice</name>', $xml);
    }

    /**
     * Test toXML with numeric keys converts them properly.
     */
    public function testToXmlWithNumericKeys(): void
    {
        $data = ['first', 'second', 'third'];
        $xml = $this->converter->toXML($data, 'Items');

        $this->assertIsString($xml);
        $this->assertStringContainsString('<Items', $xml);
    }

    /**
     * Test toXML handles HTML entities in values.
     */
    public function testToXmlEscapesHtmlEntities(): void
    {
        $data = ['content' => 'Tom & Jerry <friends>'];
        $xml = $this->converter->toXML($data);

        // The value should be escaped; raw < or & should not appear in the value
        $this->assertStringContainsString('&amp;', $xml);
    }

    /**
     * Test toXML strips invalid XML element name characters.
     */
    public function testToXmlStripsInvalidCharactersFromKeys(): void
    {
        $data = ['valid-key' => 'yes', 'inv@lid!' => 'stripped'];
        $xml = $this->converter->toXML($data);

        $this->assertStringContainsString('<valid-key>', $xml);
        // The @ and ! should be stripped, leaving 'invlid'
        $this->assertStringContainsString('<invlid>', $xml);
    }

    /**
     * Test toXML with empty array produces valid XML.
     */
    public function testToXmlEmptyArray(): void
    {
        $xml = $this->converter->toXML([]);

        $this->assertIsString($xml);
        $this->assertStringContainsString('<?xml', $xml);
    }

    // ---------------------------------------------------------------
    //  toArray()
    // ---------------------------------------------------------------

    /**
     * Test toArray converts a simple XML string to array.
     */
    public function testToArraySimpleXml(): void
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?><Root><name>John</name><age>30</age></Root>';
        $result = $this->converter->toArray($xml);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertSame('John', $result['name']);
        $this->assertSame('30', $result['age']);
    }

    /**
     * Test toArray accepts a SimpleXMLElement object.
     */
    public function testToArrayAcceptsSimpleXmlElement(): void
    {
        $xmlObj = new \SimpleXMLElement('<Root><item>test</item></Root>');
        $result = $this->converter->toArray($xmlObj);

        $this->assertIsArray($result);
        $this->assertSame('test', $result['item']);
    }

    /**
     * Test toArray with nested XML.
     */
    public function testToArrayNestedXml(): void
    {
        $xml = '<Root><person><name>Alice</name><age>25</age></person></Root>';
        $result = $this->converter->toArray($xml);

        $this->assertIsArray($result);
        $this->assertIsArray($result['person']);
        $this->assertSame('Alice', $result['person']['name']);
    }

    /**
     * Test toArray handles 'anon' keys for non-associative arrays.
     */
    public function testToArrayHandlesAnonKeys(): void
    {
        $xml = '<Root><anon>first</anon><anon>second</anon></Root>';
        $result = $this->converter->toArray($xml);

        $this->assertIsArray($result);
        $this->assertArrayHasKey(0, $result);
        $this->assertArrayHasKey(1, $result);
        $this->assertSame('first', $result[0]);
        $this->assertSame('second', $result[1]);
    }

    /**
     * Test toArray handles duplicate keys by collecting them into array.
     */
    public function testToArrayHandlesDuplicateKeys(): void
    {
        $xml = '<Root><item>one</item><item>two</item></Root>';
        $result = $this->converter->toArray($xml);

        $this->assertIsArray($result['item']);
        $this->assertCount(2, $result['item']);
    }

    /**
     * Test toArray returns string for leaf nodes.
     */
    public function testToArrayReturnsStringForLeafNode(): void
    {
        $xml = '<Root>plain text</Root>';
        $xmlObj = new \SimpleXMLElement($xml);
        $child = $xmlObj; // Root itself has no children with sub-elements
        $result = $this->converter->toArray($xmlObj);

        // When there are no children, it returns a string
        $this->assertIsString($result);
        $this->assertSame('plain text', $result);
    }

    // ---------------------------------------------------------------
    //  Roundtrip
    // ---------------------------------------------------------------

    /**
     * Test that toXML -> toArray roundtrip preserves data.
     */
    public function testRoundtripToXmlToArray(): void
    {
        $data = [
            'name' => 'Test',
            'value' => '42',
            'nested' => [
                'a' => '1',
                'b' => '2',
            ],
        ];

        $xml = $this->converter->toXML($data, 'Data');
        $result = $this->converter->toArray($xml);

        $this->assertSame('Test', $result['name']);
        $this->assertSame('42', $result['value']);
        $this->assertSame('1', $result['nested']['a']);
        $this->assertSame('2', $result['nested']['b']);
    }

    // ---------------------------------------------------------------
    //  isAssoc()
    // ---------------------------------------------------------------

    /**
     * Test isAssoc returns true for associative arrays.
     */
    public function testIsAssocReturnsTrueForAssociativeArray(): void
    {
        $this->assertTrue($this->converter->isAssoc(['a' => 1, 'b' => 2]));
    }

    /**
     * Test isAssoc returns false for sequential arrays.
     */
    public function testIsAssocReturnsFalseForSequentialArray(): void
    {
        $this->assertFalse($this->converter->isAssoc([1, 2, 3]));
    }

    /**
     * Test isAssoc returns false for empty array.
     */
    public function testIsAssocReturnsFalseForEmptyArray(): void
    {
        $this->assertFalse($this->converter->isAssoc([]));
    }

    /**
     * Test isAssoc returns true for mixed key arrays.
     */
    public function testIsAssocReturnsTrueForMixedKeys(): void
    {
        $this->assertTrue($this->converter->isAssoc([0 => 'a', 'key' => 'b']));
    }

    /**
     * Test isAssoc returns false for non-array input.
     */
    public function testIsAssocReturnsFalseForNonArray(): void
    {
        $this->assertFalse($this->converter->isAssoc('not an array'));
    }
}

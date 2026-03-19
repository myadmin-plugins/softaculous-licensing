<?php

declare(strict_types=1);

namespace Detain\MyAdminSoftaculous\Tests;

use Detain\MyAdminSoftaculous\SoftaculousNOC;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

/**
 * Unit tests for the SoftaculousNOC class.
 *
 * All API methods delegate to req() which makes HTTP calls, so we test
 * parameter wiring via reflection rather than making live requests.
 *
 * @covers \Detain\MyAdminSoftaculous\SoftaculousNOC
 */
class SoftaculousNOCTest extends TestCase
{
    /** @var SoftaculousNOC */
    private $noc;

    /**
     * Set up test fixtures.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->noc = new SoftaculousNOC('testuser', 'testpass');
    }

    // ---------------------------------------------------------------
    //  Helper: read private property
    // ---------------------------------------------------------------

    /**
     * Read a private/protected property via reflection.
     *
     * @param object $object
     * @param string $property
     * @return mixed
     */
    private function getPrivateProperty(object $object, string $property)
    {
        $ref = new ReflectionProperty(get_class($object), $property);
        $ref->setAccessible(true);
        return $ref->getValue($object);
    }

    // ---------------------------------------------------------------
    //  Helper: create a SoftaculousNOC subclass that stubs req()
    // ---------------------------------------------------------------

    /**
     * Create a testable NOC instance whose req() returns a canned response
     * instead of making real HTTP calls.
     *
     * @param array|false $cannedResponse
     * @return SoftaculousNOC
     */
    private function createStubbedNoc($cannedResponse = [])
    {
        return new class('testuser', 'testpass', '', 0, $cannedResponse) extends SoftaculousNOC {
            /** @var array|false */
            private $cannedResponse;

            public function __construct(string $user, string $pass, string $url, int $json, $canned)
            {
                parent::__construct($user, $pass, $url, $json);
                $this->cannedResponse = $canned;
            }

            public function req()
            {
                if ($this->cannedResponse === false) {
                    $this->error[] = 'Stubbed error';
                    return false;
                }
                $this->response = $this->cannedResponse;
                return $this->cannedResponse;
            }
        };
    }

    // ---------------------------------------------------------------
    //  Constructor tests
    // ---------------------------------------------------------------

    /**
     * Test constructor sets credentials.
     */
    public function testConstructorSetsCredentials(): void
    {
        $noc = new SoftaculousNOC('myuser', 'mypass');

        $this->assertSame('myuser', $this->getPrivateProperty($noc, 'nocname'));
        $this->assertSame('mypass', $this->getPrivateProperty($noc, 'nocpass'));
    }

    /**
     * Test constructor uses default credentials when empty strings passed.
     */
    public function testConstructorUsesDefaultsWhenEmpty(): void
    {
        $noc = new SoftaculousNOC();

        $this->assertSame('username', $this->getPrivateProperty($noc, 'nocname'));
        $this->assertSame('password', $this->getPrivateProperty($noc, 'nocpass'));
    }

    /**
     * Test constructor sets custom URL.
     */
    public function testConstructorSetsCustomUrl(): void
    {
        $noc = new SoftaculousNOC('u', 'p', 'https://custom.example.com/noc');

        $this->assertSame('https://custom.example.com/noc', $noc->softaculous);
    }

    /**
     * Test constructor sets default URL when not provided.
     */
    public function testConstructorSetsDefaultUrl(): void
    {
        $noc = new SoftaculousNOC();

        $this->assertSame('https://www.softaculous.com/noc', $noc->softaculous);
    }

    /**
     * Test constructor enables JSON mode.
     */
    public function testConstructorEnablesJsonMode(): void
    {
        $noc = new SoftaculousNOC('u', 'p', '', 1);

        $this->assertSame(1, $noc->json);
    }

    /**
     * Test constructor defaults to non-JSON mode.
     */
    public function testConstructorDefaultsToNonJsonMode(): void
    {
        $noc = new SoftaculousNOC();

        $this->assertSame(0, $noc->json);
    }

    // ---------------------------------------------------------------
    //  Class structure / static analysis
    // ---------------------------------------------------------------

    /**
     * Test the class can be instantiated.
     */
    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(SoftaculousNOC::class, $this->noc);
    }

    /**
     * Verify all expected public properties exist and have correct defaults.
     */
    public function testDefaultPublicProperties(): void
    {
        $noc = new SoftaculousNOC();

        $this->assertIsArray($noc->error);
        $this->assertEmpty($noc->error);
        $this->assertIsArray($noc->params);
        $this->assertEmpty($noc->params);
        $this->assertIsArray($noc->response);
        $this->assertEmpty($noc->response);
        $this->assertIsArray($noc->post);
        $this->assertEmpty($noc->post);
        $this->assertNull($noc->raw_response);
        $this->assertSame(0, $noc->json);
    }

    /**
     * Verify all expected public methods exist on the class.
     *
     * @dataProvider publicMethodProvider
     */
    public function testPublicMethodExists(string $methodName): void
    {
        $ref = new ReflectionClass(SoftaculousNOC::class);
        $this->assertTrue(
            $ref->hasMethod($methodName),
            "Method {$methodName} should exist on SoftaculousNOC"
        );
        $this->assertTrue(
            $ref->getMethod($methodName)->isPublic(),
            "Method {$methodName} should be public"
        );
    }

    /**
     * Data provider for all expected public methods.
     *
     * @return array<string, array{string}>
     */
    public function publicMethodProvider(): array
    {
        return [
            'req' => ['req'],
            'APIunserialize' => ['APIunserialize'],
            'buy' => ['buy'],
            'refund' => ['refund'],
            'licenses' => ['licenses'],
            'cancel' => ['cancel'],
            'refund_and_cancel' => ['refund_and_cancel'],
            'editips' => ['editips'],
            'licenselogs' => ['licenselogs'],
            'autorenewals' => ['autorenewals'],
            'addautorenewal' => ['addautorenewal'],
            'removeautorenewal' => ['removeautorenewal'],
            'webuzo_buy' => ['webuzo_buy'],
            'webuzo_refund' => ['webuzo_refund'],
            'webuzo_licenses' => ['webuzo_licenses'],
            'webuzo_cancel' => ['webuzo_cancel'],
            'webuzo_refund_and_cancel' => ['webuzo_refund_and_cancel'],
            'webuzo_editips' => ['webuzo_editips'],
            'webuzo_licenselogs' => ['webuzo_licenselogs'],
            'webuzo_autorenewals' => ['webuzo_autorenewals'],
            'webuzo_addautorenewal' => ['webuzo_addautorenewal'],
            'webuzo_removeautorenewal' => ['webuzo_removeautorenewal'],
            'webuzotrial' => ['webuzotrial'],
            'virt_buy' => ['virt_buy'],
            'virt_refund' => ['virt_refund'],
            'virt_licenses' => ['virt_licenses'],
            'virt_remove' => ['virt_remove'],
            'virt_refund_and_cancel' => ['virt_refund_and_cancel'],
            'virt_editips' => ['virt_editips'],
            'virt_licenselogs' => ['virt_licenselogs'],
            'virt_renewals' => ['virt_renewals'],
            'virt_addautorenewal' => ['virt_addautorenewal'],
            'virt_removeautorenewal' => ['virt_removeautorenewal'],
            'sitemush_buy' => ['sitemush_buy'],
            'sitemush_refund' => ['sitemush_refund'],
            'sitemush_licenses' => ['sitemush_licenses'],
            'sitemush_remove' => ['sitemush_remove'],
            'sitemush_refund_and_cancel' => ['sitemush_refund_and_cancel'],
            'sitemush_editips' => ['sitemush_editips'],
            'sitemush_licenselogs' => ['sitemush_licenselogs'],
            'sitemush_renewals' => ['sitemush_renewals'],
            'sitemush_addautorenewal' => ['sitemush_addautorenewal'],
            'sitemush_removeautorenewal' => ['sitemush_removeautorenewal'],
            'invoicedetails' => ['invoicedetails'],
            'r' => ['r'],
        ];
    }

    // ---------------------------------------------------------------
    //  APIunserialize()
    // ---------------------------------------------------------------

    /**
     * Test APIunserialize decodes JSON when json mode is enabled.
     */
    public function testApiUnserializeDecodesJson(): void
    {
        $noc = new SoftaculousNOC('u', 'p', '', 1);
        $data = ['key' => 'value', 'num' => 42];
        $json = json_encode($data);

        $result = $noc->APIunserialize($json);

        $this->assertSame($data, $result);
    }

    /**
     * Test APIunserialize decodes serialized PHP when json mode is off.
     */
    public function testApiUnserializeDecodesSerialized(): void
    {
        $noc = new SoftaculousNOC('u', 'p', '', 0);
        $data = ['key' => 'value', 'num' => 42];
        $serialized = serialize($data);

        $result = $noc->APIunserialize($serialized);

        $this->assertSame($data, $result);
    }

    // ---------------------------------------------------------------
    //  Parameter wiring: Softaculous methods
    // ---------------------------------------------------------------

    /**
     * Test buy() sets correct parameters.
     */
    public function testBuySetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc(['license' => 'test-key']);
        $noc->buy('1.2.3.4', '1M', 1, 'test@test.com', 1, 0);

        $this->assertSame('softaculous_buy', $noc->params['ca']);
        $this->assertSame(1, $noc->params['purchase']);
        $this->assertSame('1.2.3.4', $noc->params['ips']);
        $this->assertSame('1M', $noc->params['toadd']);
        $this->assertSame(1, $noc->params['servertype']);
        $this->assertSame('test@test.com', $noc->params['authemail']);
        $this->assertSame(1, $noc->params['autorenew']);
        $this->assertSame(0, $noc->params['buy_sitepad']);
    }

    /**
     * Test refund() sets correct parameters.
     */
    public function testRefundSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->refund(100);

        $this->assertSame('softaculous_refund', $noc->params['ca']);
        $this->assertSame(100, $noc->params['actid']);
    }

    /**
     * Test licenses() sets correct parameters with defaults.
     */
    public function testLicensesSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->licenses();

        $this->assertSame('softaculous', $noc->params['ca']);
        $this->assertSame('', $noc->params['lickey']);
        $this->assertSame('', $noc->params['ips']);
        $this->assertSame('', $noc->params['expiry']);
        $this->assertSame(0, $noc->params['start']);
        $this->assertSame(1000000, $noc->params['len']);
        $this->assertSame('', $noc->params['email']);
    }

    /**
     * Test licenses() with IP filter.
     */
    public function testLicensesWithIpFilter(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->licenses('', '10.0.0.1');

        $this->assertSame('10.0.0.1', $noc->params['ips']);
    }

    /**
     * Test cancel() sets correct parameters.
     */
    public function testCancelSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->cancel('my-key', '', 0);

        $this->assertSame('softaculous_cancel', $noc->params['ca']);
        $this->assertSame('my-key', $noc->params['lickey']);
        $this->assertSame(1, $noc->params['cancel_license']);
        $this->assertArrayNotHasKey('force', $noc->params);
    }

    /**
     * Test cancel() with force flag.
     */
    public function testCancelWithForce(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->cancel('my-key', '', 1);

        $this->assertSame(1, $noc->params['force']);
    }

    /**
     * Test editips() sets correct parameters.
     */
    public function testEditipsSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->editips(1000, '5.6.7.8');

        $this->assertSame('softaculous_showlicense', $noc->params['ca']);
        $this->assertSame(1000, $noc->params['lid']);
        $this->assertSame('5.6.7.8', $noc->params['ips[]']);
        $this->assertSame(1, $noc->params['editlicense']);
    }

    /**
     * Test licenselogs() sets correct parameters.
     */
    public function testLicenselogsSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->licenselogs('my-key');

        $this->assertSame('softaculous_licenselogs', $noc->params['ca']);
        $this->assertSame('my-key', $noc->params['key']);
        $this->assertArrayNotHasKey('limit', $noc->params);
    }

    /**
     * Test licenselogs() with limit.
     */
    public function testLicenselogsWithLimit(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->licenselogs('my-key', 10);

        $this->assertSame(10, $noc->params['limit']);
    }

    /**
     * Test autorenewals() sets correct parameters.
     */
    public function testAutorenewalsSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->autorenewals();

        $this->assertSame('softaculous_renewals', $noc->params['ca']);
    }

    /**
     * Test addautorenewal() sets correct parameters.
     */
    public function testAddautorenewalSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->addautorenewal('test-key');

        $this->assertSame('softaculous_renewals', $noc->params['ca']);
        $this->assertSame(1, $noc->params['addrenewal']);
        $this->assertSame('test-key', $noc->params['lickey']);
    }

    /**
     * Test removeautorenewal() sets correct parameters.
     */
    public function testRemoveautorenewalSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->removeautorenewal('test-key');

        $this->assertSame('softaculous_renewals', $noc->params['ca']);
        $this->assertSame(1, $noc->params['cancelrenewal']);
        $this->assertSame('test-key', $noc->params['lickey']);
    }

    // ---------------------------------------------------------------
    //  Parameter wiring: Webuzo methods
    // ---------------------------------------------------------------

    /**
     * Test webuzo_buy() sets correct parameters.
     */
    public function testWebuzoBuySetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->webuzo_buy('1.2.3.4', '1M', 1, 'test@test.com', 1);

        $this->assertSame('webuzo_buy', $noc->params['ca']);
        $this->assertSame('1.2.3.4', $noc->params['ips']);
    }

    /**
     * Test webuzo_refund() sets correct parameters.
     */
    public function testWebuzoRefundSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->webuzo_refund(200);

        $this->assertSame('webuzo_refund', $noc->params['ca']);
        $this->assertSame(200, $noc->params['actid']);
    }

    /**
     * Test webuzo_licenses() sets correct parameters.
     */
    public function testWebuzoLicensesSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->webuzo_licenses();

        $this->assertSame('webuzo', $noc->params['ca']);
    }

    /**
     * Test webuzo_cancel() sets correct parameters.
     */
    public function testWebuzoCancelSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->webuzo_cancel('wkey');

        $this->assertSame('webuzo_cancel', $noc->params['ca']);
        $this->assertSame('wkey', $noc->params['lickey']);
    }

    /**
     * Test webuzo_editips() sets correct parameters.
     */
    public function testWebuzoEditipsSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->webuzo_editips(500, '10.0.0.1');

        $this->assertSame('webuzo_showlicense', $noc->params['ca']);
        $this->assertSame(500, $noc->params['lid']);
        $this->assertSame('10.0.0.1', $noc->params['ips']);
    }

    /**
     * Test webuzo_licenselogs() sets correct parameters.
     */
    public function testWebuzoLicenselogsSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->webuzo_licenselogs('wkey');

        $this->assertSame('webuzo_licenselogs', $noc->params['ca']);
        $this->assertSame('wkey', $noc->params['key']);
    }

    /**
     * Test webuzo_autorenewals() sets correct parameters.
     */
    public function testWebuzoAutorenewalsSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->webuzo_autorenewals();

        $this->assertSame('webuzo_renewals', $noc->params['ca']);
    }

    /**
     * Test webuzo_addautorenewal() sets correct parameters.
     */
    public function testWebuzoAddautorenewalSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->webuzo_addautorenewal('wkey');

        $this->assertSame('webuzo_renewals', $noc->params['ca']);
        $this->assertSame(1, $noc->params['addrenewal']);
    }

    /**
     * Test webuzo_removeautorenewal() sets correct parameters.
     */
    public function testWebuzoRemoveautorenewalSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->webuzo_removeautorenewal('wkey');

        $this->assertSame('webuzo_renewals', $noc->params['ca']);
        $this->assertSame(1, $noc->params['cancelrenewal']);
    }

    /**
     * Test webuzotrial() sets correct parameters.
     */
    public function testWebuzotrialSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->webuzotrial('10.0.0.1', 1);

        $this->assertSame('webuzotrial', $noc->params['ca']);
        $this->assertSame('10.0.0.1', $noc->params['ips']);
        $this->assertSame(1, $noc->params['type']);
        $this->assertSame(1, $noc->params['gettrial']);
    }

    // ---------------------------------------------------------------
    //  Parameter wiring: Virtualizor methods
    // ---------------------------------------------------------------

    /**
     * Test virt_buy() sets correct parameters.
     */
    public function testVirtBuySetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->virt_buy('1.2.3.4', '1M', 1);

        $this->assertSame('virtualizor_buy', $noc->params['ca']);
        $this->assertSame('1.2.3.4', $noc->params['ips']);
        $this->assertSame('1M', $noc->params['toadd']);
        $this->assertSame(1, $noc->params['autorenew']);
    }

    /**
     * Test virt_refund() sets correct parameters.
     */
    public function testVirtRefundSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->virt_refund(300);

        $this->assertSame('virtualizor_refund', $noc->params['ca']);
        $this->assertSame(300, $noc->params['actid']);
    }

    /**
     * Test virt_licenses() sets correct parameters.
     */
    public function testVirtLicensesSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->virt_licenses();

        $this->assertSame('virtualizor', $noc->params['ca']);
    }

    /**
     * Test virt_remove() sets correct parameters.
     */
    public function testVirtRemoveSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->virt_remove('vkey');

        $this->assertSame('virtualizor_cancel', $noc->params['ca']);
        $this->assertSame('vkey', $noc->params['lickey']);
    }

    /**
     * Test virt_editips() sets correct parameters.
     */
    public function testVirtEditipsSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->virt_editips(700, '10.0.0.1');

        $this->assertSame('virtualizor_showlicense', $noc->params['ca']);
        $this->assertSame(700, $noc->params['lid']);
    }

    /**
     * Test virt_licenselogs() sets correct parameters.
     */
    public function testVirtLicenselogsSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->virt_licenselogs('vkey');

        $this->assertSame('virtualizor_licenselogs', $noc->params['ca']);
    }

    /**
     * Test virt_renewals() sets correct parameters.
     */
    public function testVirtRenewalsSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->virt_renewals();

        $this->assertSame('virtualizor_renewals', $noc->params['ca']);
    }

    /**
     * Test virt_addautorenewal() sets correct parameters.
     */
    public function testVirtAddautorenewalSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->virt_addautorenewal('vkey');

        $this->assertSame('virtualizor_renewals', $noc->params['ca']);
        $this->assertSame(1, $noc->params['addrenewal']);
    }

    /**
     * Test virt_removeautorenewal() sets correct parameters.
     */
    public function testVirtRemoveautorenewalSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->virt_removeautorenewal('vkey');

        $this->assertSame('virtualizor_renewals', $noc->params['ca']);
        $this->assertSame(1, $noc->params['cancelrenewal']);
    }

    // ---------------------------------------------------------------
    //  Parameter wiring: SiteMush methods
    // ---------------------------------------------------------------

    /**
     * Test sitemush_buy() sets correct parameters.
     */
    public function testSitemushBuySetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->sitemush_buy('1.2.3.4', '1M', 1);

        $this->assertSame('sitemush_buy', $noc->params['ca']);
        $this->assertSame('1.2.3.4', $noc->params['ips']);
    }

    /**
     * Test sitemush_refund() sets correct parameters.
     */
    public function testSitemushRefundSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->sitemush_refund(400);

        $this->assertSame('sitemush_refund', $noc->params['ca']);
        $this->assertSame(400, $noc->params['actid']);
    }

    /**
     * Test sitemush_licenses() sets correct parameters.
     */
    public function testSitemushLicensesSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->sitemush_licenses();

        $this->assertSame('sitemush', $noc->params['ca']);
    }

    /**
     * Test sitemush_remove() sets correct parameters.
     */
    public function testSitemushRemoveSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->sitemush_remove('smkey');

        $this->assertSame('sitemush_cancel', $noc->params['ca']);
        $this->assertSame('smkey', $noc->params['lickey']);
    }

    /**
     * Test sitemush_editips() sets correct parameters.
     */
    public function testSitemushEditipsSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->sitemush_editips(800, '10.0.0.1');

        $this->assertSame('sitemush_showlicense', $noc->params['ca']);
        $this->assertSame(800, $noc->params['lid']);
    }

    /**
     * Test sitemush_licenselogs() sets correct parameters.
     */
    public function testSitemushLicenselogsSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->sitemush_licenselogs('smkey');

        $this->assertSame('sitemush_licenselogs', $noc->params['ca']);
    }

    /**
     * Test sitemush_renewals() sets correct parameters.
     */
    public function testSitemushRenewalsSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->sitemush_renewals();

        $this->assertSame('sitemush_renewals', $noc->params['ca']);
    }

    /**
     * Test sitemush_addautorenewal() sets correct parameters.
     */
    public function testSitemushAddautorenewalSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->sitemush_addautorenewal('smkey');

        $this->assertSame('sitemush_renewals', $noc->params['ca']);
        $this->assertSame(1, $noc->params['addrenewal']);
    }

    /**
     * Test sitemush_removeautorenewal() sets correct parameters.
     */
    public function testSitemushRemoveautorenewalSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->sitemush_removeautorenewal('smkey');

        $this->assertSame('sitemush_renewals', $noc->params['ca']);
        $this->assertSame(1, $noc->params['cancelrenewal']);
    }

    // ---------------------------------------------------------------
    //  Invoice and misc
    // ---------------------------------------------------------------

    /**
     * Test invoicedetails() sets correct parameters.
     */
    public function testInvoicedetailsSetsCorrectParams(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->invoicedetails(100);

        $this->assertSame('invoicedetails', $noc->params['ca']);
        $this->assertSame(100, $noc->params['invoid']);
    }

    /**
     * Test invoicedetails() defaults to zero.
     */
    public function testInvoicedetailsDefaultsToZero(): void
    {
        $noc = $this->createStubbedNoc([]);
        $noc->invoicedetails();

        $this->assertSame(0, $noc->params['invoid']);
    }

    // ---------------------------------------------------------------
    //  refund_and_cancel() logic tests
    // ---------------------------------------------------------------

    /**
     * Test refund_and_cancel() returns false with error when no key or IP provided.
     */
    public function testRefundAndCancelReturnsErrorWithNoKeyOrIp(): void
    {
        $noc = $this->createStubbedNoc([]);
        $result = $noc->refund_and_cancel('', '');

        $this->assertFalse($result);
        $this->assertContains('Please provide a License Key or a Valid IP.', $noc->error);
    }

    /**
     * Test webuzo_refund_and_cancel() returns false when no key or IP.
     */
    public function testWebuzoRefundAndCancelReturnsErrorWithNoKeyOrIp(): void
    {
        $noc = $this->createStubbedNoc([]);
        $result = $noc->webuzo_refund_and_cancel('', '');

        $this->assertFalse($result);
        $this->assertContains('Please provide a License Key or a Valid IP.', $noc->error);
    }

    /**
     * Test virt_refund_and_cancel() returns false when no key or IP.
     */
    public function testVirtRefundAndCancelReturnsErrorWithNoKeyOrIp(): void
    {
        $noc = $this->createStubbedNoc([]);
        $result = $noc->virt_refund_and_cancel('', '');

        $this->assertFalse($result);
        $this->assertContains('Please provide a License Key or a Valid IP.', $noc->error);
    }

    /**
     * Test sitemush_refund_and_cancel() returns false when no key or IP.
     */
    public function testSitemushRefundAndCancelReturnsErrorWithNoKeyOrIp(): void
    {
        $noc = $this->createStubbedNoc([]);
        $result = $noc->sitemush_refund_and_cancel('', '');

        $this->assertFalse($result);
        $this->assertContains('Please provide a License Key or a Valid IP.', $noc->error);
    }

    // ---------------------------------------------------------------
    //  Method signature tests
    // ---------------------------------------------------------------

    /**
     * Test buy() method signature has correct parameter count.
     */
    public function testBuyMethodSignature(): void
    {
        $ref = new \ReflectionMethod(SoftaculousNOC::class, 'buy');

        $this->assertSame(6, $ref->getNumberOfParameters());
        $this->assertSame(5, $ref->getNumberOfRequiredParameters());
    }

    /**
     * Test licenses() method signature has all optional parameters.
     */
    public function testLicensesMethodSignature(): void
    {
        $ref = new \ReflectionMethod(SoftaculousNOC::class, 'licenses');

        $this->assertSame(6, $ref->getNumberOfParameters());
        $this->assertSame(0, $ref->getNumberOfRequiredParameters());
    }

    /**
     * Test cancel() method signature.
     */
    public function testCancelMethodSignature(): void
    {
        $ref = new \ReflectionMethod(SoftaculousNOC::class, 'cancel');

        $this->assertSame(3, $ref->getNumberOfParameters());
        $this->assertSame(0, $ref->getNumberOfRequiredParameters());
    }
}

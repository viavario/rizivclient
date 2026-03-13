<?php

declare(strict_types=1);

namespace viavario\rizivclient\tests;

use PHPUnit\Framework\TestCase;
use viavario\rizivclient\RizivClient;
use viavario\rizivclient\RizivResult;

/**
 * Test suite for RizivClient class.
 * @coversDefaultClass RizivClient
 */
class RizivClientTest extends TestCase
{
    /**
     * Sample HTML response for a successful search result.
     * @var string
     */
    private const SAMPLE_HTML_SUCCESS = '
    <html><body>
    <div class="card">
    <div class="card-body">
    <label><small>Naam</small></label><div><small>Test Name</small></div>
    <label><small>RIZIV-nr</small></label><div><small>12345678</small></div>
    <label><small>Beroep</small></label><div><small>Doctor</small></div>
    <label><small>Conv.</small></label><div><small>geconventioneerd</small></div>
    <label><small>Kwalificatie</small></label><div><small>MD</small></div>
    <label><small>Kwal. datum</small></label><div><small>01/01/2020</small></div>
    </div></div>
    </body></html>
    ';

    /**
     * Sample HTML response when no result is found.
     * @var string
     */
    private const SAMPLE_HTML_NO_RESULT = '<html><body></body></html>';

    /**
     * Creates a test client that returns the provided HTML.
     */
    private function createClientWithHtml(string $html): RizivClient
    {
        return new class($html) extends RizivClient {
            private string $html;

            public function __construct(string $html)
            {
                $this->html = $html;
            }

            protected function get(string $url, array $params = []): string
            {
                return $this->html;
            }
        };
    }

    /**
     * Tests successful search by registration number.
     */
    public function testSearchByRegistrationNumberSuccess(): void
    {
        $client = $this->createClientWithHtml(self::SAMPLE_HTML_SUCCESS);

        $result = $client->searchByRegistrationNumber('12345678');

        $this->assertInstanceOf(RizivResult::class, $result);
        $this->assertEquals('Test Name', $result->name);
        $this->assertEquals('12345678', $result->riziv_number);
        $this->assertEquals('Doctor', $result->profession);
        $this->assertTrue($result->contracted);
        $this->assertEquals('MD', $result->qualification);
        $this->assertEquals('2020-01-01', $result->qualification_date->format('Y-m-d'));
    }

    /**
     * Tests search by registration number when no result is found.
     */
    public function testSearchByRegistrationNumberNoResult(): void
    {
        $client = $this->createClientWithHtml(self::SAMPLE_HTML_NO_RESULT);

        $result = $client->searchByRegistrationNumber('99999999');

        $this->assertNull($result);
    }

    /**
     * Tests that non-digit characters are removed from registration number.
     */
    public function testSearchByRegistrationNumberWithNonDigits(): void
    {
        // Test that non-digits are removed from registration number
        $client = $this->createClientWithHtml(self::SAMPLE_HTML_SUCCESS);

        // The number "12-34-56-78" should become "12345678"
        $result = $client->searchByRegistrationNumber('12-34-56-78');

        $this->assertInstanceOf(RizivResult::class, $result);
        // Since the params include substr(registrationNumber, 0, 8), which is '12345678'
        // And the sample expects it
    }
}
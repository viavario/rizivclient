<?php

declare(strict_types=1);

namespace viavario\rizivclient;

/**
 * Client for searching healthcare professionals on webappsa.riziv-inami.fgov.be
 * by RIZIV registration number.
 */
class RizivClient
{
    private const BASE_URL    = 'https://webappsa.riziv-inami.fgov.be';
    private const SEARCH_PATH = '/silverpages/Home/SearchHcw/';
    private const TIMEOUT     = 15;

    /**
     * Search by RIZIV number.
     *
     * @param  string $registrationNumber  E.g. "731106598"
     * @return RizivResult|null
     */
    public function searchByRegistrationNumber(string $registrationNumber): ?RizivResult
    {
        $registrationNumber = preg_replace('/\D/', '', $registrationNumber); // Remove non-digit characters
        return $this->search([
            'Form.Name'              => '',
            'Form.FirstName'         => '',
            'Form.Profession'        => '',
            'Form.Specialisation'    => '',
            'Form.ConventionState'   => '',
            'Form.Location'          => '',
            'Form.NihdiNumber'       => substr($registrationNumber, 0, 8),
            'Form.Qualification'     => '',
            'Form.Attribute'         => '',
            'Form.AttributeValue'    => '',
            'Form.NorthEastLat'      => '',
            'Form.NorthEastLng'      => '',
            'Form.SouthWestLat'      => '',
            'Form.SouthWestLng'      => '',
            'Form.LocationLng'       => '',
            'Form.LocationLat'       => '',
        ]);
    }

    /**
     * Perform the POST request and parse results.
     *
     * @param  array<string, string> $params
     * @return RizivResult|null
     *
     * @throws RuntimeException on request failure
     */
    protected function search(array $params): ?RizivResult
    {
        $html = $this->get(self::BASE_URL . self::SEARCH_PATH, $params);

        return $this->parseHtml($html);
    }

    /**
     * Execute a GET request and return the response body.
     *
     * @param  string $url
     * @param  array<string, string> $params
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function get(string $url, array $params = []): string
    {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; RizivClient/1.0)',
            CURLOPT_COOKIE         => '.nihdi.language=c%3Dnl-BE%7Cuic%3Dnl-BE',
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException("cURL request failed: {$error}");
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new \RuntimeException("Unexpected HTTP status code: {$httpCode}");
        }

        return (string) $response;
    }

    /**
     * Parse the HTML response and extract healthcare professional results.
     *
     * @param  string $html
     * @return RizivResult|null
     */
    protected function parseHtml(string $html): ?RizivResult
    {
        $dom = new \DOMDocument();

        // Suppress warnings from malformed HTML and load with UTF-8 encoding hint
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // Query for the first card
        $card = $xpath->query('//div[contains(@class, "card")]');
        if ($card === false || $card->length === 0) {
            return null;
        }

        // Extract name: text from <small> in div following label with "Naam"
        $nameNode = $xpath->query('.//label/small[text()="Naam"]/parent::*/following-sibling::div[1]/small', $card->item(0));
        $name = $nameNode && $nameNode->length > 0 ? trim($nameNode->item(0)->textContent) : '';

        // Query for the card body containing the rows
        $cardBody = $xpath->query('.//div[contains(@class, "card-body")]', $card->item(0));
        if ($cardBody === false || $cardBody->length === 0) {
            return null;
        }

        $body = $cardBody->item(0);

        // Extract riziv_number: text from <small> in div following label with "RIZIV-nr"
        $rizivNode = $xpath->query('.//label/small[text()="RIZIV-nr"]/parent::*/following-sibling::div[1]/small', $body);
        $riziv_number = $rizivNode && $rizivNode->length > 0 ? trim($rizivNode->item(0)->textContent) : '';

        // Extract profession: text from <small> in div following label with "Beroep"
        $professionNode = $xpath->query('.//label/small[text()="Beroep"]/parent::*/following-sibling::div[1]/small', $body);
        $profession = $professionNode && $professionNode->length > 0 ? trim($professionNode->item(0)->textContent) : '';

        // Extract contracted: text from <small> in div following label with "Conv."
        $contractedNode = $xpath->query('.//label/small[text()="Conv."]/parent::*/following-sibling::div[1]/small', $body);
        $contractedText = $contractedNode && $contractedNode->length > 0 ? trim($contractedNode->item(0)->textContent) : '';
        $contracted = strtolower($contractedText) === "geconventioneerd";

        // Extract qualification: text from <small> in div following label with "Kwalificatie"
        $qualificationNode = $xpath->query('.//label/small[text()="Kwalificatie"]/parent::*/following-sibling::div[1]/small', $body);
        $qualification = $qualificationNode && $qualificationNode->length > 0 ? trim($qualificationNode->item(0)->textContent) : '';

        // Extract qualification_date: text from <small> in div following label with "Kwal. datum"
        $dateNode = $xpath->query('.//label/small[text()="Kwal. datum"]/parent::*/following-sibling::div[1]/small', $body);
        $dateText = $dateNode && $dateNode->length > 0 ? trim($dateNode->item(0)->textContent) : '';
        $qualification_date = !empty($dateText) ? \DateTime::createFromFormat("d/m/Y", $dateText) : null;
        if (!$qualification_date instanceof \DateTime) {
            $qualification_date = new \DateTime(); // fallback to current date if parsing fails
        }

        // Return RizivResult with all required fields
        return new RizivResult(
            $name,
            $riziv_number,
            $profession,
            $contracted,
            $qualification,
            $qualification_date
        );
    }
}

<?php

namespace Core;

use Config;
use RuntimeException;

class AirtableClient
{
    private string $token;
    private string $baseId;

    public function __construct()
    {
        $this->token = (string) Config\env('AIRTABLE_TOKEN', '');
        $this->baseId = (string) Config\env('AIRTABLE_BASE_ID', '');

        if ($this->token === '' || $this->baseId === '') {
            throw new RuntimeException('Airtable is not configured. Set AIRTABLE_TOKEN and AIRTABLE_BASE_ID.');
        }
    }

    public function fetchRecords(string $tableName, ?string $view = null): array
    {
        if ($tableName === '') {
            throw new RuntimeException('AIRTABLE_ACTIONS_TABLE is required.');
        }

        $records = [];
        $offset = null;

        do {
            $url = 'https://api.airtable.com/v0/' . rawurlencode($this->baseId) . '/' . rawurlencode($tableName);
            $params = [];
            if ($view !== null && $view !== '') {
                $params['view'] = $view;
            }
            if ($offset !== null) {
                $params['offset'] = $offset;
            }
            if ($params !== []) {
                $url .= '?' . http_build_query($params);
            }

            $payload = $this->request($url);
            foreach (($payload['records'] ?? []) as $record) {
                $records[] = $record;
            }
            $offset = $payload['offset'] ?? null;
        } while ($offset !== null);

        return $records;
    }

    private function request(string $url): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('Unable to initialize Airtable request.');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('Airtable request failed: ' . $error);
        }

        $statusCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid Airtable response.');
        }

        if ($statusCode >= 400) {
            $message = $decoded['error']['message'] ?? ('HTTP ' . $statusCode);
            throw new RuntimeException('Airtable error: ' . $message);
        }

        return $decoded;
    }
}

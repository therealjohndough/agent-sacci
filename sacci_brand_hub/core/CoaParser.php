<?php

namespace Core;

/**
 * Sends a COA PDF to the Claude API and extracts lab data as structured JSON.
 *
 * Requires ANTHROPIC_API_KEY in .env.
 */
class CoaParser
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const MODEL   = 'claude-opus-4-6';

    /**
     * Parse a COA PDF file and return extracted lab fields.
     *
     * @param  string $pdfPath Absolute path to the PDF file.
     * @return array{
     *   batch_number: string|null,
     *   thc_percent: float|null,
     *   cbd_percent: float|null,
     *   total_terpenes_percent: float|null,
     *   terp_1_name: string|null,
     *   terp_1_pct: float|null,
     *   terp_2_name: string|null,
     *   terp_2_pct: float|null,
     *   terp_3_name: string|null,
     *   terp_3_pct: float|null,
     *   lab_name: string|null,
     *   raw: string
     * }
     * @throws \RuntimeException on API or parse failure.
     */
    public static function parse(string $pdfPath): array
    {
        $apiKey = \Config\env('ANTHROPIC_API_KEY');
        if (!$apiKey) {
            throw new \RuntimeException('ANTHROPIC_API_KEY is not set in .env');
        }

        $pdfData = base64_encode((string) file_get_contents($pdfPath));

        $prompt = <<<'PROMPT'
Extract the following fields from this Certificate of Analysis (COA) and return ONLY valid JSON with these exact keys:
- batch_number: the batch or lot ID (usually top-right of document, e.g. "SW042825-F3-41G")
- thc_percent: Total THC as a number without the % sign (e.g. 28.4), or null if not found
- cbd_percent: Total CBD as a number without the % sign, or null if not found
- total_terpenes_percent: Total terpenes as a number without the % sign, or null if not found
- terp_1_name: name of the highest-testing terpene, or null
- terp_1_pct: percentage of terp_1 as a number, or null
- terp_2_name: name of the second-highest terpene, or null
- terp_2_pct: percentage of terp_2 as a number, or null
- terp_3_name: name of the third-highest terpene, or null
- terp_3_pct: percentage of terp_3 as a number, or null
- lab_name: name of the testing laboratory, or null

Return ONLY the JSON object. No explanation, no markdown, no code fences.
PROMPT;

        $payload = json_encode([
            'model'      => self::MODEL,
            'max_tokens' => 512,
            'messages'   => [
                [
                    'role'    => 'user',
                    'content' => [
                        [
                            'type'   => 'document',
                            'source' => [
                                'type'       => 'base64',
                                'media_type' => 'application/pdf',
                                'data'       => $pdfData,
                            ],
                        ],
                        [
                            'type' => 'text',
                            'text' => $prompt,
                        ],
                    ],
                ],
            ],
        ]);

        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_TIMEOUT        => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            throw new \RuntimeException('Claude API call failed (HTTP ' . $httpCode . '): ' . (string) $response);
        }

        $body = json_decode((string) $response, true);
        $raw  = $body['content'][0]['text'] ?? '';

        $data = json_decode(trim($raw), true);
        if (!is_array($data)) {
            throw new \RuntimeException('Claude returned non-JSON response: ' . $raw);
        }

        return [
            'batch_number'           => isset($data['batch_number']) ? (string) $data['batch_number'] : null,
            'thc_percent'            => isset($data['thc_percent'])  ? (float) $data['thc_percent']   : null,
            'cbd_percent'            => isset($data['cbd_percent'])  ? (float) $data['cbd_percent']   : null,
            'total_terpenes_percent' => isset($data['total_terpenes_percent']) ? (float) $data['total_terpenes_percent'] : null,
            'terp_1_name'            => isset($data['terp_1_name']) ? (string) $data['terp_1_name'] : null,
            'terp_1_pct'             => isset($data['terp_1_pct'])  ? (float) $data['terp_1_pct']  : null,
            'terp_2_name'            => isset($data['terp_2_name']) ? (string) $data['terp_2_name'] : null,
            'terp_2_pct'             => isset($data['terp_2_pct'])  ? (float) $data['terp_2_pct']  : null,
            'terp_3_name'            => isset($data['terp_3_name']) ? (string) $data['terp_3_name'] : null,
            'terp_3_pct'             => isset($data['terp_3_pct'])  ? (float) $data['terp_3_pct']  : null,
            'lab_name'               => isset($data['lab_name']) ? (string) $data['lab_name'] : null,
            'raw'                    => $raw,
        ];
    }
}

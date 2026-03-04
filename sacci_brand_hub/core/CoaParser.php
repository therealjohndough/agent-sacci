<?php

namespace Core;

use Smalot\PdfParser\Parser;

/**
 * Parses a COA PDF by extracting its text content, then sending it to
 * a language model via OpenRouter for structured data extraction.
 *
 * This approach works on any shared hosting with no extra server tools.
 * It requires digital (not scanned) PDFs — all modern lab COAs qualify.
 *
 * Requires OPENROUTER_API_KEY in .env.
 * Optionally set OLLAMA_URL to use a local Ollama instance instead.
 */
class CoaParser
{
    private const OPENROUTER_URL   = 'https://openrouter.ai/api/v1/chat/completions';
    private const OPENROUTER_MODEL = 'qwen/qwen2.5-72b-instruct:free';

    private const PROMPT_TEMPLATE = <<<'PROMPT'
Below is the raw text extracted from a cannabis Certificate of Analysis (COA).

Extract the following fields and return ONLY a valid JSON object — no explanation, no markdown, no code fences:

{
  "batch_number": "the batch or lot ID, e.g. SW042825-F3",
  "thc_percent": 28.4,
  "cbd_percent": 0.1,
  "cbg_percent": 0.5,
  "cbn_percent": null,
  "terp_total_percent": 2.1,
  "terp_1_name": "Myrcene",
  "terp_1_pct": 0.82,
  "terp_2_name": "Limonene",
  "terp_2_pct": 0.45,
  "terp_3_name": "Caryophyllene",
  "terp_3_pct": 0.31,
  "lab_name": "Kaycha Labs"
}

Rules:
- All percentage values must be plain numbers (floats), not strings. No % symbol.
- Use null for any field not present.
- Terpenes ranked by percentage descending.
- THC should be Total THC (not just delta-9).

COA TEXT:
---
%s
---
PROMPT;

    /**
     * Parse a COA PDF and return extracted lab fields.
     *
     * @param  string $pdfPath Absolute path to the PDF.
     * @return array{
     *   batch_number: string|null,
     *   thc_percent: float|null,
     *   cbd_percent: float|null,
     *   cbg_percent: float|null,
     *   cbn_percent: float|null,
     *   terp_total_percent: float|null,
     *   terp_1_name: string|null,
     *   terp_1_pct: float|null,
     *   terp_2_name: string|null,
     *   terp_2_pct: float|null,
     *   terp_3_name: string|null,
     *   terp_3_pct: float|null,
     *   lab_name: string|null,
     *   raw: string
     * }
     * @throws \RuntimeException on extraction or API failure.
     */
    public static function parse(string $pdfPath): array
    {
        $text = self::extractText($pdfPath);
        $prompt = sprintf(self::PROMPT_TEMPLATE, $text);

        $ollamaUrl = \Config\env('OLLAMA_URL');
        $raw = $ollamaUrl
            ? self::callOllama($ollamaUrl, $prompt)
            : self::callOpenRouter($prompt);

        return self::parseResponse($raw);
    }

    // -------------------------------------------------------------------------
    // PDF text extraction
    // -------------------------------------------------------------------------

    private static function extractText(string $pdfPath): string
    {
        if (!file_exists($pdfPath)) {
            throw new \RuntimeException("PDF not found: $pdfPath");
        }

        $parser = new Parser();
        $pdf    = $parser->parseFile($pdfPath);
        $text   = $pdf->getText();

        if (trim($text) === '') {
            throw new \RuntimeException(
                'No text could be extracted from the PDF. ' .
                'This may be a scanned (image-only) document.'
            );
        }

        // Trim to ~12,000 chars to stay within token limits
        return mb_substr($text, 0, 12000);
    }

    // -------------------------------------------------------------------------
    // API calls
    // -------------------------------------------------------------------------

    private static function callOpenRouter(string $prompt): string
    {
        $apiKey = \Config\env('OPENROUTER_API_KEY');
        if (!$apiKey) {
            throw new \RuntimeException('OPENROUTER_API_KEY is not set in .env');
        }

        $payload = json_encode([
            'model'      => self::OPENROUTER_MODEL,
            'max_tokens' => 512,
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        $ch = curl_init(self::OPENROUTER_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
                'HTTP-Referer: ' . (\Config\env('APP_URL') ?: 'https://sacci.space'),
                'X-Title: Sacci Brand Hub',
            ],
            CURLOPT_TIMEOUT        => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            throw new \RuntimeException('OpenRouter API error (HTTP ' . $httpCode . '): ' . (string) $response);
        }

        $body = json_decode((string) $response, true);
        return $body['choices'][0]['message']['content'] ?? '';
    }

    private static function callOllama(string $baseUrl, string $prompt): string
    {
        $model = \Config\env('OLLAMA_MODEL') ?: 'qwen2.5:7b';
        $url   = rtrim($baseUrl, '/') . '/api/chat';

        $payload = json_encode([
            'model'    => $model,
            'stream'   => false,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 120,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            throw new \RuntimeException('Ollama error (HTTP ' . $httpCode . '): ' . (string) $response);
        }

        $body = json_decode((string) $response, true);
        return $body['message']['content'] ?? '';
    }

    // -------------------------------------------------------------------------
    // Response parsing
    // -------------------------------------------------------------------------

    private static function parseResponse(string $raw): array
    {
        // Strip markdown code fences if present
        $json = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
        $json = preg_replace('/\s*```$/', '', trim((string) $json));

        $data = json_decode(trim((string) $json), true);
        if (!is_array($data)) {
            throw new \RuntimeException('Model returned non-JSON: ' . $raw);
        }

        return [
            'batch_number'       => isset($data['batch_number'])       ? (string) $data['batch_number']       : null,
            'thc_percent'        => isset($data['thc_percent'])        ? (float)  $data['thc_percent']        : null,
            'cbd_percent'        => isset($data['cbd_percent'])        ? (float)  $data['cbd_percent']        : null,
            'cbg_percent'        => isset($data['cbg_percent'])        ? (float)  $data['cbg_percent']        : null,
            'cbn_percent'        => isset($data['cbn_percent'])        ? (float)  $data['cbn_percent']        : null,
            'terp_total_percent' => isset($data['terp_total_percent']) ? (float)  $data['terp_total_percent'] : null,
            'terp_1_name'        => isset($data['terp_1_name'])        ? (string) $data['terp_1_name']        : null,
            'terp_1_pct'         => isset($data['terp_1_pct'])         ? (float)  $data['terp_1_pct']         : null,
            'terp_2_name'        => isset($data['terp_2_name'])        ? (string) $data['terp_2_name']        : null,
            'terp_2_pct'         => isset($data['terp_2_pct'])         ? (float)  $data['terp_2_pct']         : null,
            'terp_3_name'        => isset($data['terp_3_name'])        ? (string) $data['terp_3_name']        : null,
            'terp_3_pct'         => isset($data['terp_3_pct'])         ? (float)  $data['terp_3_pct']         : null,
            'lab_name'           => isset($data['lab_name'])           ? (string) $data['lab_name']           : null,
            'raw'                => $raw,
        ];
    }
}

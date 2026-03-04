<?php

namespace Core;

/**
 * Parses a COA PDF using Qwen2.5-VL via OpenRouter's hosted API.
 *
 * Flow:
 *   1. Convert page 1 of the PDF to PNG (Imagick → pdftoppm → convert fallback)
 *   2. Base64-encode the PNG and send to OpenRouter chat completions
 *   3. Parse the JSON response into structured lab fields
 *
 * Requires OPENROUTER_API_KEY in .env.
 * Set OLLAMA_URL in .env to use a local Ollama instance instead (optional).
 *
 * To install PDF-to-image support on the server:
 *   sudo apt install poppler-utils       # provides pdftoppm (recommended)
 *   sudo apt install imagemagick         # provides convert (fallback)
 *   sudo apt install php-imagick         # PHP extension (fastest)
 */
class CoaParser
{
    private const OPENROUTER_URL = 'https://openrouter.ai/api/v1/chat/completions';
    private const OPENROUTER_MODEL = 'qwen/qwen2.5-vl-7b-instruct:free';

    private const PROMPT = <<<'PROMPT'
This is a cannabis Certificate of Analysis (COA). Extract the following fields and return ONLY a valid JSON object with these exact keys — no explanation, no markdown, no code fences:

{
  "batch_number": "the batch or lot ID from the top of the document, e.g. SW042825-F3",
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
- All percentage values must be numbers (floats), not strings. Omit the % symbol.
- Use null for any field not present in the document.
- Terpenes should be ranked by percentage descending.
- THC should be "Total THC", not delta-9 THC alone.
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
     * @throws \RuntimeException on conversion or API failure.
     */
    public static function parse(string $pdfPath): array
    {
        $pngData = self::pdfToBase64Png($pdfPath);

        // If OLLAMA_URL is set, use local Ollama instead of OpenRouter
        $ollamaUrl = \Config\env('OLLAMA_URL');
        if ($ollamaUrl) {
            $raw = self::callOllama($ollamaUrl, $pngData);
        } else {
            $raw = self::callOpenRouter($pngData);
        }

        return self::parseResponse($raw);
    }

    // -------------------------------------------------------------------------
    // PDF → PNG conversion
    // -------------------------------------------------------------------------

    /**
     * Convert the first page of a PDF to a base64-encoded PNG.
     * Tries Imagick, then pdftoppm, then ImageMagick convert CLI.
     */
    private static function pdfToBase64Png(string $pdfPath): string
    {
        if (!file_exists($pdfPath)) {
            throw new \RuntimeException("PDF not found: $pdfPath");
        }

        // 1. Imagick (fastest, no shell exec)
        if (extension_loaded('imagick')) {
            return self::convertViaImagick($pdfPath);
        }

        // 2. pdftoppm (poppler-utils) — sudo apt install poppler-utils
        $pdftoppm = trim((string) shell_exec('which pdftoppm 2>/dev/null'));
        if ($pdftoppm !== '') {
            return self::convertViaPdftoppm($pdfPath, $pdftoppm);
        }

        // 3. ImageMagick convert CLI — sudo apt install imagemagick
        $convert = trim((string) shell_exec('which convert 2>/dev/null'));
        if ($convert !== '') {
            return self::convertViaConvert($pdfPath, $convert);
        }

        // 4. GhostScript — sudo apt install ghostscript
        $gs = trim((string) shell_exec('which gs 2>/dev/null'));
        if ($gs !== '') {
            return self::convertViaGhostScript($pdfPath, $gs);
        }

        throw new \RuntimeException(
            'No PDF-to-image tool found. Install one: ' .
            'sudo apt install poppler-utils  OR  sudo apt install php-imagick'
        );
    }

    private static function convertViaImagick(string $pdfPath): string
    {
        $im = new \Imagick();
        $im->setResolution(150, 150);
        $im->readImage($pdfPath . '[0]'); // first page only
        $im->setImageFormat('png');
        $im->setImageDepth(8);
        $png = $im->getImageBlob();
        $im->destroy();
        return base64_encode($png);
    }

    private static function convertViaPdftoppm(string $pdfPath, string $bin): string
    {
        $tmp = sys_get_temp_dir() . '/coa_' . uniqid('', true);
        $safe = escapeshellarg($pdfPath);
        $safeTmp = escapeshellarg($tmp);
        shell_exec("$bin -r 150 -f 1 -l 1 -png $safe $safeTmp 2>/dev/null");
        // pdftoppm outputs: {tmp}-1.png or {tmp}-01.png
        $files = glob($tmp . '*.png');
        if (empty($files)) {
            throw new \RuntimeException('pdftoppm produced no output for: ' . $pdfPath);
        }
        $data = base64_encode((string) file_get_contents($files[0]));
        foreach ($files as $f) {
            @unlink($f);
        }
        return $data;
    }

    private static function convertViaConvert(string $pdfPath, string $bin): string
    {
        $tmp = sys_get_temp_dir() . '/coa_' . uniqid('', true) . '.png';
        $safe = escapeshellarg($pdfPath . '[0]');
        $safeTmp = escapeshellarg($tmp);
        shell_exec("$bin -density 150 $safe -quality 90 $safeTmp 2>/dev/null");
        if (!file_exists($tmp)) {
            throw new \RuntimeException('ImageMagick convert produced no output for: ' . $pdfPath);
        }
        $data = base64_encode((string) file_get_contents($tmp));
        @unlink($tmp);
        return $data;
    }

    private static function convertViaGhostScript(string $pdfPath, string $bin): string
    {
        $tmp = sys_get_temp_dir() . '/coa_' . uniqid('', true) . '.png';
        $safe = escapeshellarg($pdfPath);
        $safeTmp = escapeshellarg($tmp);
        shell_exec("$bin -dNOPAUSE -dBATCH -sDEVICE=pngalpha -r150 -dFirstPage=1 -dLastPage=1 -sOutputFile=$safeTmp $safe 2>/dev/null");
        if (!file_exists($tmp)) {
            throw new \RuntimeException('GhostScript produced no output for: ' . $pdfPath);
        }
        $data = base64_encode((string) file_get_contents($tmp));
        @unlink($tmp);
        return $data;
    }

    // -------------------------------------------------------------------------
    // API calls
    // -------------------------------------------------------------------------

    private static function callOpenRouter(string $base64Png): string
    {
        $apiKey = \Config\env('OPENROUTER_API_KEY');
        if (!$apiKey) {
            throw new \RuntimeException('OPENROUTER_API_KEY is not set in .env');
        }

        $payload = json_encode([
            'model'    => self::OPENROUTER_MODEL,
            'messages' => [
                [
                    'role'    => 'user',
                    'content' => [
                        [
                            'type'      => 'image_url',
                            'image_url' => ['url' => 'data:image/png;base64,' . $base64Png],
                        ],
                        ['type' => 'text', 'text' => self::PROMPT],
                    ],
                ],
            ],
            'max_tokens' => 512,
        ]);

        $ch = curl_init(self::OPENROUTER_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
                'HTTP-Referer: ' . (\Config\env('APP_URL') ?: 'https://sacci.app'),
                'X-Title: Sacci Brand Hub',
            ],
            CURLOPT_TIMEOUT        => 90,
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

    /**
     * Call a local Ollama instance (e.g. OLLAMA_URL=http://localhost:11434).
     */
    private static function callOllama(string $baseUrl, string $base64Png): string
    {
        $ollamaModel = \Config\env('OLLAMA_MODEL') ?: 'qwen2.5vl:7b';
        $url = rtrim($baseUrl, '/') . '/api/chat';

        $payload = json_encode([
            'model'  => $ollamaModel,
            'stream' => false,
            'messages' => [
                [
                    'role'    => 'user',
                    'content' => self::PROMPT,
                    'images'  => [$base64Png],
                ],
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
            throw new \RuntimeException('Ollama API error (HTTP ' . $httpCode . '): ' . (string) $response);
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
            'batch_number'      => isset($data['batch_number'])      ? (string) $data['batch_number']      : null,
            'thc_percent'       => isset($data['thc_percent'])       ? (float)  $data['thc_percent']       : null,
            'cbd_percent'       => isset($data['cbd_percent'])       ? (float)  $data['cbd_percent']       : null,
            'cbg_percent'       => isset($data['cbg_percent'])       ? (float)  $data['cbg_percent']       : null,
            'cbn_percent'       => isset($data['cbn_percent'])       ? (float)  $data['cbn_percent']       : null,
            'terp_total_percent'=> isset($data['terp_total_percent'])? (float)  $data['terp_total_percent']: null,
            'terp_1_name'       => isset($data['terp_1_name'])       ? (string) $data['terp_1_name']       : null,
            'terp_1_pct'        => isset($data['terp_1_pct'])        ? (float)  $data['terp_1_pct']        : null,
            'terp_2_name'       => isset($data['terp_2_name'])       ? (string) $data['terp_2_name']       : null,
            'terp_2_pct'        => isset($data['terp_2_pct'])        ? (float)  $data['terp_2_pct']        : null,
            'terp_3_name'       => isset($data['terp_3_name'])       ? (string) $data['terp_3_name']       : null,
            'terp_3_pct'        => isset($data['terp_3_pct'])        ? (float)  $data['terp_3_pct']        : null,
            'lab_name'          => isset($data['lab_name'])          ? (string) $data['lab_name']          : null,
            'raw'               => $raw,
        ];
    }
}

<?php
namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class GeminiService
{
  private string $apiKey;
  private string $base;
  private string $model;

  public function __construct()
  {
    $this->apiKey = config('services.gemini.key', env('GEMINI_API_KEY'));
    $this->base = rtrim(config('services.gemini.base', env('GEMINI_BASE', 'https://generativelanguage.googleapis.com/v1beta')), '/');
    $this->model = config('services.gemini.model', env('GEMINI_MODEL', 'gemini-2.5-flash'));
  }

  public function generate(array $contents, array $options = []): array
  {
    $endpoint = "{$this->base}/models/{$this->model}:generateContent";
    $payload = array_filter([
      'contents' => $contents,            // required
      'systemInstruction' => $options['system'] ?? null,
      'generationConfig' => $options['config'] ?? null,
      'tools' => $options['tools'] ?? null,
      'safetySettings' => $options['safety'] ?? null,
    ], fn($v) => !is_null($v));

    $resp = Http::timeout(60)
      ->withHeaders(['Content-Type' => 'application/json'])
      ->post($endpoint . "?key={$this->apiKey}", $payload);

    if ($resp->failed()) {
      // Surface Google error payloads verbosely for observability
      throw new RequestException($resp);
    }

    return $resp->json();
  }

  public function prompt(string $text, array $options = []): string
  {
    $contents = [
      [
        'role' => 'user',
        'parts' => [['text' => $text]],
      ]
    ];

    $json = $this->generate($contents, $options);

    // standard place for text output in Gemini responses
    return $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
  }
}
?>
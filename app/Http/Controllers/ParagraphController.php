<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostParagraphRequest;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PhpParser\Error;

class ParagraphController extends Controller
{
    public function __construct(private GeminiService $gemini)
    {
    }

    public function post(PostParagraphRequest $request)
    {
        $config = [
            "system" => [
                "parts" => [
                    "text" => "Bạn có nhiệm vụ giúp người dùng trích xuất các từ mới từ đoạn văn bản được cung cấp (đoạn văn bản đó có thể bằng bất kỳ ngôn ngữ nào), trả về dạng JSON với 2 key front là từ gốc trong văn bản, back là nghĩa của từ đó bằng tiếng việt."
                ]
            ],
            "config" => [
                "responseMimeType" => "application/json",
                "responseSchema" => [
                    "type" => "ARRAY",
                    "items" => [
                        "type" => "OBJECT",
                        "properties" => [
                            "front" => ["type" => "STRING"],
                            "back" => ["type" => "STRING"]
                        ],
                        "propertyOrdering" => ["front", "back"]
                    ]
                ]
            ]
        ];

        $answer = $this->gemini->prompt($request->input('content'), $config);

        $data = json_decode($answer, true);

        return Inertia::render("Demo/FlashcardList", ["items" => $data]);
    }
}

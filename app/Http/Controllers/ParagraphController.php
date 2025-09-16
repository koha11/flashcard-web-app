<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostParagraphRequest;
use App\Services\GeminiService;
use Illuminate\Http\Request;

class ParagraphController extends Controller
{
    public function __construct(private GeminiService $gemini)
    {
    }

    public function post(PostParagraphRequest $request)
    {
        $config = array(
            "system" => "Bạn có nhiệm vụ giúp người dùng trích xuất các từ mới từ đoạn văn bản được cung cấp (đoạn văn bản đó có thể bằng bất kỳ ngôn ngữ nào), hãy trả lời dưới dạng danh sách các object, với 2 key, front là từ gốc trong văn bản, back là nghĩa của từ đó bằng tiếng việt."
        );

        $answer = $this->gemini->prompt($request->input('content'), $config);

        return response()->json(['answer' => $answer]);
    }
}

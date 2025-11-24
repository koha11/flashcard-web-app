<?php

return [
    'autogen_base_on_tags' => [
        "system" => [
            "parts" => [
                [
                    "text" => "Bạn có nhiệm vụ giúp người dùng tạo các flashcards theo các tags và mô tả của người dùng. Hãy trả về JSON gồm các từ (term) và nghĩa tiếng Việt (definition). Lưu ý hãy cho definition ngắn gọn thôi không mô tả dài dòng chỉ trả về definition của term"
                ]
            ]
        ],
        "config" => [
            "responseMimeType" => "application/json",
            "responseSchema" => [
                "type" => "ARRAY",
                "items" => [
                    "type" => "OBJECT",
                    "properties" => [
                        "term" => ["type" => "STRING"],
                        "definition" => ["type" => "STRING"]
                    ],
                    "required" => ["term", "definition"]
                ]
            ]
        ]
    ],
];

<?php

return [
  'host' => env('MAIL_HOST', 'smtp.gmail.com'),
  'port' => env('MAIL_PORT', 587),
  'username' => env('MAIL_USERNAME'),
  'password' => env('MAIL_PASSWORD'),
  'encryption' => env('MAIL_ENCRYPTION', 'tls'),
  'from_email' => env('MAIL_FROM_ADDRESS'),
  'from_name' => env('MAIL_FROM_NAME', 'G-Flashcard'),
  'frontend_url' => env('FRONTEND_URL', 'http://localhost:5173'),
];

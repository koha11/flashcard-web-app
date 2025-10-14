<?php
namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(version: '1.0.0', title: 'Flashcard API')]
#[OA\Server(url: '/api', description: 'API base')]
class OpenApi
{
}

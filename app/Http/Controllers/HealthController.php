<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class HealthController extends Controller
{
  #[OA\Get(
    path: '/health',
    tags: ['System'],
    summary: 'Health check',
    responses: [
      new OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(properties: [
          new OA\Property(property: 'status', type: 'string', example: 'ok'),
        ])
      ),
    ]
  )]
  public function __invoke(Request $request)
  {
    return response()->json(['status' => 'ok']);
  }
}

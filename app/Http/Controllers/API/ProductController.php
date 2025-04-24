<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use App\Models\Product;

class ProductController extends Controller
{
    #[OA\Get(
        path: '/m/product/all',
        summary: 'Get all products',
        description: 'Get a list of all products',
        tags: ['Product'],
        parameters: [
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                required: false, // `limit` is not required since you have a default value in your function.
                description: 'Limit of items per page',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 10
                )
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false, // `page` is not required since you have a default value in your function.
                description: 'Page number',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'success',
                            type: 'boolean',
                            example: true
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Products retrieved successfully.'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                        ),
                        new OA\Property(
                            property: 'total',
                            type: 'integer',
                            example: 100
                        ),
                        new OA\Property(
                            property: 'current_page',
                            type: 'integer',
                            example: 1
                        ),
                        new OA\Property(
                            property: 'last_page',
                            type: 'integer',
                            example: 10
                        ),
                        new OA\Property(
                            property: 'per_page',
                            type: 'integer',
                            example: 10
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function all(Request $request) {
        $limit = $request->get('limit', 10);
        $page = $request->get('page', 1);

        $products = Product::paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully.',
            'data' => $products->items(),
            'total' => $products->total(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
        ]);
    }

}

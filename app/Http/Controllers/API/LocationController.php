<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Http\Responses\API\BaseResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use App\Enums\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Models\Location;

class LocationController extends Controller
{
    #[OA\Post(
        path: '/m/location/create',
        summary: 'Add new location',
        description: "Add new location",
        tags: ['Location'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        description: 'Name of the location',
                        example: 'SIT - Singapore Institute of Technology'
                    ),
                    new OA\Property(
                        property: 'address',
                        type: 'string',
                        description: 'address of the location',
                        example: '11 New Punggol Road'
                    ),
                    new OA\Property(
                        property: 'url',
                        type: 'string',
                        description: 'location URL',
                        example: 'https://maps.app.goo.gl/K1oMjsLwU2p2ZJo7A'
                    ),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', description: 'Indicates if the transaction was successful', example: true),
                        new OA\Property(property: 'location', type: 'string', description: 'location data')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function create(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'url' => 'string',
        ]);
        $location = Location::create($request->all());
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $location->id,
                'locationName' => $location->name,
                'address' => $location->address,
                'url' => $location->url
            ]
        ]);
    }


    #[OA\Get(
        path: '/m/location/all',
        description: 'Retrieves all location',
        summary: 'Get location list',
        security: [
            ['Bearer Token' => []],
            ['Access Token' => []]
        ],
        tags: ['Location'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', description: 'The ID of the location', type: 'integer'),
                        new OA\Property(property: 'name', description: 'Location name', type: 'string'),
                        new OA\Property(property: 'address', description: 'Location Address', type: 'string'),
                        new OA\Property(property: 'country', description: 'Location country', type: 'string'),
                        new OA\Property(property: 'postal_code', description: 'Location Postal Code', type: 'string'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function all() {
        $locations = Location::where('status', 1)->get()->map(function ($location) {
            return [
                'id' => $location->id,
                'locationName' => $location->name,
                'address' => $location->address,
                'url' => $location->url
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $locations
        ]);
    }



    #[OA\Post(
        path: '/m/location/toggle-status',
        summary: 'Toggle location status between active and inactive',
        description: "Toggle location status",
        tags: ['Location'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'location_id',
                        type: 'integer',
                        description: 'Location ID',
                        example: '1'
                    ),
                    new OA\Property(
                        property: 'status',
                        type: 'integer',
                        description: 'Location status',
                        example: '1'
                    )
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', description: 'Indicates if the transaction was successful', example: true),
                        new OA\Property(property: 'message', type: 'string', description: 'response message')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function toggleStatus(Request $request) {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'status' => 'required|in:0,1',
        ]);
        Location::findOrFail($request->location_id)->update(['status' => $request->status]);
        return response()->json([
            'success' => true,
            'message' => 'Location status updated successfully'
        ]);
    }
}

<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use App\Models\BannerManagement;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\JsonResponse;


class BannerManagementController extends Controller
{

    #[OA\Get(
        path: '/m/banner-management',
        summary: 'Get Banner Management',
        tags: ['Banner Management'],
        security: [
                ['Bearer Token' => []],
                ['Access Token' => []],
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful Response'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function index()
    {
        $banners = BannerManagement::orderBy('group')
        ->orderBy('order')
        ->get()
        ->groupBy('group')
        ->map(function ($items) {
                return $items->map(function ($item) {
                    $temp = $item->fullmedia;
                    $item->file_path = 'storage'.'/'. $temp->disk. '/' . $temp->id. '/' .$temp->file_name;
                    unset($item->fullmedia);
                    return $item;
                });
        });

        return response()->json([
            'success' => true,
            'data' => $banners
        ]);
    }

    #[OA\Post(
        path: '/m/banner-management/create',
        summary: 'Create new banner',
        description: 'Create new banner',
        tags: ['Banner Management'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'url',
                            type: 'string',
                            description: 'Url Target',
                            example: 'https://example.com/endpoint'
                        ),
                        new OA\Property(
                            property: 'group',
                            type: 'string',
                            example: 'Featured Management'
                        ),
                        new OA\Property(
                            property: 'file',
                            type: 'string',
                            format: "binary",
                            description: 'Please upload image',
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:20048',
        ]);
        $lastBanner = BannerManagement::where('group', Str::snake($request->input('group')))->orderBy('order', 'desc')->first();

        $banner = new BannerManagement();
        $banner->url = !empty($request->input('url')) ? $request->input('url') : '';
        if (!empty($lastBanner)) {
            $banner->group = Str::snake($lastBanner->group);
            $banner->order = $lastBanner->order + 1;
        } else {
            $banner->group = !empty($request->input('group')) ? Str::snake($request->input('group')) : '';
            $banner->order = 1;
        }
        $banner->save();

        if ($request->hasFile('file')) {

            $directory = 'public/banner_mgmt';
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }

            $randomString = Str::random(10);
            $originalFileName = $request->file('file')->getClientOriginalName();
            $extension = $request->file('file')->getClientOriginalExtension();
            $newFileName = pathinfo($originalFileName, PATHINFO_FILENAME) . '_' . $randomString . '.' . $extension;

            $data = $banner->addMediaFromRequest('file')
                ->usingFileName($newFileName)
                ->toMediaCollection('images', 'banner_mgmt');

            $banner->media_id = $data->id;
            $banner->save();

        }

        return response()->json([
            'success' => true,
            'message' => "Create success.",
        ]);
    }

    #[OA\Post(
        path: '/m/banner-management/update',
        summary: 'Update banner by ID',
        description: 'Update banner by ID',
        tags: ['Banner Management'],
        // parameters: [
        //     new OA\Parameter(
        //         in: 'path',
        //         name: 'id',
        //         required: true
        //     )
        // ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'id',
                            type: 'integer'
                        ),
                        new OA\Property(
                            property: 'url',
                            type: 'string',
                            description: 'Url Target',
                            example: 'https://example.com/endpoint'
                        ),
                        new OA\Property(
                            property: 'file',
                            type: 'string',
                            format: "binary",
                            description: 'Please upload image',
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function update(Request $request)
    {
        $request->validate([
            // 'file' => 'image|max:20048',
            'id' => 'required'
        ]);

        $banner = BannerManagement::find($request->id);

        if(empty($banner)){
            return response()->json([
                'success' => false,
                'message' => 'Banner not found.'
            ]);
        }

        $banner->url = $request->input('url', $banner->url);

        if ($request->hasFile('file')) {

            $directory = 'public/banner_mgmt';
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }

            $banner->clearMediaCollection('images');

            $randomString = Str::random(10);
            $originalFileName = $request->file('file')->getClientOriginalName();
            $extension = $request->file('file')->getClientOriginalExtension();
            $newFileName = pathinfo($originalFileName, PATHINFO_FILENAME) . '_' . $randomString . '.' . $extension;

            $data = $banner->addMediaFromRequest('file')
                ->usingFileName($newFileName)
                ->toMediaCollection('images', 'banner_mgmt');

            $banner->media_id = $data->id;

        }
        $banner->save();

        return new JsonResponse([
                'success' => true,
                'data' => 'Successful response'
        ]);

    }

    #[OA\Delete(
        path: '/m/banner-management/delete/{id}',
        summary: 'Delete banner by ID',
        description: 'Delete banner by ID',
        tags: ['Banner Management'],
        parameters: [
            new OA\Parameter(
                in: 'path',
                name: 'id',
                required: true,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function destroy($id)
    {
        $banner = BannerManagement::find($id);
        if(empty($banner)){
            return response()->json([
                'success' => false,
                'message' => 'Delete failed.'
            ]);
        }

        $banner->clearMediaCollection('images');
        $banner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Banner deleted successfully.',
        ]);
    }

    #[OA\Post(
        path: '/m/banner-management/sort',
        summary: 'Sort banner',
        description: 'Sort banner',
        tags: ['Banner Management'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'list_banner',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(
                                    property: 'id',
                                    type: 'number',
                                    example: '1'
                                ),
                                new OA\Property(
                                    property: 'order',
                                    type: 'number',
                                    example: 1
                                ),
                            ],
                            type: 'object'
                        )
                    )
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function sort(Request $request)
    {
        $banner = BannerManagement::get();

        if(count($request->list_banner) != count($banner)){
             return response()->json([
                'success' => false,
                'message' => 'Please fill out Item completely.'
            ]);
        }

        $bannerIds = array_unique(array_column($request->list_banner, 'id'));
        $existingBanners =BannerManagement::whereIn('id', $bannerIds)
                    ->pluck('id')
                    ->toArray();
        $nonExistingBanners = array_diff($bannerIds, $existingBanners);
        if (!empty($nonExistingBanners)) {
            return response()->json([
                'success' => false,
                'message' => 'ID ' . implode(',', $nonExistingBanners) . ' does not exist!'
            ]);
        }


        foreach ($request->list_banner as $bannerData) {
            $id = $bannerData['id'];
            $order = $bannerData['order'];
            BannerManagement::where('id', $id)->update(['order' => $order]);
        }

        return new JsonResponse([
                'success' => true,
                'data' => 'Successful response'
        ]);
    }
}

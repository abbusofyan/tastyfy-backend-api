<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\BannerManagement;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BannerManagementController extends Controller
{

    public function index()
    {
        $banners = BannerManagement::orderBy('group')
            ->orderBy('order')
            ->get()
            ->groupBy('group')
            ->map(function ($items) {
                return $items->map(function ($item) {
                    $temp = $item->fullmedia;
                    $item->file_name = 'storage' . '/' . $temp->disk . '/' . $temp->id . '/' . $temp->file_name;
                    $item->original_name = $temp->file_name;
                    unset($item->fullmedia);
                    return $item;
                });
            });

        //Duy
        return Inertia::render('Banner/Index', [
            'banners' => $banners,
        ]);
        // $banners;
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:20048',
            'url' => 'required|url',
            'group' => 'required|string',
        ]);
        $lastBanner = BannerManagement::where('group', $request->input('group'))->orderBy('order', 'desc')->first();

        $banner = new BannerManagement();
        $banner->url = !empty($request->input('url')) ? $request->input('url') : '';
        if ($lastBanner) {
            $banner->group = $lastBanner->group;
            $banner->order = $lastBanner->order + 1;
        } else {
            $banner->group = !empty($request->input('group')) ? $request->input('group') : '';
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

        //Duy
        // return $banner;
        return redirect()->back();
    }

    public function update(Request $request)
    {
        $request->validate([
            // 'file' => 'image|max:20048',
            'id' => 'required',
        ]);

        $banner = BannerManagement::find($request->id);

        if (empty($banner)) {
            return 'Banner not found.';
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

        //Duy
        return $banner;
    }

    public function fetchData(Request $request)
    {

        $banners = BannerManagement::orderBy('group')
            ->orderBy('order')
            ->get()
            ->groupBy('group')
            ->map(function ($items) {
                return $items->map(function ($item) {
                    $temp = $item->fullmedia;
                    $item->file_name = 'storage' . '/' . $temp->disk . '/' . $temp->id . '/' . $temp->file_name;
                    $item->original_name = $temp->file_name;
                    unset($item->fullmedia);
                    return $item;
                });
            });

        // dd($banners);

        // Return the paginated users
        return response()->json(['banners' => $banners]);
    }

    public function destroy($id)
    {
        $banner = BannerManagement::find($id);
        if (empty($banner)) {
            return 'Delete failed.';
        }

        $banner->clearMediaCollection('images');
        $banner->delete();

        //Duy
        return 'Banner deleted successfully.';
    }

    public function sort(Request $request)
    {
        $banner = BannerManagement::get();

        if (count($request->list_banner) != count($banner)) {
            return "Please fill out Item completely.";
        }

        $bannerIds = array_unique(array_column($request->list_banner, 'id'));
        $existingBanners = BannerManagement::whereIn('id', $bannerIds)
            ->pluck('id')
            ->toArray();
        $nonExistingBanners = array_diff($bannerIds, $existingBanners);
        if (!empty($nonExistingBanners)) {
            return 'ID ' . implode(',', $nonExistingBanners) . ' does not exist!';
        }

        foreach ($request->list_banner as $bannerData) {
            $id = $bannerData['id'];
            $order = $bannerData['order'];
            BannerManagement::where('id', $id)->update(['order' => $order]);
        }

        //Duy
        return $banner;
    }

    public function updateOrder(Request $request)
    {
        $banners = $request->input('banners');
        $group = $banners[0]['group'];
        $existingBannerIds = BannerManagement::where('group', $group)->pluck('id')->toArray();
        $newBannerIds = array_column($banners, 'id');

        // Update the order for existing banners
        foreach ($banners as $index => $banner) {
            BannerManagement::where('id', $banner['id'])->update(['order' => $index]);
        }

        // Delete banners that are no longer in the list
        $bannersToDelete = array_diff($existingBannerIds, $newBannerIds);
        BannerManagement::whereIn('id', $bannersToDelete)->delete();

        return response()->json(['message' => 'Order updated successfully']);
    }
}

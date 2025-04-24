<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BannerController extends Controller
{
    public function index()
    {
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->take(10)->get();

        // Pass the users data to the Inertia view
        return Inertia::render('Banner/Index', [
            'users' => $users,
        ]);
    }

    public function fetchData(Request $request)
    {

        // Get the page number, items per page, sorting, and search query from the request
        $page = $request->input('page', 1);
        $itemsPerPage = $request->input('itemsPerPage', 10);
        $sortBy = $request->input('sortBy', 'id'); // Default sorting by 'id'
        $sortOrder = $request->input('sortOrder', 'desc');
        $search = $request->input('search', '');

        // Start query for users
        $usersQuery = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        });


        // Apply search filter if search query is present
        if (!empty($search)) {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        // Apply sorting
        $usersQuery->orderBy($sortBy, $sortOrder);

        // Paginate the users
        $users = $usersQuery->paginate($itemsPerPage);

        // Return the paginated users
        return response()->json(['users' => $users]);
    }
}

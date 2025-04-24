<?php

namespace App\Http\Controllers;

use App\Enums\CustomerRole;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicUserController extends Controller
{
    //

    public function index()
    {
        // Dummy data
        $customers = Customer::whereHas('roles', function ($query) {
            $query->where('name', CustomerRole::PUBLIC->value);
        })->paginate(10);


        // Pass the users data to the Inertia view
        return Inertia::render('PublicUsers/Index', [
            'users' => $customers,
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
        $usersQuery = Customer::whereHas('roles', function ($query) {
            $query->where('name', CustomerRole::PUBLIC->value);
        });

        // Apply search filter if search query is present
        if (!empty($search)) {
            $usersQuery->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('id', 'like', '%'.$search.'%');
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

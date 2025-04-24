<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\AdminRequest;

class AdminUserController extends Controller
{
    //

    public function index()
    {
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->take(10)->get();

        // Pass the users data to the Inertia view
        return Inertia::render('AdminUsers/Index', [
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

    public function store(AdminRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            //create a user first
            $adminUser = User::create([
                'email'    => $data['email'],
                'is_active'    => $data['status'],
                'phone'    => $data['phone'],
                'password' => Hash::make($data['password']),
                'name'     => $data['name'],
            ]);

            // Assign admin role to the user
            $adminUser->assignRole('admin');


            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'data'    => $adminUser,
            ], 201);
            //            DB::rollBack();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e->getMessage()], $e->getCode());
        }
    }

    public function update(AdminRequest $request)
    {
        // Fetch the admin to be updated

        $adminUser = User::findOrFail($request->id);

        $data = $request->validated();

        DB::beginTransaction();
        try {
            $adminUser->update([
                'email' => $data['email'] ?? $adminUser->email,
                'name'  => $data['name'] ?? $adminUser->name,
                'phone'  => $data['phone'] ?? $adminUser->phone,
                'is_active'  => $data['status'] ?? $adminUser->status,
            ]);

            if (isset($data['password'])) {
                $adminUser->update([
                    'password' => Hash::make($data['password']),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully',
                'data'    => $adminUser,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e->getMessage()], $e->getCode());
        }
    }
}

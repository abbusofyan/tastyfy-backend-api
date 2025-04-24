<?php

namespace App\Http\Controllers;

use App\Enums\CustomerRole;
use App\Models\CustomerRole as Role;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;

class BeneficiariesUserController extends Controller
{
    //

    public function index()
    {
        // Dummy data
        $customers = Customer::whereHas('roles', function ($query) {
            $query->where('name', CustomerRole::BENEFICIARIES->value);
        })->paginate(10);

        // Pass the users data to the Inertia view
        return Inertia::render('BeneficiariesUsers/Index', [
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
            $query->where('name', CustomerRole::BENEFICIARIES->value);
        });


        // Apply search filter if search query is present
        if (!empty($search)) {
            $usersQuery->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        // Apply sorting
        $usersQuery->orderBy($sortBy, $sortOrder);

        // Paginate the users
        $users = $usersQuery->paginate($itemsPerPage);

        // Return the paginated users
        return response()->json(['users' => $users]);
    }

    public function addCredit(Request $request)
    {
        $user = auth()->user();
        $store = $user->store;
        $customer = Customer::where('customer_id', $request->id)->first();

        if ($request->credit == 0) {
            return redirect()->back()->withErrors(['message' => "Credit amount required or not 0"]);
        }

        $current_credit = $customer->credit_balance;
        $new_credit = $current_credit + $request->credit;

        // dd($new_credit);

        $customer->credit_balance = $new_credit;
        $customer->save();

        // // Update customer details
        // $customer->update($request->all());

        return redirect()->back();
    }

    public function creditById($id)
    {
        $customer = Customer::where('customer_id', $id)->get();

        return response()->json([
            'customer' => $customer
        ]);
    }

    public function import(Request $request) {
        $request->validate([
            'files.*' => 'required|mimes:xlsx,xls,csv',
        ]);

        $role = Role::where('name', CustomerRole::BENEFICIARIES->value)->first();

        foreach ($request->file('files') as $file) {
            Excel::import(new UsersImport($role), $file);
        }

        return redirect()->back();
    }
}

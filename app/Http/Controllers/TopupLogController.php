<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WalletHistory;
use App\Models\Topup;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TopupLogController extends Controller
{
    //

    public function index()
    {

        $topups = Topup::with('customer', 'walletHistory', 'customer.user')->get();

        // Pass the topups data to the Inertia view
        return Inertia::render('TopupLog/Index', [
            'topups' => $topups,
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

        // Start query for topups
        $topupsQuery = Topup::with('customer', 'walletHistory', 'customer.user');


        // Apply search filter if search query is present
        if (!empty($search)) {
            $topupsQuery->whereHas('customer.user', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Apply sorting
        $topupsQuery->orderBy($sortBy, $sortOrder);

        // Paginate the topups
        $topups = $topupsQuery->paginate($itemsPerPage);

        // Format transaction_date to Singapore time
        foreach ($topups as $topup) {
            $topup->transaction_date = Carbon::parse($topup->created_at)
                ->format('l, d F Y \a\t h:i A');
        }


        // Return the paginated topups
        return response()->json(['topups' => $topups]);
    }

    public function exportToCSV()
    {
        $filename = 'topups_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(
            function () {
                $handle = fopen('php://output', 'w');

                // Add headers
                fputcsv($handle, [
                    'ID',
                    'Customer Name',
                    'Customer Phone',
                    'Amount',
                    'Status',
                    'Created At',
                ]);

                $topups = Topup::with('customer.user')->get();
                foreach ($topups as $topup) {
                    fputcsv($handle, [
                        $topup->id,
                        $topup->customer->user->name,
                        $topup->customer->user->phone,
                        $topup->amount,
                        $topup->status == 1 ? 'Success' : 'Failed',
                        Carbon::parse($topup->created_at)->format('l, d F Y \a\t h:i A'),
                    ]);
                }

                fclose($handle);
            },
            200,
            $headers
        );
    }
}

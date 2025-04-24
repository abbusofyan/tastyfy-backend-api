<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WalletHistory;
use App\Models\Transaction;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TransactionLogController extends Controller
{
    //

    public function index()
    {

        $transactions = WalletHistory::with('admin', 'customer', 'customer.user', 'payments', 'payments.transaction', 'payments.transaction.items')->where('type', '!=', 'topup')->get();

        // Pass the transactions data to the Inertia view
        return Inertia::render('TransactionLog/Index', [
            'transactions' => $transactions,
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

        // Start query for transactions
        $transactionsQuery = WalletHistory::where('type', '!=', 'topup')->with('admin', 'customer', 'payments', 'payments.transaction', 'payments.transaction.items', 'customer.user');


        // Apply search filter if search query is present
        if (!empty($search)) {
            $transactionsQuery->whereHas('customer.user', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Apply sorting
        $transactionsQuery->orderBy($sortBy, $sortOrder);

        // Paginate the transactions
        $transactions = $transactionsQuery->paginate($itemsPerPage);

        // Format transaction_date to Singapore time
        foreach ($transactions as $transaction) {
            $transaction->transaction_date = Carbon::parse($transaction->created_at)
                ->format('l, d F Y \a\t h:i A');
        }


        // Return the paginated transactions
        return response()->json(['transactions' => $transactions]);
    }

    public function exportToCSV()
    {
        $filename = 'transactions_' . date('Y-m-d_His') . '.csv';
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
                    'Amount Credit',
                    'Amount Cash',
                    'Type',
                    'Created At',
                ]);

                $transactions = WalletHistory::with('admin', 'customer', 'customer.user', 'payments', 'payments.transaction', 'payments.transaction.items')->where('type', '!=', 'topup')->get();
                // dd($transactions->toArray());
                foreach ($transactions as $transactionData) {
                    fputcsv($handle, [
                        $transactionData->id,
                        $transactionData->customer->user->name,
                        $transactionData->customer->user->phone,
                        $transactionData->credit,
                        $transactionData->cash,
                        $transactionData->type == 'manual_adjustment' ? 'Credit manually adjusted by ' . $transactionData->admin->name : 'Purchase items from Vending Machine #' .
                            $transactionData->payments->first()->transaction->first()->vending_machine_id,
                        Carbon::parse($transactionData->created_at)->format('l, d F Y \a\t h:i A'),
                    ]);
                }

                fclose($handle);
            },
            200,
            $headers
        );
    }
}

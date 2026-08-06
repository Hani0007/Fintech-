<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Generate report based on type and filters.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:daily,weekly,monthly,yearly,custom',
            'start_date' => 'nullable|date|required_if:type,custom',
            'end_date' => 'nullable|date|required_if:type,custom|after_or_equal:start_date',
            'category_id' => 'nullable|exists:categories,id',
            'account_id' => 'nullable|exists:accounts,id',
            'transaction_type' => 'nullable|in:income,expense',
        ]);

        $userId = Auth::id();
        $query = Transaction::where('user_id', $userId)
            ->with(['category', 'account']);

        // Apply date range filter based on report type
        switch ($request->type) {
            case 'daily':
                $query->whereDate('transaction_date', today());
                break;
            case 'weekly':
                $query->whereBetween('transaction_date', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]);
                break;
            case 'monthly':
                $query->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year);
                break;
            case 'yearly':
                $query->whereYear('transaction_date', now()->year);
                break;
            case 'custom':
                if ($request->start_date && $request->end_date) {
                    $query->whereBetween('transaction_date', [
                        $request->start_date,
                        $request->end_date
                    ]);
                }
                break;
        }

        // Apply additional filters
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->account_id) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->transaction_type) {
            $query->where('type', $request->transaction_type);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        // Calculate summary statistics
        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpenses = $transactions->where('type', 'expense')->sum('amount');
        $netBalance = $totalIncome - $totalExpenses;

        // Group by category for breakdown
        $categoryBreakdown = $transactions->groupBy('category_id')
            ->map(function ($items) {
                return [
                    'category' => $items->first()->category,
                    'total' => $items->sum('amount'),
                    'count' => $items->count(),
                ];
            })
            ->values()
            ->sortByDesc('total');

        // Group by account for breakdown
        $accountBreakdown = $transactions->groupBy('account_id')
            ->map(function ($items) {
                return [
                    'account' => $items->first()->account,
                    'total' => $items->sum('amount'),
                    'count' => $items->count(),
                ];
            })
            ->values()
            ->sortByDesc('total');

        return response()->json([
            'summary' => [
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'net_balance' => $netBalance,
                'transaction_count' => $transactions->count(),
            ],
            'transactions' => $transactions,
            'category_breakdown' => $categoryBreakdown,
            'account_breakdown' => $accountBreakdown,
            'filters' => [
                'type' => $request->type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'category_id' => $request->category_id,
                'account_id' => $request->account_id,
                'transaction_type' => $request->transaction_type,
            ],
        ]);
    }

    /**
     * Export report to CSV.
     */
    public function exportCsv(Request $request)
    {
        $reportData = $this->generate($request)->getData(true);
        $transactions = collect($reportData->transactions);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="report_' . $request->type . '_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Title', 'Type', 'Category', 'Account', 'Amount', 'Notes']);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->transaction_date,
                    $transaction->title,
                    $transaction->type,
                    $transaction->category->name,
                    $transaction->account->name,
                    $transaction->amount,
                    $transaction->notes,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display reports page.
     */
    public function index()
    {
        $categories = Category::where('user_id', Auth::id())->get();
        $accounts = \App\Models\Account::where('user_id', Auth::id())->get();

        return inertia('Reports', [
            'categories' => $categories,
            'accounts' => $accounts,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     */
    public function stats()
    {
        $userId = Auth::id();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Get user's primary currency (from first account or default to USD)
        $primaryAccount = Account::where('user_id', $userId)->first();
        $currency = $primaryAccount ? $primaryAccount->currency : 'USD';

        // Calculate total balance from all accounts
        $totalBalance = Account::where('user_id', $userId)
            ->sum('balance');

        // Calculate total income for current month
        $totalIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear)
            ->sum('amount');

        // Calculate total expenses for current month
        $totalExpenses = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear)
            ->sum('amount');

        // Calculate remaining budget
        $totalBudget = Budget::where('user_id', $userId)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->sum('amount');

        $remainingBudget = $totalBudget - $totalExpenses;

        // Calculate net savings (income - expenses)
        $netSavings = $totalIncome - $totalExpenses;

        // Category breakdown for current month
        $categoryBreakdown = Transaction::where('user_id', $userId)
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear)
            ->with('category')
            ->get()
            ->groupBy('category.name')
            ->map(function ($transactions) {
                return [
                    'amount' => $transactions->sum('amount'),
                    'count' => $transactions->count(),
                    'type' => $transactions->first()->type,
                ];
            });

        // Income by category
        $incomeByCategory = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear)
            ->with('category')
            ->get()
            ->groupBy('category.name')
            ->map(function ($transactions) {
                return $transactions->sum('amount');
            });

        // Expense by category
        $expenseByCategory = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear)
            ->with('category')
            ->get()
            ->groupBy('category.name')
            ->map(function ($transactions) {
                return $transactions->sum('amount');
            });

        // Monthly trend for last 6 months
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->month;
            $year = now()->subMonths($i)->year;
            $monthName = now()->subMonths($i)->format('M');

            $monthlyIncome = Transaction::where('user_id', $userId)
                ->where('type', 'income')
                ->whereMonth('transaction_date', $month)
                ->whereYear('transaction_date', $year)
                ->sum('amount');

            $monthlyExpense = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->whereMonth('transaction_date', $month)
                ->whereYear('transaction_date', $year)
                ->sum('amount');

            $monthlyTrend[] = [
                'month' => $monthName,
                'income' => $monthlyIncome,
                'expense' => $monthlyExpense,
            ];
        }

        return response()->json([
            'total_balance' => $totalBalance,
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'remaining_budget' => $remainingBudget > 0 ? $remainingBudget : 0,
            'total_budget' => $totalBudget,
            'net_savings' => $netSavings,
            'currency' => $currency,
            'category_breakdown' => $categoryBreakdown,
            'income_by_category' => $incomeByCategory,
            'expense_by_category' => $expenseByCategory,
            'monthly_trend' => $monthlyTrend,
        ]);
    }

    /**
     * Display the dashboard.
     */
    public function index()
    {
        return inertia('Dashboard');
    }
}

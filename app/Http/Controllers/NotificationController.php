<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Budget;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user.
     */
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        $unreadCount = $notifications->where('is_read', false)->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Notification $notification)
    {
        $this->authorize('view', $notification);
        
        $notification->update(['is_read' => true]);

        return response()->json($notification);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    /**
     * Delete notification.
     */
    public function destroy(Notification $notification)
    {
        $this->authorize('delete', $notification);
        
        $notification->delete();

        return response()->json(null, 204);
    }

    /**
     * Check and create budget notifications.
     */
    public function checkBudgetNotifications()
    {
        $userId = Auth::id();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $budgets = Budget::where('user_id', $userId)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->with('category')
            ->get();

        foreach ($budgets as $budget) {
            $percentageUsed = $budget->percentage_used;

            // Check if budget exceeds 80%
            if ($percentageUsed >= 80 && $percentageUsed < 100) {
                $existingNotification = Notification::where('user_id', $userId)
                    ->where('title', 'Budget Alert')
                    ->where('message', 'like', "%{$budget->category->name}%")
                    ->where('created_at', '>=', now()->startOfDay())
                    ->first();

                if (!$existingNotification) {
                    Notification::create([
                        'user_id' => $userId,
                        'title' => 'Budget Alert',
                        'message' => "You have used {$percentageUsed}% of your budget for {$budget->category->name}",
                    ]);
                }
            }

            // Check if budget exceeds 100%
            if ($percentageUsed >= 100) {
                $existingNotification = Notification::where('user_id', $userId)
                    ->where('title', 'Budget Exceeded')
                    ->where('message', 'like', "%{$budget->category->name}%")
                    ->where('created_at', '>=', now()->startOfDay())
                    ->first();

                if (!$existingNotification) {
                    Notification::create([
                        'user_id' => $userId,
                        'title' => 'Budget Exceeded',
                        'message' => "You have exceeded your budget for {$budget->category->name} by " . ($percentageUsed - 100) . "%",
                    ]);
                }
            }
        }
    }

    /**
     * Check and create savings goal notifications.
     */
    public function checkSavingsGoalNotifications()
    {
        $userId = Auth::id();

        $savingsGoals = SavingsGoal::where('user_id', $userId)
            ->where('status', 'active')
            ->get();

        foreach ($savingsGoals as $goal) {
            // Check if goal is achieved
            if ($goal->saved_amount >= $goal->target_amount) {
                $existingNotification = Notification::where('user_id', $userId)
                    ->where('title', 'Savings Goal Achieved')
                    ->where('message', 'like', "%{$goal->title}%")
                    ->first();

                if (!$existingNotification) {
                    Notification::create([
                        'user_id' => $userId,
                        'title' => 'Savings Goal Achieved',
                        'message' => "Congratulations! You have achieved your savings goal: {$goal->title}",
                    ]);

                    // Update goal status
                    $goal->update(['status' => 'completed']);
                }
            }
        }
    }

    /**
     * Create monthly report notification.
     */
    public function createMonthlyReportNotification()
    {
        $userId = Auth::id();
        $lastMonth = now()->subMonth();
        $lastMonthName = $lastMonth->format('F');

        $totalIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereMonth('transaction_date', $lastMonth->month)
            ->whereYear('transaction_date', $lastMonth->year)
            ->sum('amount');

        $totalExpenses = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', $lastMonth->month)
            ->whereYear('transaction_date', $lastMonth->year)
            ->sum('amount');

        Notification::create([
            'user_id' => $userId,
            'title' => 'Monthly Report Ready',
            'message' => "Your {$lastMonthName} report is ready. Income: ${$totalIncome}, Expenses: ${$totalExpenses}",
        ]);
    }
}

<?php

namespace App\Console\Commands;

use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\Notification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

#[Signature('app:process-recurring-transactions')]
#[Description('Process recurring transactions that are due')]
class ProcessRecurringTransactions extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing recurring transactions...');

        $today = now()->startOfDay();
        
        // Get all active recurring transactions that are due today or overdue
        $recurringTransactions = RecurringTransaction::where('status', 'active')
            ->where('next_run', '<=', $today)
            ->with(['account', 'category'])
            ->get();

        $processedCount = 0;

        foreach ($recurringTransactions as $recurring) {
            try {
                // Create the transaction
                $transaction = Transaction::create([
                    'user_id' => $recurring->user_id,
                    'account_id' => $recurring->account_id,
                    'category_id' => $recurring->category_id,
                    'title' => $recurring->title,
                    'type' => 'expense', // Most recurring transactions are expenses
                    'amount' => $recurring->amount,
                    'transaction_date' => $recurring->next_run,
                    'notes' => 'Recurring transaction',
                ]);

                // Update account balance
                $account = $recurring->account;
                $account->balance -= $recurring->amount;
                $account->save();

                // Create notification
                Notification::create([
                    'user_id' => $recurring->user_id,
                    'title' => 'Recurring Transaction Processed',
                    'message' => "Your recurring transaction '{$recurring->title}' of ${$recurring->amount} has been processed.",
                ]);

                // Calculate next run date based on frequency
                $nextRun = $this->calculateNextRun($recurring->frequency, $recurring->next_run);
                
                if ($nextRun) {
                    $recurring->update(['next_run' => $nextRun]);
                } else {
                    // If no next run date, pause the recurring transaction
                    $recurring->update(['status' => 'paused']);
                }

                $processedCount++;
                $this->info("Processed recurring transaction: {$recurring->title}");
            } catch (\Exception $e) {
                $this->error("Failed to process recurring transaction {$recurring->id}: {$e->getMessage()}");
            }
        }

        $this->info("Processed {$processedCount} recurring transactions.");
        return Command::SUCCESS;
    }

    /**
     * Calculate the next run date based on frequency.
     */
    private function calculateNextRun(string $frequency, \DateTime $currentDate): ?\DateTime
    {
        $nextRun = clone $currentDate;

        switch ($frequency) {
            case 'daily':
                $nextRun->addDay();
                break;
            case 'weekly':
                $nextRun->addWeek();
                break;
            case 'monthly':
                $nextRun->addMonth();
                break;
            case 'yearly':
                $nextRun->addYear();
                break;
            default:
                return null;
        }

        return $nextRun;
    }
}

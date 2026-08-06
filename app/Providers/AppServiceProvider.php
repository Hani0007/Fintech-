<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use App\Models\Category;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\SavingsGoal;
use App\Models\Notification;
use App\Policies\CategoryPolicy;
use App\Policies\AccountPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\BudgetPolicy;
use App\Policies\SavingsGoalPolicy;
use App\Policies\NotificationPolicy;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Account::class, AccountPolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);
        Gate::policy(Budget::class, BudgetPolicy::class);
        Gate::policy(SavingsGoal::class, SavingsGoalPolicy::class);
        Gate::policy(Notification::class, NotificationPolicy::class);
        Gate::policy(RecurringTransaction::class, RecurringTransactionPolicy::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}

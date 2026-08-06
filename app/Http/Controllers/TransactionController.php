<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = Transaction::where('user_id', Auth::id())
            ->with(['account', 'category'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($transactions);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accounts = \App\Models\Account::where('user_id', Auth::id())->get();
        $categories = \App\Models\Category::where('user_id', Auth::id())->get();

        return inertia('Transactions/Create', [
            'accounts' => $accounts->toArray(),
            'categories' => $categories->toArray(),
        ]);
    }

    /**
     * Show the form for creating a new income.
     */
    public function createIncome()
    {
        $accounts = \App\Models\Account::where('user_id', Auth::id())->get();
        $categories = \App\Models\Category::where('user_id', Auth::id())->get();

        return inertia('Transactions/Create', [
            'accounts' => $accounts->toArray(),
            'categories' => $categories->toArray(),
            'defaultType' => 'income',
        ]);
    }

    /**
     * Show the form for creating a new expense.
     */
    public function createExpense()
    {
        $accounts = \App\Models\Account::where('user_id', Auth::id())->get();
        $categories = \App\Models\Category::where('user_id', Auth::id())->get();

        return inertia('Transactions/Create', [
            'accounts' => $accounts->toArray(),
            'categories' => $categories->toArray(),
            'defaultType' => 'expense',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request)
    {
        $receiptPath = null;
        
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'account_id' => $request->account_id,
            'category_id' => $request->category_id,
            'title' => $request->title,
            'type' => $request->type,
            'amount' => $request->amount,
            'transaction_date' => $request->transaction_date,
            'receipt' => $receiptPath,
            'notes' => $request->notes,
        ]);

        // Update account balance
        $account = $transaction->account;
        if ($transaction->type === 'income') {
            $account->balance += $transaction->amount;
        } else {
            $account->balance -= $transaction->amount;
        }
        $account->save();
        
        return redirect()->route('dashboard')->with('success', 'Transaction created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);
        return response()->json($transaction->load(['account', 'category']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $accounts = \App\Models\Account::where('user_id', Auth::id())->get();
        $categories = \App\Models\Category::where('user_id', Auth::id())->get();

        return inertia('Transactions/Edit', [
            'transaction' => $transaction->load(['account', 'category'])->toArray(),
            'accounts' => $accounts->toArray(),
            'categories' => $categories->toArray(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);
        
        // Store old values for balance adjustment
        $oldAmount = $transaction->amount;
        $oldType = $transaction->type;
        $oldAccountId = $transaction->account_id;

        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            // Delete old receipt if exists
            if ($transaction->receipt) {
                Storage::disk('public')->delete($transaction->receipt);
            }
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
            $transaction->receipt = $receiptPath;
        }

        $transaction->update($request->except('receipt'));

        // Update account balances
        if ($oldAccountId != $request->account_id) {
            // Revert old account balance
            $oldAccount = \App\Models\Account::find($oldAccountId);
            if ($oldType === 'income') {
                $oldAccount->balance -= $oldAmount;
            } else {
                $oldAccount->balance += $oldAmount;
            }
            $oldAccount->save();

            // Update new account balance
            $newAccount = $transaction->account;
            if ($transaction->type === 'income') {
                $newAccount->balance += $transaction->amount;
            } else {
                $newAccount->balance -= $transaction->amount;
            }
            $newAccount->save();
        } elseif ($oldAmount != $request->amount || $oldType != $request->type) {
            // Adjust same account balance
            $account = $transaction->account;
            
            // Revert old transaction
            if ($oldType === 'income') {
                $account->balance -= $oldAmount;
            } else {
                $account->balance += $oldAmount;
            }
            
            // Apply new transaction
            if ($transaction->type === 'income') {
                $account->balance += $transaction->amount;
            } else {
                $account->balance -= $transaction->amount;
            }
            $account->save();
        }
        
        return response()->json($transaction->load(['account', 'category']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);
        
        // Update account balance
        $account = $transaction->account;
        if ($transaction->type === 'income') {
            $account->balance -= $transaction->amount;
        } else {
            $account->balance += $transaction->amount;
        }
        $account->save();

        // Delete receipt if exists
        if ($transaction->receipt) {
            Storage::disk('public')->delete($transaction->receipt);
        }
        
        $transaction->delete();
        
        return response()->json(null, 204);
    }
}

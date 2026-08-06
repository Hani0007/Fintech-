<?php

namespace App\Http\Controllers;

use App\Models\RecurringTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecurringTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $recurringTransactions = RecurringTransaction::where('user_id', Auth::id())
            ->with(['account', 'category'])
            ->orderBy('next_run')
            ->get();
        
        return response()->json($recurringTransactions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'next_run' => 'required|date',
        ]);

        $recurringTransaction = RecurringTransaction::create([
            'user_id' => Auth::id(),
            'account_id' => $request->account_id,
            'category_id' => $request->category_id,
            'title' => $request->title,
            'amount' => $request->amount,
            'frequency' => $request->frequency,
            'next_run' => $request->next_run,
            'status' => 'active',
        ]);

        return response()->json($recurringTransaction->load(['account', 'category']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(RecurringTransaction $recurringTransaction)
    {
        $this->authorize('view', $recurringTransaction);
        return response()->json($recurringTransaction->load(['account', 'category']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RecurringTransaction $recurringTransaction)
    {
        $this->authorize('update', $recurringTransaction);
        
        $request->validate([
            'account_id' => 'sometimes|required|exists:accounts,id',
            'category_id' => 'sometimes|required|exists:categories,id',
            'title' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0',
            'frequency' => 'sometimes|required|in:daily,weekly,monthly,yearly',
            'next_run' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:active,paused,cancelled',
        ]);

        $recurringTransaction->update($request->all());
        
        return response()->json($recurringTransaction->load(['account', 'category']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RecurringTransaction $recurringTransaction)
    {
        $this->authorize('delete', $recurringTransaction);
        
        $recurringTransaction->delete();
        
        return response()->json(null, 204);
    }

    /**
     * Pause a recurring transaction.
     */
    public function pause(RecurringTransaction $recurringTransaction)
    {
        $this->authorize('update', $recurringTransaction);
        
        $recurringTransaction->update(['status' => 'paused']);
        
        return response()->json($recurringTransaction);
    }

    /**
     * Resume a paused recurring transaction.
     */
    public function resume(RecurringTransaction $recurringTransaction)
    {
        $this->authorize('update', $recurringTransaction);
        
        $recurringTransaction->update(['status' => 'active']);
        
        return response()->json($recurringTransaction);
    }
}

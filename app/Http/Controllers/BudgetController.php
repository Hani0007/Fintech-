<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetRequest;
use App\Http\Requests\UpdateBudgetRequest;
use App\Models\Budget;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $month = request()->query('month', now()->month);
        $year = request()->query('year', now()->year);

        $budgets = Budget::where('user_id', Auth::id())
            ->where('month', $month)
            ->where('year', $year)
            ->with(['category'])
            ->get()
            ->map(function ($budget) {
                $budget->used = $budget->used;
                $budget->remaining = $budget->remaining;
                $budget->percentage_used = $budget->percentage_used;
                return $budget;
            });
        
        return response()->json($budgets);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBudgetRequest $request)
    {
        $budget = Budget::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'category_id' => $request->category_id,
                'month' => $request->month,
                'year' => $request->year,
            ],
            [
                'amount' => $request->amount,
            ]
        );

        // If budget already exists, update the amount
        if (!$budget->wasRecentlyCreated) {
            $budget->update(['amount' => $request->amount]);
        }

        $budget->load('category');
        $budget->used = $budget->used;
        $budget->remaining = $budget->remaining;
        $budget->percentage_used = $budget->percentage_used;
        
        return response()->json($budget, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Budget $budget)
    {
        $this->authorize('view', $budget);
        
        $budget->load('category');
        $budget->used = $budget->used;
        $budget->remaining = $budget->remaining;
        $budget->percentage_used = $budget->percentage_used;
        
        return response()->json($budget);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBudgetRequest $request, Budget $budget)
    {
        $this->authorize('update', $budget);
        
        $budget->update($request->validated());
        
        $budget->load('category');
        $budget->used = $budget->used;
        $budget->remaining = $budget->remaining;
        $budget->percentage_used = $budget->percentage_used;
        
        return response()->json($budget);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Budget $budget)
    {
        $this->authorize('delete', $budget);
        
        $budget->delete();
        
        return response()->json(null, 204);
    }
}

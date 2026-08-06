<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSavingsGoalRequest;
use App\Http\Requests\UpdateSavingsGoalRequest;
use App\Models\SavingsGoal;
use Illuminate\Support\Facades\Auth;

class SavingsGoalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $goals = SavingsGoal::where('user_id', Auth::id())
            ->orderBy('status')
            ->orderBy('deadline')
            ->get()
            ->map(function ($goal) {
                $goal->remaining_amount = $goal->remaining_amount;
                $goal->progress_percentage = $goal->progress_percentage;
                return $goal;
            });
        
        return response()->json($goals);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSavingsGoalRequest $request)
    {
        $goal = SavingsGoal::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'target_amount' => $request->target_amount,
            'saved_amount' => $request->saved_amount ?? 0,
            'deadline' => $request->deadline,
            'status' => $request->status ?? 'active',
        ]);

        $goal->remaining_amount = $goal->remaining_amount;
        $goal->progress_percentage = $goal->progress_percentage;
        
        return response()->json($goal, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(SavingsGoal $savingsGoal)
    {
        $this->authorize('view', $savingsGoal);
        
        $savingsGoal->remaining_amount = $savingsGoal->remaining_amount;
        $savingsGoal->progress_percentage = $savingsGoal->progress_percentage;
        
        return response()->json($savingsGoal);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSavingsGoalRequest $request, SavingsGoal $savingsGoal)
    {
        $this->authorize('update', $savingsGoal);
        
        $savingsGoal->update($request->validated());
        
        // Auto-mark as completed if saved amount reaches target
        if ($savingsGoal->saved_amount >= $savingsGoal->target_amount && $savingsGoal->status !== 'completed') {
            $savingsGoal->update(['status' => 'completed']);
        }
        
        $savingsGoal->remaining_amount = $savingsGoal->remaining_amount;
        $savingsGoal->progress_percentage = $savingsGoal->progress_percentage;
        
        return response()->json($savingsGoal);
    }

    /**
     * Add funds to savings goal.
     */
    public function addFunds(SavingsGoal $savingsGoal, Request $request)
    {
        $this->authorize('update', $savingsGoal);
        
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $savingsGoal->increment('saved_amount', $request->amount);
        
        // Auto-mark as completed if saved amount reaches target
        if ($savingsGoal->saved_amount >= $savingsGoal->target_amount && $savingsGoal->status !== 'completed') {
            $savingsGoal->update(['status' => 'completed']);
        }
        
        $savingsGoal->remaining_amount = $savingsGoal->remaining_amount;
        $savingsGoal->progress_percentage = $savingsGoal->progress_percentage;
        
        return response()->json($savingsGoal);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SavingsGoal $savingsGoal)
    {
        $this->authorize('delete', $savingsGoal);
        
        $savingsGoal->delete();
        
        return response()->json(null, 204);
    }
}

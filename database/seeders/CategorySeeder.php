<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        
        if (!$user) {
            $this->command->warn('No users found. Please create a user first.');
            return;
        }

        $incomeCategories = [
            ['name' => 'Salary', 'color' => '#10B981', 'icon' => '💰'],
            ['name' => 'Freelancing', 'color' => '#3B82F6', 'icon' => '💻'],
            ['name' => 'Business', 'color' => '#8B5CF6', 'icon' => '🏢'],
            ['name' => 'Investment', 'color' => '#F59E0B', 'icon' => '📈'],
            ['name' => 'Other', 'color' => '#6B7280', 'icon' => '💵'],
        ];

        $expenseCategories = [
            ['name' => 'Food', 'color' => '#EF4444', 'icon' => '🍔'],
            ['name' => 'Shopping', 'color' => '#EC4899', 'icon' => '🛍️'],
            ['name' => 'Bills', 'color' => '#F97316', 'icon' => '📄'],
            ['name' => 'Education', 'color' => '#14B8A6', 'icon' => '📚'],
            ['name' => 'Fuel', 'color' => '#84CC16', 'icon' => '⛽'],
            ['name' => 'Entertainment', 'color' => '#A855F7', 'icon' => '🎬'],
            ['name' => 'Healthcare', 'color' => '#06B6D4', 'icon' => '🏥'],
            ['name' => 'Travel', 'color' => '#6366F1', 'icon' => '✈️'],
        ];

        foreach ($incomeCategories as $category) {
            Category::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $category['name'],
                    'type' => 'income',
                ],
                $category
            );
        }

        foreach ($expenseCategories as $category) {
            Category::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $category['name'],
                    'type' => 'expense',
                ],
                $category
            );
        }

        $this->command->info('Default categories seeded successfully.');
    }
}

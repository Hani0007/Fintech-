<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    account_type: 'bank',
    balance: '0',
    currency: 'USD',
    account_number: '',
});

const submit = () => {
    form.post('/accounts', {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Create Account" />
    
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Account</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Add a new account to track your finances</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Account Name
                        </label>
                        <input
                            type="text"
                            id="name"
                            v-model="form.name"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        />
                        <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                    </div>

                    <div>
                        <label for="account_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Account Number
                        </label>
                        <input
                            type="text"
                            id="account_number"
                            v-model="form.account_number"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        />
                        <div v-if="form.errors.account_number" class="mt-1 text-sm text-red-600">{{ form.errors.account_number }}</div>
                    </div>

                    <div>
                        <label for="account_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Account Type
                        </label>
                        <select
                            id="account_type"
                            v-model="form.account_type"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="easypaisa">Easypaisa</option>
                            <option value="jazzcash">Jazzcash</option>
                            <option value="credit_card">Credit Card</option>
                        </select>
                        <div v-if="form.errors.account_type" class="mt-1 text-sm text-red-600">{{ form.errors.account_type }}</div>
                    </div>

                    <div>
                        <label for="balance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Initial Balance
                        </label>
                        <input
                            type="number"
                            id="balance"
                            v-model="form.balance"
                            step="0.01"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        />
                        <div v-if="form.errors.balance" class="mt-1 text-sm text-red-600">{{ form.errors.balance }}</div>
                    </div>

                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Currency
                        </label>
                        <select
                            id="currency"
                            v-model="form.currency"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="USD">USD - US Dollar</option>
                            <option value="PKR">PKR - Pakistani Rupee</option>
                            <option value="AED">AED - UAE Dirham</option>
                            <option value="EUR">EUR - Euro</option>
                        </select>
                        <div v-if="form.errors.currency" class="mt-1 text-sm text-red-600">{{ form.errors.currency }}</div>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a
                            href="/dashboard"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </a>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Creating...' : 'Create Account' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

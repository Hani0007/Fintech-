<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Account {
    id: number;
    name: string;
    balance: number;
}

interface Category {
    id: number;
    name: string;
    color: string;
    icon: string;
}

const props = defineProps<{
    accounts: Account[];
    categories: Category[];
    defaultType?: 'income' | 'expense';
}>();

const form = useForm({
    account_id: '',
    category_id: '',
    title: '',
    type: props.defaultType || 'expense',
    amount: '',
    transaction_date: new Date().toISOString().split('T')[0],
    receipt: null as File | null,
    notes: '',
});

const submit = () => {
    const formData = new FormData();
    formData.append('account_id', form.account_id);
    formData.append('category_id', form.category_id);
    formData.append('title', form.title);
    formData.append('type', form.type);
    formData.append('amount', form.amount);
    formData.append('transaction_date', form.transaction_date);
    if (form.receipt) {
        formData.append('receipt', form.receipt);
    }
    formData.append('notes', form.notes);

    form.post('/transactions', {
        data: formData,
        onSuccess: () => {
            form.reset();
        },
    });
};

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.receipt = target.files[0];
    }
};
</script>

<template>
    <Head :title="defaultType === 'income' ? 'Create Income' : 'Create Transaction'" />
    
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ defaultType === 'income' ? 'Create Income' : 'Create Transaction' }}
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    {{ defaultType === 'income' ? 'Add a new income' : 'Add a new income or expense' }}
                </p>
            </div>

            <!-- Form -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Transaction Type -->
                    <div v-if="!defaultType">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Transaction Type
                        </label>
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input
                                    type="radio"
                                    v-model="form.type"
                                    value="income"
                                    class="mr-2"
                                />
                                <span class="text-gray-700 dark:text-gray-300">Income</span>
                            </label>
                            <label class="flex items-center">
                                <input
                                    type="radio"
                                    v-model="form.type"
                                    value="expense"
                                    class="mr-2"
                                />
                                <span class="text-gray-700 dark:text-gray-300">Expense</span>
                            </label>
                        </div>
                    </div>
                    <input type="hidden" v-model="form.type" v-else />

                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Title
                        </label>
                        <input
                            type="text"
                            id="title"
                            v-model="form.title"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        />
                        <div v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</div>
                    </div>

                    <!-- Amount -->
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Amount
                        </label>
                        <input
                            type="number"
                            id="amount"
                            v-model="form.amount"
                            step="0.01"
                            min="0"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        />
                        <div v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</div>
                    </div>

                    <!-- Account -->
                    <div>
                        <label for="account" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Account
                        </label>
                        <select
                            id="account"
                            v-model="form.account_id"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">Select an account</option>
                            <option v-for="account in accounts" :key="account.id" :value="account.id">
                                {{ account.name }} ({{ parseFloat(account.balance).toFixed(2) }})
                            </option>
                        </select>
                        <div v-if="form.errors.account_id" class="mt-1 text-sm text-red-600">{{ form.errors.account_id }}</div>
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Category
                        </label>
                        <select
                            id="category"
                            v-model="form.category_id"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">Select a category</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.icon }} {{ category.name }}
                            </option>
                        </select>
                        <div v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">{{ form.errors.category_id }}</div>
                    </div>

                    <!-- Transaction Date -->
                    <div>
                        <label for="transaction_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Date
                        </label>
                        <input
                            type="date"
                            id="transaction_date"
                            v-model="form.transaction_date"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        />
                        <div v-if="form.errors.transaction_date" class="mt-1 text-sm text-red-600">{{ form.errors.transaction_date }}</div>
                    </div>

                    <!-- Receipt -->
                    <div>
                        <label for="receipt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Receipt (optional)
                        </label>
                        <input
                            type="file"
                            id="receipt"
                            @change="handleFileChange"
                            accept="image/*,.pdf"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        />
                        <div v-if="form.receipt" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Selected: {{ form.receipt.name }}
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Notes (optional)
                        </label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        ></textarea>
                    </div>

                    <!-- Submit Button -->
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
                            {{ form.processing ? 'Creating...' : 'Create Transaction' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

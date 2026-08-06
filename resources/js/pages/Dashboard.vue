<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { Pie, Bar, Line, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    ArcElement,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
} from 'chart.js';

ChartJS.register(
    ArcElement,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement
);

interface DashboardStats {
    total_balance: number;
    total_income: number;
    total_expenses: number;
    remaining_budget: number;
    total_budget: number;
    net_savings: number;
    currency: string;
    category_breakdown: Record<string, { amount: number; count: number; type: string }>;
    income_by_category: Record<string, number>;
    expense_by_category: Record<string, number>;
    monthly_trend: Array<{ month: string; income: number; expense: number }>;
}

interface RecentTransaction {
    id: number;
    title: string;
    type: 'income' | 'expense';
    amount: number;
    transaction_date: string;
    category: {
        name: string;
        color: string;
        icon: string;
    };
    account: {
        name: string;
        currency: string;
    };
}

const stats = ref<DashboardStats>({
    total_balance: 0,
    total_income: 0,
    total_expenses: 0,
    remaining_budget: 0,
    total_budget: 0,
    net_savings: 0,
    currency: 'USD',
    category_breakdown: {},
    income_by_category: {},
    expense_by_category: {},
    monthly_trend: [],
});

const recentTransactions = ref<RecentTransaction[]>([]);
const loading = ref(true);

onMounted(async () => {
    await Promise.all([fetchStats(), fetchRecentTransactions()]);
    loading.value = false;
});

const fetchStats = async () => {
    try {
        const response = await axios.get('/api/dashboard/stats');
        stats.value = response.data;
    } catch (error) {
        console.error('Error fetching stats:', error);
    }
};

const fetchRecentTransactions = async () => {
    try {
        const response = await axios.get('/transactions?limit=5');
        recentTransactions.value = response.data;
    } catch (error) {
        console.error('Error fetching transactions:', error);
    }
};

const formatCurrency = (amount: number, currency?: string) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency || stats.value.currency || 'USD',
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

// Chart data computed properties
const incomeExpenseChartData = computed(() => {
    if (!stats.value) return { labels: [], datasets: [] };
    try {
        return {
            labels: ['Income', 'Expenses'],
            datasets: [
                {
                    data: [stats.value.total_income || 0, stats.value.total_expenses || 0],
                    backgroundColor: ['#10B981', '#EF4444'],
                    borderWidth: 0,
                },
            ],
        };
    } catch (e) {
        return { labels: [], datasets: [] };
    }
});

const expenseByCategoryChartData = computed(() => {
    try {
        if (!stats.value?.expense_by_category) return { labels: [], datasets: [] };
        const categories = Object.keys(stats.value.expense_by_category);
        if (categories.length === 0) return { labels: [], datasets: [] };
        
        return {
            labels: categories,
            datasets: [
                {
                    label: 'Expenses by Category',
                    data: Object.values(stats.value.expense_by_category),
                    backgroundColor: [
                        '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6', '#EC4899', '#6366F1', '#14B8A6',
                    ],
                    borderWidth: 0,
                },
            ],
        };
    } catch (e) {
        return { labels: [], datasets: [] };
    }
});

const incomeByCategoryChartData = computed(() => {
    try {
        if (!stats.value?.income_by_category) return { labels: [], datasets: [] };
        const categories = Object.keys(stats.value.income_by_category);
        if (categories.length === 0) return { labels: [], datasets: [] };
        
        return {
            labels: categories,
            datasets: [
                {
                    label: 'Income by Category',
                    data: Object.values(stats.value.income_by_category),
                    backgroundColor: [
                        '#10B981', '#3B82F6', '#8B5CF6', '#EC4899', '#F59E0B', '#6366F1', '#14B8A6', '#EF4444',
                    ],
                    borderWidth: 0,
                },
            ],
        };
    } catch (e) {
        return { labels: [], datasets: [] };
    }
});

const monthlyTrendChartData = computed(() => {
    try {
        if (!stats.value?.monthly_trend || stats.value.monthly_trend.length === 0) {
            return { labels: [], datasets: [] };
        }
        
        return {
            labels: stats.value.monthly_trend.map((t) => t.month),
            datasets: [
                {
                    label: 'Income',
                    data: stats.value.monthly_trend.map((t) => t.income),
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                },
                {
                    label: 'Expenses',
                    data: stats.value.monthly_trend.map((t) => t.expense),
                    borderColor: '#EF4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true,
                },
            ],
        };
    } catch (e) {
        return { labels: [], datasets: [] };
    }
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: {
                color: '#9CA3AF',
                font: {
                    size: 12,
                },
            },
        },
    },
};

const barChartOptions = {
    ...chartOptions,
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                color: '#9CA3AF',
            },
            grid: {
                color: 'rgba(75, 85, 99, 0.3)',
            },
        },
        x: {
            ticks: {
                color: '#9CA3AF',
            },
            grid: {
                display: false,
            },
        },
    },
};

const lineChartOptions = {
    ...chartOptions,
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                color: '#9CA3AF',
            },
            grid: {
                color: 'rgba(75, 85, 99, 0.3)',
            },
        },
        x: {
            ticks: {
                color: '#9CA3AF',
            },
            grid: {
                display: false,
            },
        },
    },
};
</script>

<template>
    <Head title="Dashboard" />
    
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Overview of your finances</p>
            </div>

            <!-- Stats Cards -->
            <div v-if="!loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Balance -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Balance</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                {{ formatCurrency(stats.total_balance) }}
                            </p>
                        </div>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Income -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Income</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">
                                {{ formatCurrency(stats.total_income) }}
                            </p>
                        </div>
                        <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Expenses -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Expenses</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">
                                {{ formatCurrency(stats.total_expenses) }}
                            </p>
                        </div>
                        <div class="p-3 bg-red-100 dark:bg-red-900 rounded-full">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Net Savings -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Net Savings</p>
                            <p class="text-2xl font-bold mt-1" :class="stats.net_savings >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                {{ stats.net_savings >= 0 ? '+' : '' }}{{ formatCurrency(stats.net_savings) }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ stats.total_budget > 0 ? `Budget: ${formatCurrency(stats.remaining_budget)} remaining` : 'No budget set' }}
                            </p>
                        </div>
                        <div class="p-3 rounded-full" :class="stats.net_savings >= 0 ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900'">
                            <svg class="w-6 h-6" :class="stats.net_savings >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div v-if="!loading" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Income vs Expenses Pie Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Income vs Expenses</h3>
                    <div class="h-64">
                        <Pie
                            v-if="incomeExpenseChartData?.labels?.length > 0"
                            :data="incomeExpenseChartData"
                            :options="chartOptions"
                        />
                        <div v-else class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
                            No data available
                        </div>
                    </div>
                </div>

                <!-- Expenses by Category Bar Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Expenses by Category</h3>
                    <div class="h-64">
                        <Bar
                            v-if="expenseByCategoryChartData?.labels?.length > 0"
                            :data="expenseByCategoryChartData"
                            :options="barChartOptions"
                        />
                        <div v-else class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
                            No expense data available
                        </div>
                    </div>
                </div>

                <!-- Income by Category Doughnut Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Income by Category</h3>
                    <div class="h-64">
                        <Doughnut
                            v-if="incomeByCategoryChartData?.labels?.length > 0"
                            :data="incomeByCategoryChartData"
                            :options="chartOptions"
                        />
                        <div v-else class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
                            No income data available
                        </div>
                    </div>
                </div>

                <!-- Monthly Trend Line Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">6-Month Trend</h3>
                    <div class="h-64">
                        <Line
                            v-if="monthlyTrendChartData?.labels?.length > 0"
                            :data="monthlyTrendChartData"
                            :options="lineChartOptions"
                        />
                        <div v-else class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
                            No trend data available
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Transactions</h2>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div v-if="recentTransactions.length === 0" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                        No transactions yet
                    </div>
                    <div v-for="transaction in recentTransactions" :key="transaction.id" class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <span class="text-2xl">{{ transaction.category.icon }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ transaction.title }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ transaction.category.name }} • {{ transaction.account.name }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p :class="transaction.type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" class="text-sm font-semibold">
                                {{ transaction.type === 'income' ? '+' : '-' }}{{ formatCurrency(transaction.amount, transaction.account.currency) }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(transaction.transaction_date) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex items-center justify-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>
        </div>
    </div>
</template>

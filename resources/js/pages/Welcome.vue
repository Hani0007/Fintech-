<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    auth?: {
        user?: {
            name: string;
            email: string;
        };
    };
}>();

const isAuthenticated = computed(() => props.auth?.user);

if (isAuthenticated.value) {
    router.visit('/dashboard');
}
</script>

<template>
    <Head title="Fintech Expense Tracker">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    
    <div class="min-h-screen bg-gradient-to-br from-blue-600 to-purple-700 flex items-center justify-center p-4">
        <div class="max-w-4xl w-full">
            <div class="text-center mb-12">
                <h1 class="text-5xl font-bold text-white mb-4">Fintech Expense Tracker</h1>
                <p class="text-xl text-blue-100">Manage your finances with ease</p>
            </div>

            <div class="grid md:grid-cols-3 gap-6 mb-12">
                <div class="bg-white/10 backdrop-blur-lg rounded-xl p-6 text-white">
                    <div class="text-4xl mb-4">💰</div>
                    <h3 class="text-xl font-semibold mb-2">Track Expenses</h3>
                    <p class="text-blue-100">Monitor your spending with detailed categories and accounts</p>
                </div>
                <div class="bg-white/10 backdrop-blur-lg rounded-xl p-6 text-white">
                    <div class="text-4xl mb-4">📊</div>
                    <h3 class="text-xl font-semibold mb-2">Budget Management</h3>
                    <p class="text-blue-100">Set budgets and track your spending against them</p>
                </div>
                <div class="bg-white/10 backdrop-blur-lg rounded-xl p-6 text-white">
                    <div class="text-4xl mb-4">🎯</div>
                    <h3 class="text-xl font-semibold mb-2">Savings Goals</h3>
                    <p class="text-blue-100">Set savings goals and track your progress</p>
                </div>
            </div>

            <div v-if="!isAuthenticated" class="text-center">
                <a
                    href="/login"
                    class="inline-block bg-white text-blue-600 font-semibold px-8 py-3 rounded-lg hover:bg-blue-50 transition-colors mr-4"
                >
                    Login
                </a>
                <a
                    href="/register"
                    class="inline-block bg-blue-500 text-white font-semibold px-8 py-3 rounded-lg hover:bg-blue-400 transition-colors"
                >
                    Register
                </a>
            </div>
        </div>
    </div>
</template>

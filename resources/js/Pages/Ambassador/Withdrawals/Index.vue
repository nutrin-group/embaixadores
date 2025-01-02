<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Área de Saques
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Alertas e Informações -->
                <div v-if="$page.props.flash.error" class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ $page.props.flash.error }}</p>
                        </div>
                    </div>
                </div>

                <!-- Adicionar mensagem de sucesso -->
                <div v-if="$page.props.flash.status" class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ $page.props.flash.status }}</p>
                        </div>
                    </div>
                </div>

                <!-- Cards de Informação -->
                <DashboardCard>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Saldo Disponível -->
                        <div class="bg-gradient-to-r from-pink-500 to-pink-600 rounded-lg p-6 text-white shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium mb-2">Saldo Disponível</h3>
                                    <p class="text-3xl font-bold">{{ formatCurrency(availableBalance) }}</p>
                                    <p class="text-sm opacity-75 mt-1">
                                        {{ lastWithdrawalDate
                                            ? `Último saque em ${formatDate(lastWithdrawalDate)}`
                                            : 'Nenhum saque realizado'
                                        }}
                                    </p>
                                </div>
                                <div class="text-pink-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Saldo Retido -->
                        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg p-6 text-white shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium mb-2">Saldo Retido</h3>
                                    <p class="text-3xl font-bold">{{ formatCurrency(lockedBalance) }}</p>
                                    <p class="text-sm opacity-75 mt-1">Liberado após entrega do pedido</p>
                                </div>
                                <div class="text-yellow-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </DashboardCard>
                <DashboardCard>
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Informações importantes sobre saques:
                                </h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        <li>Limite de 1 saque por mês</li>
                                        <li>Pagamentos realizados no dia 20 para saques solicitados até dia 10</li>
                                        <li>Saques acima de R$ 2.259,20 requerem emissão de Nota Fiscal</li>
                                        <li>10% do valor da comissão fica retido até a entrega do pedido (proteção
                                            contra
                                            cancelamentos e reembolsos)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </DashboardCard>

                <!-- Formulário de Saque -->
                <DashboardCard>
                    <template #title>
                        <div class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-pink-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Solicitar Saque</span>
                        </div>
                    </template>

                    <form @submit.prevent="submitWithdrawal" class="max-w-md">
                        <div class="space-y-4">
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700">
                                    Valor do Saque
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">R$</span>
                                    </div>
                                    <input id="amount" type="number" min="50" :max="availableBalance" step="0.01"
                                        v-model="form.amount"
                                        class="pl-12 pr-24 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500"
                                        :class="{ 'border-red-300': form.errors.amount }" required />
                                    <div class="absolute inset-y-0 right-0 flex items-center">
                                        <PrimaryButton type="submit" :disabled="!canWithdraw || form.processing"
                                            class="h-full rounded-l-none">
                                            <span v-if="form.processing">Processando...</span>
                                            <span v-else>Solicitar Saque</span>
                                        </PrimaryButton>
                                    </div>
                                </div>
                                <div class="mt-1 flex items-center space-x-1 text-sm text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Valor mínimo: R$ 50,00</span>
                                </div>
                                <InputError :message="form.errors.amount" class="mt-2" />
                            </div>
                        </div>
                    </form>
                </DashboardCard>

                <!-- Histórico de Saques -->
                <DashboardCard>
                    <template #title>
                        <div class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-pink-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <span>Histórico de Saques</span>
                        </div>
                    </template>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Data
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Valor
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template v-if="withdrawals && withdrawals.length > 0">
                                    <tr v-for="withdrawal in withdrawals" :key="withdrawal.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(withdrawal.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatCurrency(withdrawal.amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="['px-2 py-1 text-xs font-medium rounded-full inline-flex items-center', getStatusColor(withdrawal.status)]">
                                                <span class="w-2 h-2 rounded-full mr-1"
                                                    :class="getStatusDotColor(withdrawal.status)"></span>
                                                {{ formatStatus(withdrawal.status) }}
                                            </span>
                                        </td>
                                    </tr>
                                </template>
                                <tr v-else>
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                        Nenhum saque realizado
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </DashboardCard>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DashboardCard from '@/Components/DashboardCard.vue';
import { useToast } from 'vue-toastification';

const props = defineProps({
    paymentKey: Object,
    availableBalance: {
        type: Number,
        default: 0
    },
    lastWithdrawalDate: {
        type: String,
        default: null
    },
    withdrawals: {
        type: Array,
        default: () => []
    },
    lockedBalance: {
        type: Number,
        default: 0
    }
});

const form = useForm({
    amount: ''
});

const page = usePage();

const toast = useToast();

const submitWithdrawal = () => {
    // Validação do valor mínimo
    if (form.amount < 50) {
        toast.error('O valor mínimo para saque é R$ 50,00');
        return;
    }

    // Validação do saldo disponível
    if (form.amount > props.availableBalance) {
        toast.error('Saldo insuficiente para realizar o saque');
        return;
    }

    form.post(route('ambassador.withdrawals.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            toast.success('Solicitação de saque realizada com sucesso!');
        },
        onError: (errors) => {
            if (errors.amount) {
                toast.error(errors.amount);
            } else if (errors.error) {
                toast.error(errors.error);
            } else {
                toast.error('Erro ao solicitar saque. Tente novamente.');
            }
        }
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('pt-BR');
};

const formatStatus = (status) => {
    const statusMap = {
        'pending': 'Pendente',
        'completed': 'Aprovado',
        'rejected': 'Rejeitado'
    };
    return statusMap[status] || status;
};

const getStatusColor = (status) => {
    const colorMap = {
        'pending': 'text-yellow-600 bg-yellow-50',
        'completed': 'text-green-600 bg-green-50',
        'rejected': 'text-red-600 bg-red-50'
    };
    return colorMap[status] || 'text-gray-600 bg-gray-50';
};

const getStatusDotColor = (status) => {
    const colorMap = {
        'pending': 'bg-yellow-400',
        'completed': 'bg-green-400',
        'rejected': 'bg-red-400'
    };
    return colorMap[status] || 'bg-gray-400';
};

const copyToClipboard = async (text) => {
    try {
        await navigator.clipboard.writeText(text);
        toast.success('URL copiada com sucesso!');
    } catch (err) {
        console.error('Erro ao copiar texto: ', err);
        toast.error('Erro ao copiar URL');
    }
};

// Adicionar computed para controle do botão
const canWithdraw = computed(() => {
    return props.availableBalance >= 50 && !form.processing;
});
</script>

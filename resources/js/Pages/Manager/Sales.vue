<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    sales: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    periods: {
        type: Object,
        required: true
    },
    status_options: {
        type: Object,
        required: true
    },
    auth: {
        type: Object,
        required: true
    }
});

// Remover verificação de tipo de usuário pois o middleware já cuida disso
const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'all');
const selectedPeriod = ref(props.filters.period || 'all');
const selectedAmbassador = ref(props.filters.ambassador_id || '');

// Computed property para garantir que sempre temos um array, mesmo que vazio
const salesData = computed(() => {
    const data = props.sales?.data || [];
    console.log('Sales Data:', data); // Debug
    return data;
});

// Função para atualizar os filtros
const updateFilters = debounce(() => {
    router.get(route('manager.sales'), {
        search: search.value,
        status: selectedStatus.value,
        period: selectedPeriod.value,
        ambassador_id: selectedAmbassador.value
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}, 300);

// Observar mudanças nos filtros
watch([search, selectedStatus, selectedPeriod, selectedAmbassador], () => {
    updateFilters();
});

// Formatar moeda
const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value || 0);
};

// Formatar data
const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('pt-BR');
};

// Debug para verificar os dados de auth
console.log('Auth User:', props.auth?.user);
console.log('Coins:', props.coins);
console.log('Ranking:', props.ranking);

// Função para obter a classe CSS do status
const getStatusClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'paid': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    }
    return classes[status] || ''
}

// Função para obter o label do status
const getStatusLabel = (status) => {
    return props.status_options[status] || status
}

// Computed property para totais
const totals = computed(() => ({
    count: props.sales.total_count || 0,
    amount: props.sales.total_amount || 0,
    commission: props.sales.total_commission || 0
}))
</script>

<template>
    <AuthenticatedLayout :auth="auth">

        <Head title="Vendas da Equipe" />

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-lg text-gray-800 leading-tight">
                    Vendas da Equipe
                </h2>
                <div class="flex items-center space-x-4">
                    <div v-if="ranking && ranking !== '-'" class="flex items-center">
                        <span class="text-yellow-500 mr-1">⭐</span>
                        <span class="text-sm text-gray-600">#{{ ranking }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-yellow-500 mr-1">🪙</span>
                        <span class="text-sm text-gray-600">{{ formatCurrency(coins).replace('R$', '') }}</span>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-4 px-3 sm:py-6 sm:px-6">
            <div class="max-w-7xl mx-auto space-y-4">
                <!-- Filtros -->
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <!-- Filtro de Período -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Período
                            </label>
                            <select v-model="selectedPeriod"
                                class="w-full rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500">
                                <option v-for="(label, value) in periods" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <!-- Busca -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Buscar
                            </label>
                            <input type="text" v-model="search"
                                class="w-full rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                                placeholder="Buscar por pedido ou cliente...">
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Status
                            </label>
                            <select v-model="selectedStatus"
                                class="w-full rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500">
                                <option v-for="(label, value) in status_options" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <!-- Embaixadora -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Embaixadora
                            </label>
                            <select v-model="selectedAmbassador"
                                class="w-full rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500">
                                <option value="">Todas</option>
                                <option v-for="ambassador in sales.ambassadors" :key="ambassador.id"
                                    :value="ambassador.id">
                                    {{ ambassador.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Resumo -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <p class="text-sm text-gray-500">Total de Vendas</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">
                            {{ totals.count }}
                        </p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <p class="text-sm text-gray-500">Valor Total</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">
                            {{ formatCurrency(totals.amount) }}
                        </p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <p class="text-sm text-gray-500">Comissões Totais</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">
                            {{ formatCurrency(totals.commission) }}
                        </p>
                    </div>
                </div>

                <!-- Lista de Vendas -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Pedido
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cliente
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Embaixadora
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Valor
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Comissão
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Data
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="!salesData.length" class="hover:bg-gray-50">
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        Nenhuma venda encontrada
                                    </td>
                                </tr>
                                <tr v-for="sale in salesData" :key="sale?.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #{{ sale?.shopifyId || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ sale?.fullName || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ sale?.user?.name || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatCurrency(sale?.amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatCurrency(sale?.commisionAmount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span v-if="sale?.status"
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="getStatusClass(sale.status)">
                                            {{ getStatusLabel(sale.status) }}
                                        </span>
                                        <span v-else>-</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(sale?.created_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <div v-if="sales.links" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between">
                                <Link v-if="sales.links?.prev" :href="sales.links.prev"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Anterior
                                </Link>
                                <Link v-if="sales.links?.next" :href="sales.links.next"
                                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Próxima
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Adicione seus estilos aqui */
</style>

<template>

    <Head title="Minhas Vendas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-lg text-gray-800 leading-tight">
                Minhas Vendas
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Cards de Resumo -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gradient-to-br from-pink-500 to-pink-600 rounded-lg p-6 text-white shadow-lg">
                        <p class="text-sm font-medium opacity-75">Total em Vendas</p>
                        <p class="text-2xl font-bold mt-2">{{ formatCurrency(totalSales) }}</p>
                    </div>

                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-6 text-white shadow-lg">
                        <p class="text-sm font-medium opacity-75">Saldo Disponível</p>
                        <p class="text-2xl font-bold mt-2">{{ formatCurrency(totalCommissions) }}</p>
                    </div>

                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg p-6 text-white shadow-lg">
                        <p class="text-sm font-medium opacity-75">Comissão Média por Venda</p>
                        <p class="text-2xl font-bold mt-2">{{ formatCurrency(averageCommission) }}</p>
                    </div>

                    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg p-6 text-white shadow-lg">
                        <p class="text-sm font-medium opacity-75">Comissão Bloqueada</p>
                        <p class="text-2xl font-bold mt-2">{{ formatCurrency(totalLockedCommissions) }}</p>
                    </div>
                </div>

                <!-- Filtros e Tabela -->
                <DashboardCard>
                    <div class="space-y-6">
                        <!-- Filtros melhorados -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Data Inicial</label>
                                <input type="date" v-model="filter.startDate"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500">
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Data Final</label>
                                <input type="date" v-model="filter.endDate"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500">
                            </div>
                        </div>

                        <!-- Tabela com design melhorado -->
                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Data
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cliente
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Valor da Venda
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Comissão
                                        </th>

                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="sale in filteredSales" :key="sale.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(sale.date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ maskName(sale.client_name) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatCurrency(sale.amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatCurrency(sale.commission) }}
                                        </td>

                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </DashboardCard>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, computed, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import DashboardCard from '@/Components/DashboardCard.vue'

// Ajuste nas props para receber os dados paginados e filtros
const props = defineProps({
    sales: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        required: true
    }
})

// Estado local inicializado com os filtros da URL
const filter = ref({
    startDate: props.filters.startDate || new Date().toISOString().split('T')[0],
    endDate: props.filters.endDate || new Date().toISOString().split('T')[0],
    status: props.filters.status || 'all'
})

// Watch para atualizar a URL quando os filtros mudarem
watch(filter, (newFilters) => {
    router.get(route('ambassador.sales.index'), newFilters, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}, { deep: true })

// Computed properties ajustadas
const filteredSales = computed(() => {
    return props.sales.data.map(sale => ({
        id: sale.id,
        date: sale.created_at,
        client_name: sale.customer_name,
        amount: Number(sale.amount) || 0,
        commission: sale.available_amount,
        locked_commission: sale.locked_amount,
    }))
})

const totalSales = computed(() => {
    return filteredSales.value.reduce((sum, sale) => sum + sale.amount, 0)
})

const totalCommissions = computed(() => {
    return filteredSales.value.reduce((sum, sale) => sum + sale.commission, 0)
})

const averageCommission = computed(() => {
    const count = filteredSales.value.length
    if (!count) return 0
    return totalCommissions.value / count
})

const totalLockedCommissions = computed(() => {
    return filteredSales.value.reduce((sum, sale) => sum + sale.locked_commission, 0)
})

// Funções auxiliares
const maskName = (name) => {
    if (!name) return '';

    const parts = name.split(' ')
    if (parts.length === 1) {
        return `${parts[0].charAt(0)}${'*'.repeat(parts[0].length - 2)}${parts[0].charAt(parts[0].length - 1)}`
    }
    return `${parts[0].charAt(0)}${'*'.repeat(parts[0].length - 1)} ${parts[parts.length - 1].charAt(0)}${'*'.repeat(parts[parts.length - 1].length - 1)}`
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('pt-BR')
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value)
}

const translateStatus = (status) => {
    const translations = {
        'pending': 'Pendente',
        'paid': 'Pago',
        'cancelled': 'Cancelado'
    }
    return translations[status] || status
}
</script>

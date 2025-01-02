<template>
    <AuthenticatedLayout>

        <Head title="Dashboard do Embaixador" />

        <template #header>
            <h2 class="font-semibold text-lg text-gray-800 leading-tight">
                Dashboard do Embaixador
            </h2>
        </template>

        <div class="py-6 px-4 sm:px-6">
            <div class="max-w-7xl mx-auto space-y-4">
                <!-- Card Comissão Liberada -->
                <div class="bg-gradient-to-r from-pink-500 to-pink-600 rounded-xl p-5">
                    <div class="flex justify-between items-center">
                        <h3 class="text-base text-white">Comissão Liberada</h3>
                        <span class="text-sm text-white" v-if="comissaoDiff !== null">
                            {{ comissaoDiff > 0 ? '+' : '' }}{{ comissaoDiff }}%
                        </span>
                    </div>
                    <p class="text-2xl font-bold text-white mt-2">
                        {{ formatCurrency(comissaoLiberada) }}
                    </p>
                </div>

                <!-- Card para Comissão Retida -->
                <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl p-5">
                    <div class="flex justify-between items-center">
                        <h3 class="text-base text-white">Comissão Retida</h3>
                    </div>
                    <p class="text-2xl font-bold text-white mt-2">
                        {{ formatCurrency(comissaoRetida) }}
                    </p>
                    <p class="text-sm text-white opacity-75 mt-1">Liberada após entrega do pedido</p>
                </div>

                <!-- Card Comissão Total -->
                <div class="bg-white rounded-xl p-5 shadow-sm">
                    <h3 class="text-base text-gray-700">Vendas Totais</h3>
                    <p class="text-2xl font-bold text-gray-900 mt-2">
                        {{ new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(comissaoTotal)
                        }}
                    </p>
                </div>

                <!-- Meta Mensal -->
                <div class="bg-white rounded-xl p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg text-gray-700 font-semibold">Meta Mensal</h3>
                        </div>
                        <div class="relative w-16 h-16">
                            <svg class="transform -rotate-90 w-16 h-16">
                                <circle cx="32" cy="32" r="28" stroke-width="8" stroke="#f3f4f6" fill="none" />
                                <circle cx="32" cy="32" r="28" stroke-width="8" stroke="#ec4899" fill="none"
                                    :stroke-dasharray="175.93"
                                    :stroke-dashoffset="175.93 - (175.93 * percentualMeta / 100)" />
                            </svg>
                            <div
                                class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-sm font-medium text-pink-500">
                                {{ Math.round(percentualMeta) }}%
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Total de Vendas -->
                <div class="bg-white rounded-xl p-5 shadow-sm">
                    <h3 class="text-base text-gray-700">Total de Vendas</h3>
                    <p class="text-2xl font-bold text-gray-900 mt-2">{{ totalVendas }}</p>
                </div>

                <!-- Card Produto Mais Vendido -->
                <div v-if="produtoMaisVendido" class="bg-white rounded-xl p-5 shadow-sm">
                    <h3 class="text-base text-gray-700">Produto Mais Vendido</h3>
                    <div class="flex items-center mt-2">
                        <img :src="produtoMaisVendido.image_url" alt="Produto Mais Vendido"
                            class="w-16 h-16 rounded-full mr-4">
                        <div>
                            <p class="text-lg font-bold text-gray-900">{{ produtoMaisVendido.title }}</p>
                            <p class="text-sm text-gray-600">Quantidade vendida: {{ produtoMaisVendido.quantity }}</p>
                        </div>
                    </div>
                </div>

                <!-- Gráfico -->
                <div class="bg-white rounded-xl p-5 shadow-sm">
                    <h3 class="text-base text-gray-700 mb-4">Histórico de Vendas</h3>
                    <div class="h-[300px]">
                        <SalesChart :vendas-por-dia="vendasPorDia" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue'
import SalesChart from '../../Components/SalesChart.vue'
import { Head } from '@inertiajs/vue3'
import { onMounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    comissaoLiberada: {
        type: Number,
        default: 0
    },
    comissaoRetida: {
        type: Number,
        default: 0
    },
    comissaoTotal: {
        type: Number,
        default: 0
    },
    comissaoDiff: {
        type: Number,
        default: null
    },
    totalVendas: Number,
    percentualMeta: Number,
    vendasPorDia: Array,
    produtoMaisVendido: Object,
    embaixador: String
})

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value)
}

let intervalId

onMounted(() => {
    // Configura o intervalo para atualizar a cada 60 segundos
    intervalId = setInterval(() => {
        router.reload({ preserveScroll: true, preserveState: true })
    }, 60000)
})

onBeforeUnmount(() => {
    // Limpa o intervalo quando o componente é desmontado
    if (intervalId) {
        clearInterval(intervalId)
    }
})
</script>

<style scoped>
@media (max-width: 768px) {
    .py-6 {
        padding-bottom: 4rem;
    }
}
</style>

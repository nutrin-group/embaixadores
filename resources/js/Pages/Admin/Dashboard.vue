<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-lg text-gray-800 leading-tight">
                Dashboard Administrativo
            </h2>
        </template>

        <div class="py-6 px-4 sm:px-6">
            <div class="max-w-7xl mx-auto space-y-4 mb-20">
                <!-- Card Receita Total -->
                <div class="bg-pink-500 rounded-xl p-5">
                    <div class="flex justify-between items-center">
                        <h3 class="text-base text-white">Receita Total</h3>
                    </div>
                    <p class="text-2xl font-bold text-white mt-2">
                        {{ new Intl.NumberFormat('pt-BR', {
                            style: 'currency', currency: 'BRL'
                        }).format(statistics.sales.amount) }}
                    </p>
                    <p class="text-sm text-white/80 mt-1">Este mês</p>
                </div>

                <!-- Card Embaixadores -->
                <div class="bg-white rounded-xl p-5 shadow-sm">
                    <div class="flex justify-between items-center">
                        <h3 class="text-base text-gray-700">Embaixadores</h3>
                    </div>
                    <p class="text-2xl font-bold text-gray-900 mt-2">{{ statistics.ambassadors.total }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ statistics.ambassadors.active }} ativos</p>
                </div>

                <!-- Card Comissões -->
                <div class="bg-white rounded-xl p-5 shadow-sm">
                    <div class="flex justify-between items-center">
                        <h3 class="text-base text-gray-700">Comissões</h3>
                    </div>
                    <p class="text-2xl font-bold text-gray-900 mt-2">
                        {{ new Intl.NumberFormat('pt-BR', {
                            style: 'currency', currency: 'BRL'
                        }).format(statistics.commissions.total) }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">{{ statistics.commissions.pending }} pagamentos pendentes</p>
                </div>

                <!-- Gráficos -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white rounded-xl p-5 shadow-sm">
                        <h3 class="text-base text-gray-700 mb-4">Vendas Totais</h3>
                        <div class="h-[300px]">
                            <LineChart :data="formattedSalesData" :options="chartOptions" />
                        </div>
                    </div>

                    <div class="bg-white rounded-xl p-5 shadow-sm">
                        <h3 class="text-base text-gray-700 mb-4">Comissões Geradas</h3>
                        <div class="h-[300px]">
                            <BarChart :data="formattedCommissionsData" :options="chartOptions" />
                        </div>
                    </div>
                </div>

                <!-- Tabela Top Embaixadores -->
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="p-5 flex justify-between items-center border-b">
                        <h3 class="text-base text-gray-700">Top Embaixadores</h3>
                        <Link :href="route('admin.dashboard')" class="text-sm text-pink-500 hover:text-pink-600">
                        Ver todos
                        </Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Embaixador
                                    </th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Vendas
                                    </th>

                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="ambassador in topAmbassadors" :key="ambassador.id">
                                    <td class="px-5 py-4 text-sm text-gray-900">
                                        {{ ambassador.name }}
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-900">
                                        {{ new Intl.NumberFormat('pt-BR', {
                                            style: 'currency', currency: 'BRL'
                                        }).format(ambassador.total_amount) }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <span :class="{
                                            'px-2 py-1 text-xs rounded-full': true,
                                            'bg-green-100 text-green-800': ambassador.status === 'active',
                                            'bg-gray-100 text-gray-800': ambassador.status !== 'active'
                                        }">
                                            {{ ambassador.status === 'active' ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tabela de Produtos Mais Vendidos -->
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="p-5 flex justify-between items-center border-b">
                        <h3 class="text-base text-gray-700">Produtos Mais Vendidos</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Produto
                                    </th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Quantidade Vendida
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="product in topProducts" :key="product.id">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center">
                                            <img v-if="product.image_url" :src="product.image_url" :alt="product.name"
                                                class="w-10 h-10 rounded-lg object-cover mr-3">
                                            <div>
                                                <span class="text-sm font-medium text-gray-900">{{ product.name
                                                    }}</span>

                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="text-sm text-gray-900">{{ product.total_quantity }}</span>
                                        <p class="text-xs text-gray-500">unidades</p>
                                    </td>

                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'
import { Line as LineChart, Bar as BarChart } from 'vue-chartjs'
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend
} from 'chart.js'
import { onMounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend
)

const props = defineProps({
    statistics: {
        type: Object,
        required: true
    },
    topAmbassadors: {
        type: Array,
        required: true
    },
    salesData: {
        type: Array,
        required: true
    },
    commissionsData: {
        type: Array,
        required: true
    },
    recentSales: {
        type: Array,
        required: true
    },
    recentCommissions: {
        type: Array,
        required: true
    },
    topProducts: {
        type: Array,
        required: true
    }
})

// Preparar dados para os gráficos
const formattedSalesData = {
    labels: props.salesData.map(item => new Date(item.date).toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' })),
    datasets: [{
        label: 'Vendas',
        data: props.salesData.map(item => item.total_amount),
        borderColor: 'rgb(236, 72, 153)',
        backgroundColor: 'rgba(236, 72, 153, 0.1)',
        tension: 0.4,
        fill: true,
        pointRadius: 0,
        borderWidth: 2,
    }]
}

const formattedCommissionsData = {
    labels: props.commissionsData.map(item => new Date(item.date).toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' })),
    datasets: [{
        label: 'Comissões',
        data: props.commissionsData.map(item => item.total_amount),
        backgroundColor: 'rgba(236, 72, 153, 0.8)',
        borderRadius: 4,
    }]
}

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            mode: 'index',
            intersect: false,
            callbacks: {
                label: function (context) {
                    let label = context.dataset.label || '';
                    if (label) {
                        label += ': ';
                    }
                    if (context.parsed.y !== null) {
                        label += new Intl.NumberFormat('pt-BR', {
                            style: 'currency',
                            currency: 'BRL'
                        }).format(context.parsed.y);
                    }
                    return label;
                }
            }
        }
    },
    scales: {
        x: {
            grid: {
                display: false
            },
            ticks: {
                maxTicksLimit: 7,
                font: {
                    size: 11
                }
            }
        },
        y: {
            beginAtZero: true,
            ticks: {
                callback: (value) => new Intl.NumberFormat('pt-BR', {
                    style: 'currency',
                    currency: 'BRL',
                    maximumFractionDigits: 0
                }).format(value),
                font: {
                    size: 11
                }
            },
            grid: {
                color: 'rgba(0, 0, 0, 0.05)'
            }
        }
    },
    interaction: {
        intersect: false,
        mode: 'index'
    }
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

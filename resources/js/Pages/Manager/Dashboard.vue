<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-lg text-gray-800 leading-tight">
                Dashboard do Gerente
            </h2>
        </template>

        <div class="py-6 px-4 sm:px-6">
            <div class="max-w-7xl mx-auto space-y-6">
                <!-- Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Card Receita Total -->
                    <div class="md:col-span-1 bg-pink-500 rounded-xl p-5">
                        <div class="flex justify-between items-center">
                            <h3 class="text-base text-white">Receita Total</h3>
                            <span class="text-sm bg-pink-400 text-white px-2 py-1 rounded-full">
                                {{ new Intl.NumberFormat('pt-BR', { style: 'percent' }).format(
                                    (revenue - props.statistics.last_month_revenue) / props.statistics.last_month_revenue
                                ) }}
                            </span>
                        </div>
                        <p class="text-2xl font-bold text-white mt-2">
                            {{ new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(revenue) }}
                        </p>
                        <p class="text-sm text-white/80 mt-1">Este mês</p>
                    </div>

                    <!-- Card Total de Embaixadores -->
                    <div class="bg-white rounded-xl p-5 shadow-sm">
                        <div class="flex justify-between items-center">
                            <h3 class="text-base text-gray-700">Total de Embaixadores</h3>
                            <span class="text-sm px-2 py-1 rounded-full"
                                :class="ambassadorsDiff > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                {{ ambassadorsDiff > 0 ? '+' : '' }}{{ ambassadorsDiff }}
                            </span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ ambassadors }}</p>
                    </div>

                    <!-- Card Vendas -->
                    <div class="bg-white rounded-xl p-5 shadow-sm">
                        <div class="flex justify-between items-center">
                            <h3 class="text-base text-gray-700">Total de Vendas</h3>
                            <span class="text-sm rounded-full p-2"
                                :class="props.salesDiff > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                {{ props.salesDiff > 0 ? '+' : '' }}{{ props.salesDiff }}%
                            </span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ totalSales }}</p>
                        <p class="text-sm text-gray-500 mt-1">Este mês</p>
                    </div>
                </div>

                <!-- Card URL de Referência -->
                <div class="bg-white rounded-xl p-5 shadow-sm">
                    <h3 class="text-base text-gray-700 mb-2">Link para Cadastro</h3>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly
                            :value="`https://embaixadores.gummy.com.br/onboarding?ref=${$page.props.auth.user.id}`"
                            class="flex-1 p-2 bg-gray-50 rounded border border-gray-200 text-sm" />
                        <button
                            @click="copyToClipboard(`https://embaixadores.gummy.com.br/onboarding?ref=${$page.props.auth.user.id}`)"
                            class="px-4 py-2 bg-pink-500 text-white rounded-lg text-sm hover:bg-pink-600 transition-colors">
                            Copiar
                        </button>
                    </div>
                </div>

                <!-- Seção da Tabela -->
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="p-5 flex justify-between items-center border-b">
                        <h3 class="text-base text-gray-700">Vendas por Embaixador</h3>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500">Total de vendas:</span>
                            <span class="text-sm font-medium">{{ totalSales }}</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Embaixador
                                    </th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Vendas
                                    </th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Valor Total
                                    </th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="ambassador in ambassadorSales" :key="ambassador.id">
                                    <td class="px-5 py-4 text-sm text-gray-900">
                                        {{ ambassador.name }}
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-900">
                                        {{ ambassador.total_sales }}
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-900">
                                        {{ new Intl.NumberFormat('pt-BR', {
                                            style: 'currency',
                                            currency: 'BRL'
                                        }).format(ambassador.total_amount) }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <span :class="{
                                            'px-2 py-1 text-xs rounded-full': true,
                                            'bg-green-100 text-green-800': ambassador.status === 'Ativo',
                                            'bg-gray-100 text-gray-800': ambassador.status !== 'Ativo'
                                        }">
                                            {{ ambassador.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Seção do Gráfico -->
                <div class="bg-white rounded-xl p-5 shadow-sm">
                    <h3 class="text-base text-gray-700 mb-4">Vendas por Dia</h3>
                    <div class="relative w-full" style="height: 300px;" v-if="chartData">
                        <LineChart :data="chartData" :options="chartOptions" />
                    </div>
                </div>


            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LineChart from '@/Components/Charts/LineChart.vue'
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useToast } from 'vue-toastification'
import { router } from '@inertiajs/vue3'

const toast = useToast()

// Props com valores padrão
const props = defineProps({
    auth: {
        type: Object,
        required: true
    },
    sales: {
        type: Array,
        default: () => []
    },
    statistics: {
        type: Object,
        default: () => ({
            total_revenue: 0,
            total_sales: 0,
            last_month_revenue: 0
        })
    },
    ambassadorSales: {
        type: Array,
        default: () => []
    },
    ambassadorsDiff: {
        type: Number,
        default: 0
    },
    currentMonthAmbassadors: {
        type: Number,
        default: 0
    },
    salesDiff: {
        type: Number,
        default: 0
    }
})

// Função para copiar para a área de transferência
const copyToClipboard = async (text) => {
    try {
        await navigator.clipboard.writeText(text)
        toast.success('URL copiada com sucesso!')
    } catch (err) {
        console.error('Erro ao copiar texto: ', err)
        toast.error('Erro ao copiar URL')
    }
}

// Dados para o gráfico
const generateDailyData = () => {
    const data = []
    const dates = []
    const salesByDate = {}

    // Verifica se props.sales existe e é um array
    if (props.sales && Array.isArray(props.sales)) {
        props.sales.forEach(sale => {
            if (sale && sale.created_at) {
                const date = new Date(sale.created_at).toLocaleDateString('pt-BR', {
                    day: '2-digit',
                    month: '2-digit'
                })

                if (!salesByDate[date]) {
                    salesByDate[date] = 0
                }
                salesByDate[date] += Number(sale.amount || 0)
            }
        })
    }

    // Preenche os últimos 30 dias
    for (let i = 30; i >= 0; i--) {
        const date = new Date()
        date.setDate(date.getDate() - i)
        const formattedDate = date.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit'
        })

        dates.push(formattedDate)
        data.push(salesByDate[formattedDate] || 0)
    }

    return { dates, data }
}

// Inicializa os dados do gráfico após a montagem do componente
const chartData = ref(null)
onMounted(() => {
    const { dates, data } = generateDailyData()
    chartData.value = {
        labels: dates,
        datasets: [{
            label: 'Vendas',
            data: data,
            borderColor: 'rgb(236, 72, 153)',
            backgroundColor: 'rgba(236, 72, 153, 0.1)',
            tension: 0.4,
            fill: true,
            borderWidth: 2,
            pointRadius: 0,
            pointHoverRadius: 4,
        }]
    }
})

// Computed properties
const revenue = computed(() => Number(props.statistics?.total_revenue || 0))
const totalSales = computed(() => Number(props.statistics?.total_sales || 0))
const ambassadors = computed(() => props.currentMonthAmbassadors)

// Configurações do gráfico
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            titleFont: {
                size: 13
            },
            bodyFont: {
                size: 13
            },
            callbacks: {
                title: function (tooltipItems) {
                    return tooltipItems[0].label;
                },
                label: function (context) {
                    return `R$ ${context.parsed.y.toFixed(2)}`;
                }
            },
            displayColors: false
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            border: {
                display: false
            },
            grid: {
                color: 'rgba(0, 0, 0, 0.05)'
            },
            ticks: {
                callback: value => `R$ ${value}`,
                font: {
                    size: 11
                },
                color: '#64748b',
                padding: 8
            }
        },
        x: {
            grid: {
                display: false
            },
            ticks: {
                font: {
                    size: 11
                },
                color: '#64748b',
                maxTicksLimit: 7,
                padding: 8
            },
            border: {
                display: false
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

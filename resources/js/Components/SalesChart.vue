<template>
    <Line :data="chartData" :options="chartOptions" />
</template>

<script>
import { Line } from 'vue-chartjs'
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js'

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
)

export default {
    name: 'SalesChart',
    components: { Line },
    props: {
        vendasPorDia: {
            type: Array,
            required: true
        }
    },
    computed: {
        chartData() {
            return {
                labels: this.vendasPorDia.map(item => item.data),
                datasets: [
                    {
                        label: 'Vendas por Dia',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 2,
                        data: this.vendasPorDia.map(item => item.vendas),
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(255, 99, 132, 1)',
                        pointHoverBorderWidth: 2,
                    }
                ]
            }
        },
        chartOptions() {
            return {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 14,
                                family: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif"
                            },
                            padding: 20
                        }
                    },
                    title: {
                        display: true,
                        text: 'Histórico de Vendas',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: {
                            top: 10,
                            bottom: 30
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: 13
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 15,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        }
    }
}
</script>

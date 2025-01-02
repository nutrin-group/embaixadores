<template>
    <div class="relative w-full h-full">
        <canvas ref="chartRef" class="w-full h-full"></canvas>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    data: {
        type: Object,
        required: true
    },
    options: {
        type: Object,
        default: () => ({})
    }
});

const chartRef = ref(null);
let chart = null;

const createChart = () => {
    const ctx = chartRef.value.getContext('2d');

    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleFont: {
                    size: 13
                },
                bodyFont: {
                    size: 13
                },
                padding: 12,
                displayColors: false,
                callbacks: {
                    title: function (tooltipItems) {
                        return tooltipItems[0].label;
                    },
                    label: function (context) {
                        return `R$ ${context.parsed.y.toFixed(2)}`;
                    }
                }
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
        },
        elements: {
            line: {
                tension: 0.4
            },
            point: {
                radius: 0,
                hoverRadius: 6
            }
        },
        layout: {
            padding: {
                top: 20,
                right: 20,
                bottom: 20,
                left: 20
            }
        }
    };

    // Mescla as opções padrão com as opções personalizadas
    const finalOptions = {
        ...defaultOptions,
        ...props.options
    };

    chart = new Chart(ctx, {
        type: 'line',
        data: props.data,
        options: finalOptions
    });
};

onMounted(() => {
    createChart();
});

watch(() => props.data, () => {
    if (chart) {
        chart.destroy();
    }
    createChart();
}, { deep: true });

// Adiciona um listener para redimensionamento
window.addEventListener('resize', () => {
    if (chart) {
        chart.resize();
    }
});

// Limpa o listener quando o componente é destruído
onUnmounted(() => {
    if (chart) {
        chart.destroy();
    }
    window.removeEventListener('resize', () => {
        if (chart) {
            chart.resize();
        }
    });
});
</script>

<style scoped>
.relative {
    position: relative;
    width: 100%;
    height: 100%;
}

canvas {
    display: block;
    box-sizing: border-box;
}
</style>

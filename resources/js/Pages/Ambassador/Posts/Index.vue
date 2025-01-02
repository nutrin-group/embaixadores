<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-lg text-gray-800 leading-tight">
                Minhas Postagens
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Formulário de Nova Postagem -->
                <DashboardCard title="Nova Postagem">
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Tipo de Post</label>
                                <div class="grid grid-cols-3 gap-3">
                                    <label v-for="type in ['reels', 'feed', 'stories']" :key="type"
                                        class="relative flex items-center justify-center p-4 rounded-lg border cursor-pointer hover:bg-gray-50 transition-colors"
                                        :class="form.type === type ? 'border-pink-500 bg-pink-50' : 'border-gray-200'">
                                        <input type="radio" v-model="form.type" :value="type" class="sr-only" />
                                        <span class="capitalize text-sm font-medium">{{ type }}</span>
                                        <span v-if="form.type === type"
                                            class="absolute top-1.5 right-1.5 flex items-center justify-center w-4 h-4 text-pink-500">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Link do Post</label>
                                <input type="url" v-model="form.post_url"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Data do Post</label>
                                <input type="text" ref="datepicker" v-model="form.post_date"
                                    placeholder="Selecione a data"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500">
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="bg-pink-500 text-white px-4 py-2 rounded-md hover:bg-pink-600">
                                Registrar Post
                            </button>
                        </div>
                    </form>
                </DashboardCard>

                <!-- Calendário -->
                <DashboardCard>
                    <div class="p-4 md:p-6">
                        <!-- Navegação do Mês -->
                        <div class="flex justify-between items-center mb-4">
                            <button @click="previousMonth" class="text-gray-600 hover:text-gray-900 text-sm">
                                ← Mês Anterior
                            </button>
                            <h3 class="text-lg font-semibold capitalize">{{ currentMonthName }} {{ currentYear }}</h3>
                            <button @click="nextMonth" class="text-gray-600 hover:text-gray-900 text-sm">
                                Próximo Mês →
                            </button>
                        </div>

                        <!-- Calendário Responsivo -->
                        <div class="grid grid-cols-7 gap-1 md:gap-4">
                            <!-- Cabeçalho dos dias -->
                            <div v-for="day in ['D', 'S', 'T', 'Q', 'Q', 'S', 'S']" :key="day"
                                class="text-center text-xs md:text-sm font-semibold text-gray-600 py-2">
                                {{ day }}
                            </div>

                            <!-- Dias do calendário -->
                            <div v-for="date in calendarDays" :key="date.date" @click="toggleDateDetails(date)" :class="[
                                'min-h-[50px] md:min-h-[140px] p-1 md:p-3 border rounded-lg relative',
                                date.isCurrentMonth ? 'bg-white' : 'bg-gray-50',
                                date.hasPost ? 'border-pink-400' : 'border-gray-200',
                                selectedDate?.date === date.date ? 'ring-2 ring-pink-500' : ''
                            ]">
                                <!-- Número do dia -->
                                <div :class="{ 'text-gray-400': !date.isCurrentMonth }"
                                    class="text-xs md:text-sm text-right mb-1 md:mb-3">
                                    {{ date.day }}
                                </div>

                                <!-- Indicadores Mobile -->
                                <div class="md:hidden flex justify-center">
                                    <div v-if="date.posts?.length" class="flex gap-0.5">
                                        <div v-for="type in getUniquePostTypes(date.posts)" :key="type"
                                            class="w-1.5 h-1.5 rounded-full" :class="{
                                                'bg-pink-400': type === 'reels',
                                                'bg-pink-600': type === 'feed',
                                                'bg-purple-500': type === 'stories'
                                            }">
                                        </div>
                                    </div>
                                </div>

                                <!-- Posts Desktop -->
                                <div class="hidden md:block">
                                    <div v-if="date.posts?.length" class="space-y-2">
                                        <div v-for="post in date.posts" :key="post.id" @click="openPost(post.post_url)"
                                            class="text-xs p-2 rounded cursor-pointer hover:opacity-75 transition-opacity"
                                            :class="{
                                                'bg-pink-100 text-pink-800': post.type === 'reels',
                                                'bg-pink-200 text-pink-900': post.type === 'feed',
                                                'bg-purple-100 text-purple-800': post.type === 'stories'
                                            }">
                                            <div class="flex items-center justify-between">
                                                <span class="capitalize">{{ post.type }}</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detalhes Mobile -->
                        <div v-if="selectedDate" class="md:hidden mt-4 p-4 border rounded-lg bg-white">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="font-semibold">{{ formatSelectedDate(selectedDate.date) }}</h4>
                                <button @click="selectedDate = null" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div v-if="selectedDate.posts?.length" class="space-y-2">
                                <div v-for="post in selectedDate.posts" :key="post.id" @click="openPost(post.post_url)"
                                    class="p-3 rounded cursor-pointer hover:opacity-75 transition-opacity" :class="{
                                        'bg-pink-100 text-pink-800': post.type === 'reels',
                                        'bg-pink-200 text-pink-900': post.type === 'feed',
                                        'bg-purple-100 text-purple-800': post.type === 'stories'
                                    }">
                                    <div class="flex items-center justify-between">
                                        <span class="capitalize">{{ post.type }}</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        <p><strong>Título:</strong> {{ post.title || 'Sem título' }}</p>
                                        <p><strong>Descrição:</strong> {{ post.description || 'Sem descrição' }}</p>
                                    </div>
                                    <div class="flex justify-end mt-2 space-x-2">
                                        <button @click.stop="editPost(post)"
                                            class="text-blue-500 hover:text-blue-700 text-xs">Editar</button>
                                        <button @click.stop="deletePost(post)"
                                            class="text-red-500 hover:text-red-700 text-xs">Excluir</button>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-gray-500 text-center py-2">
                                Nenhuma postagem neste dia
                            </div>
                        </div>
                    </div>
                </DashboardCard>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useForm } from '@inertiajs/vue3'
import flatpickr from 'flatpickr'
import 'flatpickr/dist/flatpickr.min.css'
import { Portuguese } from 'flatpickr/dist/l10n/pt.js'
import DashboardCard from '@/Components/DashboardCard.vue'

export default {
    components: {
        AuthenticatedLayout,
        DashboardCard
    },

    props: {
        posts: Array,
        currentMonth: {
            type: [Number, String],
            default: () => new Date().getMonth() + 1
        },
        currentYear: {
            type: [Number, String],
            default: () => new Date().getFullYear()
        },
        debug: Object
    },

    setup(props) {
        const form = useForm({
            type: 'reels',
            post_url: '',
            post_date: '',
        })

        const month = ref(Number(props.currentMonth))
        const year = ref(Number(props.currentYear))

        const currentMonthName = computed(() => {
            return new Date(year.value, month.value - 1)
                .toLocaleString('pt-BR', { month: 'long' })
        })

        const calendarDays = computed(() => {

            const firstDay = new Date(year.value, month.value - 1, 1)
            const lastDay = new Date(year.value, month.value, 0)
            const days = []

            // Calcular dias do mês anterior
            const firstDayOfWeek = firstDay.getDay()
            const prevMonthLastDay = new Date(year.value, month.value - 1, 0).getDate()

            // Preencher dias do mês anterior
            for (let i = 0; i < firstDayOfWeek; i++) {
                const dayNumber = prevMonthLastDay - firstDayOfWeek + i + 1
                const prevMonth = month.value - 1
                const prevYear = prevMonth === 0 ? year.value - 1 : year.value
                const adjustedMonth = prevMonth === 0 ? 12 : prevMonth

                const date = `${prevYear}-${String(adjustedMonth).padStart(2, '0')}-${String(dayNumber).padStart(2, '0')}`

                days.push({
                    date,
                    day: dayNumber,
                    isCurrentMonth: false,
                    hasPost: false,
                    posts: []
                })
            }

            // Dias do mês atual
            const daysInMonth = lastDay.getDate()
            for (let i = 1; i <= daysInMonth; i++) {
                const currentDate = `${year.value}-${String(month.value).padStart(2, '0')}-${String(i).padStart(2, '0')}`
                const postsForDay = props.posts.filter(post => {

                    return post.post_date === currentDate
                })


                days.push({
                    date: currentDate,
                    day: i,
                    isCurrentMonth: true,
                    hasPost: postsForDay.length > 0,
                    posts: postsForDay
                })
            }

            // Preencher dias do próximo mês
            const totalDaysNeeded = 42 // 6 semanas x 7 dias
            const remainingDays = totalDaysNeeded - days.length

            for (let i = 1; i <= remainingDays; i++) {
                const nextMonth = month.value + 1
                const nextYear = nextMonth === 13 ? year.value + 1 : year.value
                const adjustedMonth = nextMonth === 13 ? 1 : nextMonth

                const date = `${nextYear}-${String(adjustedMonth).padStart(2, '0')}-${String(i).padStart(2, '0')}`

                days.push({
                    date,
                    day: i,
                    isCurrentMonth: false,
                    hasPost: false,
                    posts: []
                })
            }


            return days
        })

        const submitForm = () => {
            console.log('Data sendo enviada:', form.post_date)
            form.post(route('ambassador.posts.store'), {
                onSuccess: () => {
                    form.reset()
                    // Resetar o input do flatpickr
                    if (datepicker.value._flatpickr) {
                        datepicker.value._flatpickr.clear()
                    }
                }
            })
        }

        const previousMonth = () => {
            if (month.value === 1) {
                month.value = 12
                year.value--
            } else {
                month.value--
            }

            refreshData()
        }

        const nextMonth = () => {
            if (month.value === 12) {
                month.value = 1
                year.value++
            } else {
                month.value++
            }

            refreshData()
        }

        const refreshData = () => {
            window.location.href = route('ambassador.posts.index', {
                month: month.value,
                year: year.value
            })
        }

        const openPost = (url) => {

            window.open(url, '_blank')
        }

        const selectedDate = ref(null)

        const toggleDateDetails = (date) => {
            selectedDate.value = selectedDate.value?.date === date.date ? null : date
        }

        const getUniquePostTypes = (posts) => {
            return [...new Set(posts.map(post => post.type))]
        }

        const formatSelectedDate = (dateString) => {
            const date = new Date(dateString)
            return date.toLocaleDateString('pt-BR', {
                weekday: 'long',
                day: 'numeric',
                month: 'long'
            })
        }

        const editPost = (post) => {

            // Lógica para editar o post
        }

        const deletePost = (post) => {

            // Lógica para excluir o post
        }

        const datepicker = ref(null)

        onMounted(() => {
            flatpickr(datepicker.value, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                locale: Portuguese,
                maxDate: 'today',
                onChange: (selectedDates, dateStr) => {
                    form.post_date = dateStr;
                }
            })
        })

        return {
            form,
            currentMonthName,
            calendarDays,
            submitForm,
            previousMonth,
            nextMonth,
            openPost,
            selectedDate,
            toggleDateDetails,
            getUniquePostTypes,
            formatSelectedDate,
            editPost,
            deletePost,
            datepicker
        }
    }
}
</script>

<style scoped>
@media (max-width: 768px) {
    .py-6 {
        padding-bottom: 4rem;
    }
}

/* Adicione estas classes para garantir largura mínima em telas maiores */
@media (min-width: 768px) {
    .grid-cols-7>* {
        min-width: 150px;
        /* Largura mínima para cada célula */
    }
}

/* Estilo personalizado para o Flatpickr */
.flatpickr-calendar {
    background-color: #f9f9f9;
    border: 1px solid #ccc;
    border-radius: 8px;
}

.flatpickr-day {
    color: #333;
    border-radius: 4px;
}

.flatpickr-day:hover,
.flatpickr-day:focus {
    background-color: #e0e0e0;
}

.flatpickr-day.selected,
.flatpickr-day.startRange,
.flatpickr-day.endRange {
    background-color: #ff69b4;
    /* Cor rosa */
    color: #fff;
}

.flatpickr-month {
    color: #ff69b4;
    /* Cor rosa */
}
</style>

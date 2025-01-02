<script setup>
import { ref, computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import CoinBalance from '@/Components/CoinBalance.vue'
import Modal from '@/Components/Modal.vue'
import CoinIcon from '@/Components/Icons/GummyCoin.vue'

const props = defineProps({
    rewards: Array,
    user_coins: [String, Number]
})

const rewards = ref(props.rewards)
const userCoins = ref(props.user_coins)
const loading = ref(false)
const showConfirmModal = ref(false)
const selectedReward = ref(null)
const currentFilter = ref('Todos')
const sortBy = ref('name')

// Computed para recompensas filtradas e ordenadas
const sortedRewards = computed(() => {
    let filtered = [...rewards.value]

    // Aplicar filtros
    if (currentFilter.value === 'Disponíveis') {
        filtered = filtered.filter(reward => reward.quantity > 0)
    }

    // Aplicar ordenação
    switch (sortBy.value) {
        case 'coins-asc':
            filtered.sort((a, b) => a.coins - b.coins)
            break
        case 'coins-desc':
            filtered.sort((a, b) => b.coins - a.coins)
            break
        default:
            filtered.sort((a, b) => a.name.localeCompare(b.name))
    }

    return filtered
})

const confirmRedemption = (reward) => {
    selectedReward.value = reward
    showConfirmModal.value = true
}

const redeemReward = async () => {
    try {
        loading.value = true
        const response = await axios.post(`/ambassador/rewards/${selectedReward.value.id}/redeem`)
        userCoins.value = response.data.user_coins
        showConfirmModal.value = false
        // Atualizar quantidade disponível da recompensa
        const index = rewards.value.findIndex(r => r.id === selectedReward.value.id)
        if (index !== -1) {
            rewards.value[index].quantity--
        }
    } catch (error) {
        console.error('Erro ao resgatar recompensa:', error)
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <AuthenticatedLayout title="Recompensas">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Recompensas
                </h2>
                <CoinBalance :balance="userCoins" />
            </div>
        </template>

        <div class="py-6 lg:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Informações importantes - Melhorado para mobile -->
                <div class="mb-6 rounded-lg bg-white p-4 shadow sm:mb-8 sm:p-6">
                    <h3 class="mb-3 text-base font-medium text-gray-900 sm:text-lg">
                        Informações Importantes
                    </h3>
                    <div class="space-y-2.5 text-xs sm:text-sm text-gray-600">
                        <div class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0 text-pink-500" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p>Você pode resgatar apenas <span class="font-medium text-gray-900">uma unidade de cada
                                    prêmio</span>.</p>
                        </div>
                        <div class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0 text-pink-500" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p>O prazo para envio de produtos físicos é de até <span
                                    class="font-medium text-gray-900">45 dias
                                    úteis</span>.</p>
                        </div>
                        <div class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0 text-pink-500" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <p>Você receberá um e-mail com as instruções após o resgate.</p>
                        </div>
                    </div>
                </div>

                <!-- Filtros e Ordenação - Melhorado para mobile -->
                <div class="mb-4 sm:mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <!-- Filtros em scroll horizontal no mobile -->
                    <div class="flex -mx-4 px-4 sm:mx-0 sm:px-0 overflow-x-auto pb-2 sm:pb-0">
                        <div class="flex gap-2 sm:gap-4">
                            <button v-for="filter in ['Todos', 'Disponíveis', 'Resgatados']" :key="filter"
                                @click="currentFilter = filter" :class="[
                                    'whitespace-nowrap rounded-full px-3 py-1 text-xs sm:text-sm font-medium transition',
                                    currentFilter === filter
                                        ? 'bg-pink-600 text-white'
                                        : 'bg-white text-gray-600 hover:bg-gray-50'
                                ]">
                                {{ filter }}
                            </button>
                        </div>
                    </div>

                    <!-- Select de ordenação -->
                    <select v-model="sortBy"
                        class="rounded-lg border-gray-300 text-xs sm:text-sm focus:border-pink-500 focus:ring-pink-500">
                        <option value="name">Nome</option>
                        <option value="coins-asc">Menor Preço</option>
                        <option value="coins-desc">Maior Preço</option>
                    </select>
                </div>

                <!-- Grid de Recompensas -->
                <div class="grid grid-cols-2 gap-3 sm:gap-6 lg:grid-cols-3">
                    <div v-for="reward in sortedRewards" :key="reward.id"
                        class="group overflow-hidden rounded-lg bg-white shadow transition hover:shadow-md">
                        <div class="relative pt-[100%]">
                            <img :src="reward.image" :alt="reward.name"
                                class="absolute top-0 left-0 h-full w-full object-contain p-3 sm:p-4 bg-pink-50 rounded-lg" />
                            <!-- Badge de quantidade - Ajustado para mobile -->
                            <div
                                class="absolute top-2 right-2 rounded-full bg-white px-1.5 py-0.5 sm:px-2 sm:py-1 text-[10px] sm:text-xs font-medium text-gray-600 shadow-sm">
                                {{ reward.quantity }} disponíveis
                            </div>
                        </div>

                        <div class="p-3 sm:p-4">
                            <h3 class="text-sm sm:text-base font-medium text-gray-900">{{ reward.name }}</h3>
                            <p v-if="reward.description" class="mt-1 text-xs sm:text-sm text-gray-500 line-clamp-2">
                                {{ reward.description }}
                            </p>
                            <div class="mt-2 flex items-center gap-1">
                                <CoinIcon class="h-4 w-4 sm:h-5 sm:w-5 text-yellow-500" />
                                <span class="text-sm sm:text-base font-medium text-gray-900">{{ reward.coins }}
                                    GCs</span>
                            </div>

                            <button @click="confirmRedemption(reward)"
                                class="mt-3 w-full rounded-md bg-pink-600 px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium text-white transition hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="userCoins < reward.coins">
                                <span v-if="userCoins >= reward.coins">Resgatar Recompensa</span>
                                <span v-else class="flex items-center justify-center gap-1">
                                    <svg class="h-3 w-3 sm:h-4 sm:w-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m0 0v2m0-2h2m-2 0H8" />
                                    </svg>
                                    <span>GCs Insuficientes</span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Estado vazio - Ajustado para mobile -->
                <div v-if="rewards.length === 0" class="text-center py-8 sm:py-12">
                    <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma recompensa disponível</h3>
                    <p class="mt-1 text-xs sm:text-sm text-gray-500">Novas recompensas serão adicionadas em breve.</p>
                </div>
            </div>
        </div>

        <!-- Modal ajustado para mobile -->
        <Modal :show="showConfirmModal" @close="showConfirmModal = false">
            <div class="p-4 sm:p-6">
                <div class="flex items-center gap-3 sm:gap-4">
                    <img v-if="selectedReward?.image" :src="selectedReward.image" :alt="selectedReward?.name"
                        class="h-14 w-14 sm:h-16 sm:w-16 rounded-lg object-contain bg-pink-50 p-2" />
                    <div>
                        <h3 class="text-base sm:text-lg font-medium text-gray-900">
                            Confirmar Resgate
                        </h3>
                        <p class="mt-1 text-xs sm:text-sm text-gray-500">
                            Você está prestes a resgatar:
                        </p>
                    </div>
                </div>

                <div class="mt-4 rounded-lg bg-gray-50 p-3 sm:p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm sm:text-base font-medium text-gray-900">{{ selectedReward?.name }}</span>
                        <span class="text-xs sm:text-sm text-gray-500">{{ selectedReward?.coins }} GCs</span>
                    </div>
                </div>

                <div class="mt-4 text-xs sm:text-sm text-gray-500">
                    <p>Ao confirmar:</p>
                    <ul class="mt-2 list-disc pl-5 space-y-1">
                        <li>As GummyCoins serão deduzidas do seu saldo</li>
                        <li>Você receberá um e-mail com as instruções</li>
                        <li>Para produtos físicos, o prazo de envio é de até 45 dias úteis</li>
                    </ul>
                </div>

                <div class="mt-6 flex justify-end gap-2 sm:gap-3">
                    <button type="button"
                        class="rounded-md border border-gray-300 bg-white px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2"
                        @click="showConfirmModal = false">
                        Cancelar
                    </button>
                    <button type="button"
                        class="rounded-md bg-pink-600 px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium text-white hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2"
                        @click="redeemReward">
                        Confirmar Resgate
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Ajuste para o menu inferior no mobile */
@media (max-width: 768px) {
    .py-12 {
        padding-bottom: 5rem;
    }
}

/* Esconde a barra de rolagem horizontal mas mantém a funcionalidade */
.overflow-x-auto {
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    /* Firefox */
    -ms-overflow-style: none;
    /* Internet Explorer 10+ */
}

.overflow-x-auto::-webkit-scrollbar {
    /* WebKit */
    display: none;
}
</style>

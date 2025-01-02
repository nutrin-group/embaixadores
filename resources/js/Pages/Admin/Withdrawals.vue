<template>

    <Head title="Saques" />

    <AuthenticatedLayout :auth="auth">
        <template #header>
            <h2 class="font-semibold text-lg text-gray-800 leading-tight">
                Aprovação de Saques
            </h2>
        </template>

        <div class="py-6 px-4 sm:px-6">
            <div class="max-w-7xl mx-auto">
                <!-- Filtros e Estatísticas -->
                <div class="bg-white rounded-xl p-4 sm:p-5 shadow-sm mb-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm text-gray-500">Total Pendente</span>
                            <p class="text-xl sm:text-2xl font-bold text-pink-500">
                                {{ new Intl.NumberFormat('pt-BR', {
                                    style: 'currency',
                                    currency: 'BRL'
                                }).format(totalPendente) }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Saques Pendentes</span>
                            <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ saquesPendentes }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Último Processamento</span>
                            <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ ultimoProcessamento }}</p>
                        </div>
                    </div>
                </div>

                <!-- Adicione antes da tabela -->
                <div v-if="hasSelectedSaques" class="mb-4 p-4 bg-white rounded-lg shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">
                            {{ selectedSaques.length }} saques selecionados
                        </span>
                        <button @click="abrirModalAprovacaoMultipla"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            Aprovar Selecionados
                        </button>
                    </div>
                </div>

                <!-- Tabela de Saques -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-4 sm:p-5 border-b border-gray-200">
                        <h3 class="text-base font-medium text-gray-700">Solicitações de Saque</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        <input type="checkbox"
                                            :checked="selectedSaques.length === saques.filter(s => s.status === 'Pendente').length"
                                            @change="$event.target.checked
                                                ? selectedSaques = saques.filter(s => s.status === 'Pendente').map(s => s.id)
                                                : selectedSaques = []"
                                            class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                    </th>
                                    <th class="px-3 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Data
                                    </th>
                                    <th class="px-3 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Influencer
                                    </th>
                                    <th
                                        class="hidden sm:table-cell px-3 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Manager
                                    </th>
                                    <th class="px-3 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Valor
                                    </th>
                                    <th class="px-3 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Status
                                    </th>
                                    <th
                                        class="px-3 sm:px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="saque in saques" :key="saque.id">
                                    <td class="px-3 sm:px-5 py-4 whitespace-nowrap">
                                        <input v-if="saque.status === 'Pendente'" type="checkbox"
                                            v-model="selectedSaques" :value="saque.id"
                                            class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                    </td>
                                    <td class="px-3 sm:px-5 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ saque.data }}
                                    </td>
                                    <td class="px-3 sm:px-5 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ saque.influencer }}</p>
                                                <div class="flex items-center gap-2">
                                                    <p class="text-sm text-gray-500">{{ saque.pix_key }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="hidden sm:table-cell px-3 sm:px-5 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ saque.manager }}
                                    </td>
                                    <td class="px-3 sm:px-5 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ formatCurrency(saque.valor) }}
                                    </td>
                                    <td class="px-3 sm:px-5 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'px-2 py-1 text-xs rounded-full': true,
                                            'bg-yellow-100 text-yellow-800': saque.status === 'Pendente',
                                            'bg-green-100 text-green-800': saque.status === 'Aprovado',
                                            'bg-red-100 text-red-800': saque.status === 'Cancelado'
                                        }">
                                            {{ saque.status }}
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-5 py-4 whitespace-nowrap text-right text-sm">
                                        <div class="flex justify-end space-x-3">
                                            <button v-if="saque.status === 'Pendente'"
                                                @click="abrirModalAprovacao(saque)" :disabled="saque.loading"
                                                class="text-green-600 hover:text-green-900" title="Aprovar">
                                                <template v-if="saque.loading">
                                                    <ArrowPathIcon class="h-5 w-5 animate-spin" />
                                                </template>
                                                <template v-else>
                                                    <CheckCircleIcon class="h-5 w-5" />
                                                </template>
                                            </button>
                                            <button v-if="saque.status === 'Pendente'"
                                                @click="abrirModalCancelamento(saque)" :disabled="saque.loading"
                                                class="text-red-600 hover:text-red-900" title="Cancelar">
                                                <template v-if="saque.loading">
                                                    <ArrowPathIcon class="h-5 w-5 animate-spin" />
                                                </template>
                                                <template v-else>
                                                    <XCircleIcon class="h-5 w-5" />
                                                </template>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Mensagem quando não há saques -->
                                <tr v-if="saques.length === 0">
                                    <td colspan="7" class="px-3 sm:px-5 py-4 text-center text-gray-500">
                                        Nenhuma solicitação de saque encontrada
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tabela de Saques Processados -->
                <div class="mt-8 bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-4 sm:p-5 border-b border-gray-200">
                        <h3 class="text-base font-medium text-gray-700">Histórico de Transações</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Data
                                    </th>
                                    <th class="px-3 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Influencer
                                    </th>
                                    <th
                                        class="hidden sm:table-cell px-3 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Manager
                                    </th>
                                    <th class="px-3 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Valor
                                    </th>
                                    <th class="px-3 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Tipo
                                    </th>
                                    <th class="px-3 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="transaction in transactions" :key="transaction.id">
                                    <td class="px-3 sm:px-5 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ transaction.data }}
                                    </td>
                                    <td class="px-3 sm:px-5 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ transaction.user.name }}
                                                </p>
                                                <p class="text-sm text-gray-500">{{ transaction.user.payment_key }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="hidden sm:table-cell px-3 sm:px-5 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ transaction.user.manager?.name || '-' }}
                                    </td>
                                    <td class="px-3 sm:px-5 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ formatCurrency(transaction.amount) }}
                                    </td>
                                    <td class="px-3 sm:px-5 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'px-2 py-1 text-xs rounded-full': true,
                                            'bg-green-100 text-green-800': transaction.type === 'withdrawal',
                                            'bg-yellow-100 text-yellow-800': transaction.type === 'withdrawal_refund'
                                        }">
                                            {{ transaction.type === 'withdrawal' ? 'Saque' : 'Estorno' }}
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-5 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'px-2 py-1 text-xs rounded-full': true,
                                            'bg-green-100 text-green-800': transaction.status === 'completed',
                                            'bg-red-100 text-red-800': transaction.status === 'failed'
                                        }">
                                            {{ transaction.status === 'completed' ? 'Concluído' : 'Falhou' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="transactions.length === 0">
                                    <td colspan="6" class="px-3 sm:px-5 py-4 text-center text-gray-500">
                                        Nenhuma transação encontrada
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Modal de Aprovação -->
    <Dialog as="div" :open="isModalOpen" @close="isModalOpen = false" class="relative z-50">
        <div class="fixed inset-0 bg-black/30" aria-hidden="true" />

        <div class="fixed inset-0 flex items-center justify-center p-4">
            <DialogPanel class="w-full max-w-md rounded-lg bg-white">
                <div class="p-6">
                    <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">
                        {{ isMultipleApproval ? 'Aprovar Múltiplos Saques' : 'Aprovar Saque' }}
                    </DialogTitle>

                    <div class="mt-4">
                        <template v-if="isMultipleApproval">
                            <p class="text-sm text-gray-500">
                                Você está prestes a aprovar {{ saquesParaAprovar.length }} saques:
                            </p>
                            <div class="mt-3 max-h-48 overflow-y-auto">
                                <div v-for="saque in saquesParaAprovar" :key="saque.id"
                                    class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm font-medium">{{ saque.influencer }}</span>
                                    <span class="text-sm text-gray-600">
                                        {{ new Intl.NumberFormat('pt-BR', {
                                            style: 'currency', currency: 'BRL'
                                        }).format(saque.valor) }}
                                    </span>
                                </div>
                            </div>
                            <p class="mt-3 text-sm font-medium text-gray-700">
                                Total: {{ new Intl.NumberFormat('pt-BR', {
                                    style: 'currency',
                                    currency: 'BRL'
                                }).format(saquesParaAprovar.reduce((acc, saque) => acc + saque.valor, 0)) }}
                            </p>
                        </template>
                        <template v-else>
                            <div class="space-y-3">
                                <p class="text-sm text-gray-500">
                                    Confirme os detalhes do saque:
                                </p>
                                <div class="bg-gray-50 p-3 rounded-lg space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Influencer:</span>
                                        <span class="text-sm font-medium">{{ saqueParaAprovar?.influencer }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Valor:</span>
                                        <span class="text-sm font-medium">
                                            {{ new Intl.NumberFormat('pt-BR', {
                                                style: 'currency',
                                                currency: 'BRL'
                                            }).format(saqueParaAprovar?.valor) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Chave PIX:</span>
                                        <span class="text-sm font-medium">{{ saqueParaAprovar?.pix_key }}</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button"
                            class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                            @click="isModalOpen = false">
                            Cancelar
                        </button>
                        <button type="button"
                            class="inline-flex justify-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                            @click="confirmarAprovacao">
                            Confirmar Aprovação
                        </button>
                    </div>
                </div>
            </DialogPanel>
        </div>
    </Dialog>
    <!-- Modal Cancelamento -->
    <Dialog as="div" :open="isModalCancelOpen" @close="isModalCancelOpen = false" class="relative z-50">
        <div class="fixed inset-0 bg-black/30" aria-hidden="true" />

        <div class="fixed inset-0 flex items-center justify-center p-4">
            <DialogPanel class="w-full max-w-md rounded-lg bg-white">
                <div class="p-6">
                    <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">
                        Cancelar Saque
                    </DialogTitle>

                    <div class="mt-4">
                        <div class="space-y-3">
                            <p class="text-sm text-gray-500">
                                Tem certeza que deseja cancelar este saque? O valor será devolvido ao saldo do usuário.
                            </p>
                            <div class="bg-gray-50 p-3 rounded-lg space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Influencer:</span>
                                    <span class="text-sm font-medium">{{ saqueParaCancelar?.influencer }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Valor:</span>
                                    <span class="text-sm font-medium">
                                        {{ new Intl.NumberFormat('pt-BR', {
                                            style: 'currency',
                                            currency: 'BRL'
                                        }).format(saqueParaCancelar?.valor) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button"
                            class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                            @click="isModalCancelOpen = false">
                            Voltar
                        </button>
                        <button type="button"
                            class="inline-flex justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                            @click="confirmarCancelamento">
                            Confirmar Cancelamento
                        </button>
                    </div>
                </div>
            </DialogPanel>
        </div>
    </Dialog>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, computed, watch } from 'vue'
import { router, usePage, Head } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import { CheckCircleIcon, ArrowPathIcon, XCircleIcon } from '@heroicons/vue/24/outline'
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'

const toast = useToast()
const selectedSaques = ref([])

// Adicione esta computed property para controlar o botão de aprovação em massa
const hasSelectedSaques = computed(() => selectedSaques.value.length > 0)

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value)
}

const props = defineProps({
    auth: {
        type: Object,
        required: true
    },
    withdrawals: {
        type: Array,
        required: true
    },
    transactions: {
        type: Array,
        required: true
    },
    statistics: {
        type: Object,
        required: true
    },

})
console.log(props.transactions)
const saques = ref(props.withdrawals)

// Observa mudanças nas props para atualizar o estado local
watch(() => props.withdrawals, (newWithdrawals) => {
    saques.value = newWithdrawals
}, { deep: true })

const totalPendente = computed(() => props.statistics.totalPendente || 0)
const saquesPendentes = computed(() => props.statistics.saquesPendentes || 0)
const ultimoProcessamento = computed(() => props.statistics.ultimoProcessamento || 'Nenhum')

// Estados do modal
const isModalOpen = ref(false)
const saqueParaAprovar = ref(null)
const saquesParaAprovar = ref([])
const isMultipleApproval = ref(false)

// Adiciona estados para o modal de cancelamento
const isModalCancelOpen = ref(false)
const saqueParaCancelar = ref(null)

// Função para abrir o modal de aprovação individual
const abrirModalAprovacao = (saque) => {
    saqueParaAprovar.value = saque
    saquesParaAprovar.value = []
    isMultipleApproval.value = false
    isModalOpen.value = true
}

// Função para abrir o modal de aprovação múltipla
const abrirModalAprovacaoMultipla = () => {
    saqueParaAprovar.value = null
    saquesParaAprovar.value = saques.value.filter(s => selectedSaques.value.includes(s.id))
    isMultipleApproval.value = true
    isModalOpen.value = true
}

// Função de confirmação
const confirmarAprovacao = async () => {
    isModalOpen.value = false

    if (isMultipleApproval.value) {
        await aprovarSaquesSelecionados()
    } else if (saqueParaAprovar.value) {
        await aprovarSaque(saqueParaAprovar.value.id)
    }
}

// Função para aprovar múltiplos saques
const aprovarSaquesSelecionados = async () => {
    toast.info('Efetuando transações...')

    try {
        if (!route().has('openpix.withdraw')) {
            throw new Error('Rota de pagamento PIX não configurada')
        }

        axios.defaults.headers.common['X-CSRF-TOKEN'] = usePage().props.csrf
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
        axios.defaults.withCredentials = true

        // Processa cada saque selecionado
        for (const saqueId of selectedSaques.value) {
            const saque = saques.value.find(s => s.id === saqueId)
            if (!saque) continue

            try {
                // Primeiro tenta fazer o pagamento PIX
                const { data } = await axios.post(route('openpix.withdraw'), {
                    amount: Number(saque.valor),
                    user_id: Number(saque.user_id)
                })

                if (data.success) {
                    // Se o PIX foi bem sucedido, aprova o saque
                    await router.post(route('admin.withdrawals.approve', { id: saqueId }), {}, {
                        preserveScroll: true,
                        onSuccess: () => {
                            saque.status = 'Aprovado'
                            toast.success(`Saque de ${saque.influencer} aprovado com sucesso!`)
                        },
                        onError: (errors) => {
                            console.error('Erro na aprovação:', errors)
                            toast.error(`Erro ao aprovar saque de ${saque.influencer}`)
                        }
                    })
                } else {
                    // Se o PIX falhou, cancela o saque automaticamente
                    await router.post(route('admin.withdrawals.cancel', { id: saqueId }), {}, {
                        preserveScroll: true,
                        onSuccess: () => {
                            saque.status = 'Cancelado'
                            toast.error(`Falha no PIX para ${saque.influencer}: ${data.message}`)
                        }
                    })
                }
            } catch (error) {
                console.error(`Erro processando saque ${saqueId}:`, error)
                toast.error(`Erro ao processar saque de ${saque.influencer}`)
            }
        }

        selectedSaques.value = [] // Limpa a seleção após processar todos
    } catch (error) {
        console.error('Erro completo:', error)
        toast.error('Erro ao processar pagamentos: ' + (error.response?.data?.message || error.message))
    }
}

// Atualiza a função existente para usar o token do Inertia
const aprovarSaque = async (id) => {
    const saque = saques.value.find(s => s.id === id)
    if (!saque) {
        toast.error('Saque não encontrado')
        return
    }

    toast.info('Efetuando transação...')

    try {
        if (!route().has('openpix.withdraw')) {
            throw new Error('Rota de pagamento PIX não configurada')
        }

        axios.defaults.headers.common['X-CSRF-TOKEN'] = usePage().props.csrf
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
        axios.defaults.withCredentials = true

        const { data } = await axios.post(route('openpix.withdraw'), {
            amount: Number(saque.valor),
            user_id: Number(saque.user_id)
        })

        if (data.success) {
            await router.post(route('admin.withdrawals.approve', { id }), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success('Saque aprovado e PIX enviado com sucesso!')
                    saque.status = 'Aprovado'
                },
                onError: (errors) => {
                    console.error('Erro na aprovação:', errors)
                    toast.error('Erro ao registrar aprovação do saque')
                }
            })
        } else {
            // Se o PIX falhou, cancela o saque automaticamente
            await router.post(route('admin.withdrawals.cancel', { id }), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    saque.status = 'Cancelado'
                    toast.error('Falha no envio do PIX: ' + data.message)
                }
            })
        }
    } catch (error) {
        console.error('Erro completo:', error)
        toast.error('Erro ao processar o pagamento: ' + (error.response?.data?.message || error.message))
    }
}

// Atualize o botão de cancelar na tabela para usar o novo modal
const abrirModalCancelamento = (saque) => {
    saqueParaCancelar.value = saque
    isModalCancelOpen.value = true
}

// Nova função para confirmar o cancelamento
const confirmarCancelamento = async () => {
    if (!saqueParaCancelar.value) return;

    try {
        const saque = saques.value.find(s => s.id === saqueParaCancelar.value.id);
        if (saque) {
            saque.loading = true;
        }

        await router.post(
            `/admin/withdrawals/${saqueParaCancelar.value.id}/cancel`, // URL direta
            {},
            {
                preserveScroll: true,
                onSuccess: (page) => {
                    const saque = saques.value.find(s => s.id === saqueParaCancelar.value.id);
                    if (saque) {
                        saque.status = 'Cancelado';
                        saque.loading = false;
                    }

                    selectedSaques.value = selectedSaques.value.filter(id => id !== saqueParaCancelar.value.id);
                    isModalCancelOpen.value = false;
                    saqueParaCancelar.value = null;
                    toast.success('Saque cancelado e valor estornado com sucesso');
                },
                onError: (error) => {
                    const saque = saques.value.find(s => s.id === saqueParaCancelar.value.id);
                    if (saque) {
                        saque.loading = false;
                    }
                    toast.error(error.message || 'Erro ao cancelar saque');
                }
            }
        );
    } catch (error) {
        console.error('Erro ao cancelar:', error);
        const saque = saques.value.find(s => s.id === saqueParaCancelar.value.id);
        if (saque) {
            saque.loading = false;
        }
        toast.error('Erro ao processar cancelamento');
    }
};

// Adicione um watcher para debug
watch(() => props.withdrawals, (newValue) => {
    console.log('Withdrawals atualizados:', newValue)
    console.log('Status dos saques:', newValue.map(w => w.status))
}, { deep: true })

</script>

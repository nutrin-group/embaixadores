<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-lg text-gray-800 leading-tight">
                Gerenciar Embaixadores
            </h2>
        </template>

        <div class="py-6 px-4 sm:px-6">
            <div class="max-w-7xl mx-auto">
                <!-- Success Message -->
                <div v-if="$page.props.flash?.success"
                    class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                    {{ $page.props.flash.success }}
                </div>

                <!-- Filtros e Busca -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-1">
                            <input type="text" v-model="search"
                                class="w-full rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                                placeholder="Buscar por nome, email ou Instagram..." />
                        </div>
                        <div class="sm:w-48">
                            <select v-model="statusFilter"
                                class="w-full rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500">
                                <option value="">Todos os Status</option>
                                <option value="active">Ativos</option>
                                <option value="pending">Pendentes</option>
                                <option value="blocked">Bloqueados</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tabela -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nome
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Rede Social
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Gerente
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="ambassador in filteredAmbassadors" :key="ambassador.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ ambassador.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ ambassador.social_network }} - @{{ ambassador.social_media_username }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ ambassador.email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="[
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                            {
                                                'bg-green-100 text-green-800': ambassador.status === 'active',
                                                'bg-yellow-100 text-yellow-800': ambassador.status === 'pending',
                                                'bg-red-100 text-red-800': ambassador.status === 'blocked'
                                            }
                                        ]">
                                            {{ statusLabels[ambassador.status] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ ambassador.manager?.name || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <!-- Botão de Aprovar (apenas para pendentes) -->
                                            <button v-if="ambassador.status === 'pending'"
                                                @click="approveAmbassador(ambassador)"
                                                class="text-green-600 hover:text-green-900">
                                                Aprovar
                                            </button>

                                            <!-- Botões existentes de Bloquear/Ativar -->
                                            <Link v-if="ambassador.status !== 'blocked'"
                                                :href="`/admin/ambassadors/${ambassador.id}/block`" method="put"
                                                as="button" class="text-red-600 hover:text-red-900">
                                            Bloquear
                                            </Link>
                                            <Link v-else :href="`/admin/ambassadors/${ambassador.id}/activate`"
                                                method="put" as="button" class="text-green-600 hover:text-green-900">
                                            Ativar
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmação de Bloqueio -->
        <Modal :show="showBlockModal" @close="closeBlockModal">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Confirmar Bloqueio
                </h3>
                <p class="text-sm text-gray-600 mb-6">
                    Tem certeza que deseja bloquear este embaixador? Esta ação impedirá que ele acesse o sistema.
                </p>
                <div class="flex justify-end gap-4">
                    <SecondaryButton @click="closeBlockModal">
                        Cancelar
                    </SecondaryButton>
                    <DangerButton :class="{ 'opacity-25': processing }" :disabled="processing" @click="confirmBlock">
                        Bloquear
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const props = defineProps({
    ambassadors: Array
});

const search = ref('');
const statusFilter = ref('');
const showBlockModal = ref(false);
const processing = ref(false);
const ambassadorToBlock = ref(null);

const statusLabels = {
    'active': 'Ativo',
    'pending': 'Pendente',
    'blocked': 'Bloqueado'
};

const filteredAmbassadors = computed(() => {
    return props.ambassadors.filter(ambassador => {
        const matchesSearch = search.value === '' ||
            ambassador.name.toLowerCase().includes(search.value.toLowerCase()) ||
            ambassador.email.toLowerCase().includes(search.value.toLowerCase()) ||
            ambassador.instagram_id.toLowerCase().includes(search.value.toLowerCase());

        const matchesStatus = statusFilter.value === '' || ambassador.status === statusFilter.value;

        return matchesSearch && matchesStatus;
    });
});

const openBlockModal = (ambassador) => {
    ambassadorToBlock.value = ambassador;
    showBlockModal.value = true;
};

const closeBlockModal = () => {
    showBlockModal.value = false;
    ambassadorToBlock.value = null;
    processing.value = false;
};

const confirmBlock = () => {
    if (!ambassadorToBlock.value) return;

    processing.value = true;
    router.put(`/admin/ambassadors/${ambassadorToBlock.value.id}/block`, {}, {
        onSuccess: () => {
            closeBlockModal();
        },
        onError: () => {
            processing.value = false;
        }
    });
};

const activateAmbassador = (ambassador) => {
    processing.value = true;
    router.put(`/admin/ambassadors/${ambassador.id}/activate`, {}, {
        onSuccess: () => {
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        }
    });
};

const approveAmbassador = (ambassador) => {
    router.put(`/admin/ambassadors/${ambassador.id}/approve`, {}, {
        onSuccess: () => {
            toast.success('Embaixador aprovado com sucesso!', {
                position: toast.POSITION.TOP_RIGHT,
                autoClose: 3000
            });
            ambassador.status = 'active';
        },
        onError: () => {
            toast.error('Erro ao aprovar embaixador', {
                position: toast.POSITION.TOP_RIGHT,
                autoClose: 3000
            });
        }
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-lg text-gray-800 leading-tight">
                Gerenciar Managers
            </h2>
        </template>

        <div class="py-6 px-4 sm:px-6">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-end mb-6">
                    <Link :href="route('admin.managers.create')"
                        class="px-6 py-3 bg-pink-500 text-white rounded-lg font-semibold hover:bg-pink-600 transition-colors">
                    Novo Manager
                    </Link>
                </div>

                <!-- Success Message -->
                <div v-if="$page.props.flash?.success"
                    class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                    {{ $page.props.flash.success }}
                </div>

                <!-- Managers Table -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-4 sm:p-5 border-b border-gray-200">
                        <h3 class="text-base font-medium text-gray-700">Lista de Managers</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="manager in managers.data" :key="manager.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ manager.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ manager.email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                                        manager.discount_code }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'px-2 py-1 text-xs rounded-full': true,
                                            'bg-green-100 text-green-800': manager.status === 'active',
                                            'bg-yellow-100 text-yellow-800': manager.status === 'pending',
                                            'bg-red-100 text-red-800': manager.status === 'blocked' || manager.status === 'refused'
                                        }">
                                            {{ manager.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <Link :href="route('admin.managers.edit', manager.id)"
                                            class="text-pink-600 hover:text-pink-900 font-medium mr-4">
                                        Editar
                                        </Link>
                                        <button @click="confirmDelete(manager)"
                                            class="text-red-600 hover:text-red-900 font-medium">
                                            Excluir
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    <Pagination :links="managers.links" />
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="closeDeleteModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Confirmar Exclusão
                </h2>
                <p class="text-sm text-gray-500 mb-6">
                    Tem certeza que deseja excluir este manager? Esta ação não pode ser desfeita.
                </p>
                <div class="flex justify-end gap-4">
                    <SecondaryButton @click="closeDeleteModal">
                        Cancelar
                    </SecondaryButton>
                    <DangerButton @click="deleteManager" :class="{ 'opacity-25': processing }" :disabled="processing">
                        Excluir
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    managers: Object
});

const showDeleteModal = ref(false);
const managerToDelete = ref(null);
const processing = ref(false);

const confirmDelete = (manager) => {
    managerToDelete.value = manager;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    managerToDelete.value = null;
};

const deleteManager = () => {
    processing.value = true;
    router.delete(route('admin.managers.destroy', managerToDelete.value.id), {
        onSuccess: () => {
            closeDeleteModal();
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        }
    });
};
</script>

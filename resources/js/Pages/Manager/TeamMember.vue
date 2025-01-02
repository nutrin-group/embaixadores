<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    ambassador: {
        type: Object,
        required: true
    }
});

const form = useForm({
    status: props.ambassador.status
});

// Adicionar feedback visual
const showSuccessMessage = ref(false);

const updateStatus = () => {
    form.put(`/manager/team/${props.ambassador.id}/status`, {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessMessage.value = true;
            setTimeout(() => {
                showSuccessMessage.value = false;
            }, 3000);
        }
    });
};
</script>

<template>

    <Head :title="`Gerenciar ${ambassador.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-lg text-gray-800 leading-tight">
                Gerenciar Embaixador
            </h2>
        </template>

        <div class="py-4 px-3 sm:py-6 sm:px-6">
            <div class="max-w-7xl mx-auto space-y-4 sm:space-y-6">
                <!-- Card Principal -->
                <div class="bg-white rounded-lg sm:rounded-xl shadow-sm overflow-hidden">
                    <div class="p-4 sm:p-6 border-b border-gray-200">
                        <!-- Layout mobile-first para informações do embaixador -->
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
                            <div class="space-y-2">
                                <h3 class="text-lg sm:text-xl font-medium text-gray-900">
                                    {{ ambassador.name }}
                                </h3>
                                <div class="space-y-1">
                                    <p class="text-sm text-gray-500 flex items-center gap-2">
                                        <i class="fas fa-envelope"></i>
                                        {{ ambassador.email }}
                                    </p>
                                    <p class="text-sm text-gray-500 flex items-center gap-2">
                                        <i class="fab fa-instagram"></i>
                                        {{ ambassador.instagram_id }}
                                    </p>
                                </div>
                            </div>

                            <!-- Status com feedback visual -->
                            <div class="flex flex-col gap-3 w-full sm:w-auto">
                                <select v-model="form.status"
                                    class="w-full sm:w-48 rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500">
                                    <option value="pending">Pendente</option>
                                    <option value="active">Ativo</option>
                                    <option value="blocked">Bloqueado</option>
                                </select>
                                <button @click="updateStatus"
                                    class="w-full sm:w-48 px-4 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 disabled:opacity-50 transition-colors"
                                    :disabled="form.processing">
                                    <span v-if="!form.processing">Atualizar Status</span>
                                    <span v-else>Atualizando...</span>
                                </button>
                                <!-- Mensagem de sucesso -->
                                <div v-if="showSuccessMessage"
                                    class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg">
                                    Status atualizado com sucesso!
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estatísticas em cards separados para mobile -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm font-medium text-gray-500">Total de Vendas</p>
                            <p class="mt-2 text-2xl sm:text-3xl font-semibold text-gray-900">
                                {{ ambassador.total_sales }}
                            </p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm font-medium text-gray-500">Valor Total</p>
                            <p class="mt-2 text-2xl sm:text-3xl font-semibold text-gray-900">
                                {{ new Intl.NumberFormat('pt-BR', {
                                    style: 'currency', currency: 'BRL'
                                }).format(ambassador.total_amount) }}
                            </p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm font-medium text-gray-500">Comissões</p>
                            <p class="mt-2 text-2xl sm:text-3xl font-semibold text-gray-900">
                                {{ new Intl.NumberFormat('pt-BR', {
                                    style: 'currency', currency: 'BRL'
                                }).format(ambassador.total_commission) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Posts Recentes -->
                <div class="bg-white rounded-lg sm:rounded-xl shadow-sm overflow-hidden">
                    <div class="p-4 sm:p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Posts Recentes</h3>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div v-for="post in ambassador.recent_posts" :key="post.url"
                            class="p-4 sm:p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3">
                                <div class="space-y-2">
                                    <p class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                        <i :class="post.type === 'story' ? 'fas fa-circle' : 'fas fa-square'"></i>
                                        {{ post.type }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ post.description }}
                                    </p>
                                </div>
                                <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-4">
                                    <span class="text-sm text-gray-500">{{ post.date }}</span>
                                    <a :href="post.url" target="_blank"
                                        class="text-pink-500 hover:text-pink-600 p-2 hover:bg-pink-50 rounded-full transition-colors">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

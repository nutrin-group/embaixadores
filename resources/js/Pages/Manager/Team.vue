<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    ambassadors: {
        type: Array,
        default: () => []
    }
});

const search = ref('');
const statusFilter = ref('');

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
            ambassador.instagram_id?.toLowerCase().includes(search.value.toLowerCase());

        const matchesStatus = statusFilter.value === '' || ambassador.status === statusFilter.value;

        return matchesSearch && matchesStatus;
    });
});
</script>

<template>

    <Head title="Minha Equipe" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-lg text-gray-800 leading-tight">
                Minha Equipe
            </h2>
        </template>

        <div class="py-6 px-4 sm:px-6">
            <div class="max-w-7xl mx-auto">
                <!-- Filtros e Busca -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-1">
                            <input type="text" v-model="search"
                                class="w-full rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                                placeholder="Buscar embaixador..." />
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
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Embaixador
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Instagram
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total de Vendas
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="ambassador in filteredAmbassadors" :key="ambassador.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ ambassador.name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ ambassador.email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ ambassador.instagram_id }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm text-gray-900">{{ ambassador.total_sales }}</div>
                                            <div class="text-sm text-gray-500 ml-2">
                                                ({{ new Intl.NumberFormat('pt-BR', {
                                                    style: 'currency',
                                                    currency: 'BRL'
                                                }).format(ambassador.total_amount) }})
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'px-2 py-1 text-xs rounded-full': true,
                                            'bg-green-100 text-green-800': ambassador.status === 'active',
                                            'bg-yellow-100 text-yellow-800': ambassador.status === 'pending',
                                            'bg-red-100 text-red-800': ambassador.status === 'blocked'
                                        }">
                                            {{ statusLabels[ambassador.status] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="`/manager/team/${ambassador.id}`"
                                            class="text-pink-600 hover:text-pink-900">
                                        Gerenciar
                                        </Link>
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

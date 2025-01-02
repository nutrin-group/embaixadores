<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import CoinBalance from '@/Components/CoinBalance.vue';
import { router } from '@inertiajs/vue3';

import {
    Bars3Icon,
    HomeIcon,
    UsersIcon as TeamIcon,
    StarIcon as AmbassadorIcon,
    BanknotesIcon as WithdrawalIcon,
    ChartBarIcon as ReportIcon,
    CurrencyDollarIcon as CommissionIcon,
    PhotoIcon as PostsIcon,
    AcademicCapIcon as AcademyIcon,
    UserCircleIcon as ProfileIcon,
    ArrowLeftStartOnRectangleIcon as LogoutIcon,
    ShoppingBagIcon as SalesIcon,
    TrophyIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    auth: {
        type: Object,
        required: true
    }
});

const showingNavigationDropdown = ref(false);
const page = usePage();
const user = computed(() => {

    return page.props.auth.user;
});

// Menus específicos por tipo de usuário com URLs diretas
const adminMenu = [
    { name: 'Dashboard', href: '/admin/dashboard', icon: HomeIcon },
    { name: 'Gerentes', href: '/admin/managers', icon: TeamIcon },
    { name: 'Embaixadores', href: '/admin/ambassadors', icon: AmbassadorIcon },
    { name: 'Saques', href: '/admin/withdrawals', icon: WithdrawalIcon },
    { name: 'Relatórios', href: '/admin/reports', icon: ReportIcon }
];

const managerMenu = [
    { name: 'Dashboard', href: '/manager/dashboard', icon: HomeIcon },
    { name: 'Embaixadores', href: '/manager/team', icon: AmbassadorIcon },
    { name: 'Vendas', href: '/manager/sales', icon: SalesIcon },
    { name: 'Relatórios', href: '/manager/reports', icon: ReportIcon },
    { name: 'Academy', href: '/manager/briefings', icon: AcademyIcon }
];

const ambassadorMenu = [
    { name: 'Dashboard', href: '/ambassador/dashboard', icon: HomeIcon },
    { name: 'Vendas', href: '/ambassador/sales', icon: SalesIcon },
    { name: 'Solicitar Saque', href: '/ambassador/withdrawals', icon: WithdrawalIcon },
    { name: 'Postagens', href: '/ambassador/posts', icon: PostsIcon },
    { name: 'Academy', href: '/ambassador/briefings', icon: AcademyIcon }
];

const userMenu = computed(() => {
    const type = user.value?.type;
    switch (type) {
        case 'admin': return adminMenu;
        case 'manager': return managerMenu;
        case 'ambassador': return ambassadorMenu;
        default: return [];
    }
});

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Mobile Menu Button -->
        <div class="lg:hidden fixed top-0 left-0 right-0 z-50 bg-white shadow-sm px-4 py-2">
            <div class="flex items-center justify-between">
                <a href="/">
                    <ApplicationLogo class="h-12" />
                </a>
                <div class="flex items-center space-x-2">
                    <!-- Ranking Position e CoinBalance apenas para embaixadores -->
                    <template v-if="user?.type === 'ambassador'">
                        <div class="flex items-center bg-yellow-100 text-yellow-800 rounded-full px-3 py-1">
                            <TrophyIcon class="h-4 w-4 mr-1" />
                            <span class="text-sm font-medium">
                                #{{ user.ranking_position }}
                                <span class="text-xs opacity-75">/ {{ user.total_ambassadors }}</span>
                            </span>
                        </div>
                        <Link :href="route('ambassador.rewards')">
                        <CoinBalance :balance="user.coin_balance" />
                        </Link>
                    </template>
                </div>
            </div>
        </div>

        <!-- Sidebar - Desktop -->
        <div class="hidden lg:flex w-64 bg-white shadow-lg flex-col fixed inset-y-0">
            <!-- Logo e Informações do Usuário -->
            <div class="p-6 border-b shrink-0">
                <ApplicationLogo class="h-8 mb-4" />
                <!-- Ranking e Coin Balance apenas para embaixadores -->
                <div v-if="user?.type === 'ambassador'" class="flex flex-col space-y-2">
                    <div class="flex items-center bg-yellow-100 text-yellow-800 rounded-full px-3 py-1">
                        <TrophyIcon class="h-4 w-4 mr-1" />
                        <span class="text-sm font-medium">
                            #{{ user.ranking_position }}
                            <span class="text-xs opacity-75">/ {{ user.total_ambassadors }}</span>
                        </span>
                    </div>
                    <Link :href="route('ambassador.rewards')" class="inline-flex">
                    <CoinBalance :balance="user.coin_balance" />
                    </Link>
                </div>
            </div>

            <!-- Área scrollável -->
            <div class="flex-1 flex flex-col overflow-y-auto">
                <!-- Menu Principal -->
                <div class="p-4">
                    <nav class="space-y-1">
                        <!-- Menu do Embaixador -->
                        <template v-if="user?.type === 'ambassador'">
                            <Link v-for="item in ambassadorMenu" :key="item.name" :href="item.href" :class="[
                                'flex items-center px-4 py-3 text-gray-600 hover:text-[#f45197] hover:bg-pink-50 rounded-lg transition-colors duration-200',
                                { 'bg-pink-50 text-[#f45197]': $page.url.startsWith(item.href) }
                            ]">
                            <component :is="item.icon" class="w-6 h-6 mr-3" />
                            <span class="font-medium">{{ item.name }}</span>
                            </Link>
                        </template>

                        <!-- Menu do Gerente -->
                        <template v-else-if="user?.type === 'manager'">
                            <Link v-for="item in managerMenu" :key="item.name" :href="item.href" :class="[
                                'flex items-center px-4 py-3 text-gray-600 hover:text-[#f45197] hover:bg-pink-50 rounded-lg transition-colors duration-200',
                                { 'bg-pink-50 text-[#f45197]': $page.url.startsWith(item.href) }
                            ]">
                            <component :is="item.icon" class="w-6 h-6 mr-3" />
                            <span class="font-medium">{{ item.name }}</span>
                            </Link>
                        </template>

                        <!-- Menu do Admin -->
                        <template v-else-if="user?.type === 'admin'">
                            <Link v-for="item in adminMenu" :key="item.name" :href="item.href" :class="[
                                'flex items-center px-4 py-3 text-gray-600 hover:text-[#f45197] hover:bg-pink-50 rounded-lg transition-colors duration-200',
                                { 'bg-pink-50 text-[#f45197]': $page.url.startsWith(item.href) }
                            ]">
                            <component :is="item.icon" class="w-6 h-6 mr-3" />
                            <span class="font-medium">{{ item.name }}</span>
                            </Link>
                        </template>
                    </nav>
                </div>

                <!-- Adicionar seção Minha Conta -->
                <div class="p-4 border-t">
                    <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                        Minha Conta
                    </h3>
                    <nav class="space-y-1">
                        <Link href="/profile" :class="[
                            'flex items-center px-4 py-3 text-gray-600 hover:text-[#f45197] hover:bg-pink-50 rounded-lg transition-colors duration-200',
                            { 'bg-pink-50 text-[#f45197]': route().current('profile.edit') }
                        ]">
                        <ProfileIcon class="w-6 h-6 mr-3" />
                        <span class="font-medium">Perfil</span>
                        </Link>
                        <form @submit.prevent="logout" method="post">
                            <button type="submit"
                                class="w-full flex items-center px-4 py-3 text-gray-600 hover:text-[#f45197] hover:bg-pink-50 rounded-lg transition-colors duration-200">
                                <LogoutIcon class="w-6 h-6 mr-3" />
                                <span class="font-medium">Sair</span>
                            </button>
                        </form>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div v-show="showingNavigationDropdown" class="lg:hidden fixed inset-0 z-40 bg-white overflow-y-auto pt-16">
            <div class="p-4">
                <!-- Menu Principal -->
                <div class="mb-6">
                    <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                        Menu Principal
                    </h3>
                    <nav class="space-y-1">
                        <Link v-for="item in userMenu" :key="item.name" :href="item.href" :class="[
                            'flex items-center px-4 py-3 text-gray-600 hover:text-[#f45197] hover:bg-pink-50 rounded-lg transition-colors duration-200',
                            { 'bg-pink-50 text-[#f45197]': $page.url.startsWith(item.href) }
                        ]">
                        <i :class="[item.icon, 'mr-3 text-lg']"></i>
                        <span class="font-medium">{{ item.name }}</span>
                        </Link>
                    </nav>
                </div>

                <!-- Minha Conta -->
                <div class="mb-6">
                    <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                        Minha Conta
                    </h3>
                    <nav class="space-y-1">
                        <Link href="/profile" :active="route().current('profile.edit')"
                            class="flex items-center px-4 py-3 text-gray-600 hover:text-[#f45197] hover:bg-pink-50 rounded-lg transition-colors duration-200"
                            @click="showingNavigationDropdown = false">
                        <ProfileIcon class="w-6 h-6 mr-3" />
                        <span class="font-medium">Perfil</span>
                        </Link>
                        <form @submit.prevent="logout" method="post" class="w-full">
                            <button type="submit"
                                class="w-full flex items-center px-4 py-3 text-gray-600 hover:text-[#f45197] hover:bg-pink-50 rounded-lg transition-colors duration-200">
                                <LogoutIcon class="w-6 h-6 mr-3" />
                                <span class="font-medium">Sair</span>
                            </button>
                        </form>
                    </nav>
                </div>

                <!-- Suporte -->

            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:pl-64">
            <!-- Espaçamento para o botão do menu mobile -->
            <div class="h-12 lg:h-0"></div>
            <main class="py-6">
                <slot />
            </main>
        </div>

        <!-- Menu Inferior Mobile -->
        <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50">
            <!-- Menu do Embaixador -->
            <div v-if="user?.type === 'ambassador'" class="grid grid-cols-5 gap-1 px-2 py-2">
                <Link v-for="item in ambassadorMenu.slice(0, 2)" :key="item.name" :href="item.href"
                    class="flex flex-col items-center justify-center text-gray-500 text-xs p-1">
                <component :is="item.icon" class="w-6 h-6 mb-1" />
                <span>{{ item.name }}</span>
                </Link>

                <!-- Botão de Saque Destacado -->
                <Link :href="route('ambassador.withdrawals.index')"
                    class="flex flex-col items-center justify-center relative -mt-6">
                <div class="w-14 h-14 flex items-center justify-center rounded-full bg-[#f45197] shadow-lg mb-1">
                    <WithdrawalIcon class="w-7 h-7 text-white" />
                </div>
                <span class="text-xs text-gray-500">Saque</span>
                </Link>

                <Link v-for="item in ambassadorMenu.slice(3)" :key="item.name" :href="item.href"
                    class="flex flex-col items-center justify-center text-gray-500 text-xs p-1">
                <component :is="item.icon" class="w-6 h-6 mb-1" />
                <span>{{ item.name }}</span>
                </Link>
            </div>

            <!-- Menu do Gerente -->
            <div v-else-if="user?.type === 'manager'" class="grid grid-cols-5 gap-1 px-2 py-2">
                <Link v-for="item in managerMenu" :key="item.name" :href="item.href"
                    class="flex flex-col items-center text-gray-500 text-xs p-1">
                <component :is="item.icon" class="w-6 h-6 mb-2" />
                <span>{{ item.name }}</span>
                </Link>
            </div>

            <!-- Menu do Admin -->
            <div v-else-if="user?.type === 'admin'" class="grid grid-cols-5 gap-1 px-2 py-2">
                <Link v-for="item in adminMenu" :key="item.name" :href="item.href"
                    class="flex flex-col items-center text-gray-500 text-xs p-1">
                <component :is="item.icon" class="w-6 h-6 mb-2" />
                <span>{{ item.name }}</span>
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media (max-width: 768px) {
    .py-12 {
        padding-bottom: 5rem;
    }
}
</style>

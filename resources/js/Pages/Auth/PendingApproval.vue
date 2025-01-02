<template>
    <GuestLayout>

        <Head title="Cadastro Pendente" />

        <div class="px-4 sm:px-6 max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm p-6 sm:p-8 text-center">
                <div class="mb-6">
                    <svg class="mx-auto h-16 w-16 text-pink-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <h2 class="text-2xl font-bold text-gray-900 mb-4">
                    Cadastro Recebido!
                </h2>

                <p class="text-gray-600 mb-6">
                    Agradecemos seu interesse em ser uma embaixadora. Seu cadastro está em análise e você receberá um
                    e-mail assim que for aprovado.
                </p>

                <div class="mt-8">
                    <a href="/" class="text-pink-500 hover:text-pink-600 font-medium">
                        Voltar para a página inicial
                    </a>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
</script>

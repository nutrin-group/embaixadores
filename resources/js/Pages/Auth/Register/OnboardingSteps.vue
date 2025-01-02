<template>
    <div class="min-h-screen bg-[#291a47] text-white flex flex-col">
        <!-- Progress bar -->
        <div class="flex justify-between px-8 pt-8 mb-8">
            <div v-for="(step, index) in steps" :key="index"
                class="h-2 rounded-full flex-1 mx-1 transition-all duration-300" :class="[
                    currentStep > index ? 'bg-[#f45197]' :
                        currentStep === index ? 'bg-[#f45197]' : 'bg-[#ffb6d9]'
                ]">
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 flex flex-col items-center justify-center px-8 py-12">
            <!-- Step 1 -->
            <div v-if="currentStep === 0"
                class="text-center flex-1 flex flex-col items-center justify-center max-w-md mx-auto"
                :class="{ 'animate-fade-in': currentStep === 0 }">
                <div class="mb-12 relative">
                    <img src="/images/adesivos-02.svg" alt="Ganhe dinheiro"
                        class="w-80 h-80 object-contain animate-float" />
                </div>
                <h2 class="text-3xl font-bold mb-6 font-white">
                    Ganhe dinheiro nas suas redes sociais
                </h2>
                <p class="text-gray-400 text-lg leading-relaxed">
                    Você ganha dinheiro cada vez que um seguidor seu fizer uma compra através de você!
                </p>
            </div>

            <!-- Step 2 -->
            <div v-if="currentStep === 1"
                class="text-center flex-1 flex flex-col items-center justify-center max-w-md mx-auto"
                :class="{ 'animate-fade-in': currentStep === 1 }">
                <div class="mb-12 relative">
                    <img src="/images/adesivos-11.svg" alt="Indique amigos"
                        class="w-80 h-80 object-contain animate-float" />
                </div>
                <h2 class="text-3xl font-bold mb-6 font-white">
                    Indique alguém e ganhe mais!
                </h2>
                <p class="text-gray-400 text-lg leading-relaxed">
                    Você pode indicar outros influenciadores para o Influenciou e ganhar uma porcentagem das vendas de
                    cada um que entrou através de você.
                </p>
            </div>

            <!-- Step 3 -->
            <div v-if="currentStep === 2"
                class="text-center flex-1 flex flex-col items-center justify-center max-w-md mx-auto"
                :class="{ 'animate-fade-in': currentStep === 2 }">
                <div class="mb-12 relative">
                    <img src="/images/potes.svg" alt="Crescimento" class="w-80 h-80 object-contain animate-float" />
                </div>
                <h2 class="text-3xl font-bold mb-6 font-white">
                    Quanto mais participa, mais você ganha!
                </h2>
                <p class="text-gray-400 text-lg leading-relaxed">
                    Nosso sistema é baseado em conquistas: quanto mais ativo você estiver, mais benefícios você recebe.
                </p>
            </div>

            <!-- Navigation buttons -->
            <div class="w-full max-w-md mt-12 px-8">
                <button v-if="currentStep < steps.length - 1" @click="nextStep"
                    class="w-full bg-[#f45197] hover:bg-[#DB2777] text-white rounded-full py-4 font-semibold mb-4 transition-all duration-200 transform hover:scale-105">
                    Próximo
                </button>
                <button v-else @click="skip"
                    class="w-full bg-[#f45197] hover:bg-[#DB2777] text-white rounded-full py-4 font-semibold mb-4 transition-all duration-200 transform hover:scale-105">
                    Começar agora
                </button>
                <button v-if="currentStep < steps.length - 1" @click="skip"
                    class="w-full text-gray-400 py-2 hover:text-white transition-colors duration-200">
                    Pular
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const steps = [
    'Ganhe dinheiro',
    'Indique amigos',
    'Cresça conosco'
]

const currentStep = ref(0)

const registerUrl = computed(() => {
    const urlParams = new URLSearchParams(window.location.search)
    const ref = urlParams.get('ref')
    const baseUrl = '/register/ambassador'
    return ref ? `${baseUrl}?ref=${ref}` : baseUrl
})

const nextStep = () => {
    if (currentStep.value < steps.length - 1) {
        currentStep.value++
    }
}

const skip = () => {
    window.location.href = registerUrl.value
}
</script>

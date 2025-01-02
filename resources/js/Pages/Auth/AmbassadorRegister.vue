<template>
    <GuestLayout>

        <Head title="Cadastro de Embaixadora" />

        <div class="px-4 sm:px-6 max-w-2xl mx-auto">
            <!-- Indicador de Progresso -->
            <div class="mb-8 mt-4">
                <div class="flex items-center justify-between relative">
                    <div class="w-full absolute top-1/2 transform -translate-y-1/2 -mt-4">
                        <div class="h-1.5 bg-gray-200 rounded-full">
                            <div class="h-1.5 bg-pink-500 rounded-full transition-all duration-500"
                                :style="{ width: step === 1 ? '33%' : step === 2 ? '66%' : '100%' }"></div>
                        </div>
                    </div>
                    <div class="relative z-10 flex justify-between w-full">
                        <div class="flex flex-col items-center w-24">
                            <div
                                class="w-12 h-12 rounded-full bg-pink-500 flex items-center justify-center text-white shadow-md text-lg font-medium">
                                1
                            </div>
                            <span class="text-xs mt-2 text-gray-600 text-center break-words">Dados Básicos</span>
                        </div>
                        <div class="flex flex-col items-center w-32">
                            <div :class="[
                                'w-12 h-12 rounded-full flex items-center justify-center shadow-md transition-colors duration-300',
                                step >= 2 ? 'bg-pink-500 text-white' : 'bg-gray-200 text-gray-500'
                            ]">
                                2
                            </div>
                            <span class="text-xs mt-2 text-gray-600 text-center break-words">Informações
                                Complementares</span>
                        </div>
                        <div class="flex flex-col items-center w-24">
                            <div :class="[
                                'w-12 h-12 rounded-full flex items-center justify-center shadow-md transition-colors duration-300',
                                step === 3 ? 'bg-pink-500 text-white' : 'bg-gray-200 text-gray-500'
                            ]">
                                3
                            </div>
                            <span class="text-xs mt-2 text-gray-600 text-center break-words">Código de Desconto</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-8">
                <!-- Etapa 1: Dados Básicos -->
                <form v-if="step === 1" @submit.prevent="nextStep" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Nome Completo" class="text-sm font-medium" />
                        <TextInput id="name" type="text" class="mt-1 block w-full h-12 text-base" v-model="form.name"
                            required autofocus placeholder="Digite seu nome completo"
                            :class="{ 'border-green-500': form.name.length > 3 }" />
                        <InputError class="mt-1 text-xs" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="email" value="E-mail" />
                        <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required
                            placeholder="seu@email.com" />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Senha" class="text-sm font-medium" />
                        <div class="relative">
                            <TextInput id="password" :type="showPassword ? 'text' : 'password'"
                                class="mt-1 block w-full h-12 pr-10" v-model="form.password" required
                                placeholder="********" />
                            <button type="button"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 p-2 hover:text-gray-700"
                                @click.prevent="togglePassword">
                                <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z"
                                        clip-rule="evenodd" />
                                    <path
                                        d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fill-rule="evenodd"
                                        d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div class="mt-1 flex gap-1">
                            <div class="h-1 flex-1 rounded-full"
                                :class="passwordStrength >= 1 ? 'bg-red-500' : 'bg-gray-200'"></div>
                            <div class="h-1 flex-1 rounded-full"
                                :class="passwordStrength >= 2 ? 'bg-yellow-500' : 'bg-gray-200'"></div>
                            <div class="h-1 flex-1 rounded-full"
                                :class="passwordStrength >= 3 ? 'bg-green-500' : 'bg-gray-200'"></div>
                        </div>
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Confirmar Senha" />
                        <TextInput id="password_confirmation" type="password" class="mt-1 block w-full"
                            v-model="form.password_confirmation" required placeholder="********" />
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <InputLabel for="social_network" value="Rede Social Principal" />
                            <select id="social_network" v-model="form.social_network"
                                class="mt-1 block w-full border-gray-300 focus:border-pink-500 focus:ring-pink-500 rounded-md shadow-sm"
                                required>
                                <option value="" disabled>Selecione uma rede social</option>
                                <option value="instagram">Instagram</option>
                                <option value="tiktok">TikTok</option>
                                <option value="kwai">Kwai</option>
                                <option value="x">X (Twitter)</option>
                                <option value="facebook">Facebook</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.social_network" />
                        </div>

                        <div>
                            <InputLabel for="social_media_username" value="Nome de Usuário" />
                            <TextInput id="social_media_username" type="text" class="mt-1 block w-full"
                                v-model="form.social_media_username" required :placeholder="socialMediaPlaceholder" />
                            <InputError class="mt-2" :message="form.errors.social_media_username" />
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <PrimaryButton
                            class="w-full h-12 text-base font-medium bg-pink-500 hover:bg-pink-600 active:bg-pink-700 transform active:scale-98 transition-all"
                            :disabled="processing">
                            Próximo
                        </PrimaryButton>
                    </div>
                </form>

                <!-- Etapa 2: Informações Complementares -->
                <form v-else-if="step === 2" @submit.prevent="nextStep" class="space-y-6">
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="nationality" value="Nacionalidade" />
                                <TextInput id="nationality" type="text" class="mt-1 block w-full"
                                    v-model="form.nationality" required placeholder="Brasileira" />
                                <InputError class="mt-2" :message="form.errors.nationality" />
                            </div>

                            <div>
                                <InputLabel for="cpf" value="CPF" />
                                <TextInput id="cpf" type="text" class="mt-1 block w-full" v-model="form.cpf" required
                                    v-mask="'###.###.###-##'" placeholder="000.000.000-00" />
                                <InputError class="mt-2" :message="form.errors.cpf" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="birth_date" value="Data de Nascimento" />
                                <TextInput id="birth_date" type="date" class="mt-1 block w-full"
                                    v-model="form.birth_date" required />
                                <InputError class="mt-2" :message="form.errors.birth_date" />
                            </div>

                            <div>
                                <InputLabel for="phone" value="Telefone" />
                                <TextInput id="phone" type="text" class="mt-1 block w-full" v-model="form.phone"
                                    required v-mask="'(##) #####-####'" placeholder="(00) 00000-0000" />
                                <InputError class="mt-2" :message="form.errors.phone" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="cep" value="CEP" />
                            <TextInput id="cep" type="text" class="mt-1 block w-full" v-model="form.cep" required
                                v-mask="'#####-###'" @blur="fetchAddress" placeholder="00000-000" />
                            <InputError class="mt-2" :message="form.errors.cep" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="street" value="Rua" />
                                <TextInput id="street" type="text" class="mt-1 block w-full" v-model="form.street"
                                    required />
                                <InputError class="mt-2" :message="form.errors.street" />
                            </div>

                            <div>
                                <InputLabel for="number" value="Número" />
                                <TextInput id="number" type="text" class="mt-1 block w-full" v-model="form.number"
                                    required placeholder="123" />
                                <InputError class="mt-2" :message="form.errors.number" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="complement" value="Complemento (opcional)" />
                            <TextInput id="complement" type="text" class="mt-1 block w-full" v-model="form.complement"
                                placeholder="Apto 123, Bloco B" />
                            <InputError class="mt-2" :message="form.errors.complement" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <div>
                                <InputLabel for="neighborhood" value="Bairro" />
                                <TextInput id="neighborhood" type="text" class="mt-1 block w-full"
                                    v-model="form.neighborhood" required />
                                <InputError class="mt-2" :message="form.errors.neighborhood" />
                            </div>

                            <div>
                                <InputLabel for="city" value="Cidade" />
                                <TextInput id="city" type="text" class="mt-1 block w-full" v-model="form.city"
                                    required />
                                <InputError class="mt-2" :message="form.errors.city" />
                            </div>

                            <div>
                                <InputLabel for="state" value="Estado" />
                                <TextInput id="state" type="text" class="mt-1 block w-full" v-model="form.state"
                                    required />
                                <InputError class="mt-2" :message="form.errors.state" />
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-between gap-4 mt-8">
                        <SecondaryButton @click="step = 1" type="button" class="w-full sm:w-auto order-2 sm:order-1">
                            Voltar
                        </SecondaryButton>
                        <PrimaryButton :disabled="processing"
                            class="w-full sm:w-auto bg-pink-500 hover:bg-pink-600 order-1 sm:order-2">
                            Próximo
                        </PrimaryButton>
                    </div>
                </form>

                <!-- Etapa 3: Código de Desconto -->
                <form v-if="step === 3" @submit.prevent="submit" class="space-y-6">
                    <div class="bg-gray-50 border border-pink-200 shadow-md rounded-lg p-4 mb-6">
                        <h3 class="text-gray-800 font-medium mb-2">Informações importantes:</h3>
                        <ul class="text-sm text-gray-700 space-y-2">
                            <li class="flex items-start">
                                <svg class="h-5 w-5 text-gray-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="gray"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Seu cupom dará 10% de desconto para suas seguidoras</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-5 w-5 text-gray-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="gray"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>O código não pode começar com a palavra "GUMMY"</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-5 w-5 text-gray-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="gray"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Use seu nome ou apelido para facilitar a identificação</span>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <InputLabel for="coupon_code" value="Código do Cupom" />
                        <div class="relative">
                            <TextInput id="coupon_code" type="text" class="mt-1 block w-full h-12 pr-24 uppercase"
                                v-model="form.coupon_code" @input="handleCouponInput" required placeholder="Ex: MARIA10"
                                :class="{
                                    'border-green-500': couponValidated,
                                    'border-red-500': form.errors.coupon_code
                                }" :disabled="validatingCoupon" />
                            <!-- Botão de Validar -->
                            <button type="button" @click="validateCouponCode(form.coupon_code)"
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 px-3 py-1 bg-pink-500 text-white rounded-md text-sm font-medium hover:bg-pink-600 disabled:opacity-50 disabled:cursor-not-allowed h-8 flex items-center"
                                :disabled="!form.coupon_code || form.coupon_code.length < 4 || form.coupon_code.startsWith('GUMMY') || validatingCoupon">
                                <span v-if="!validatingCoupon">Validar</span>
                                <svg v-else class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.coupon_code" />
                        <!-- Indicador de sucesso -->
                        <p v-if="couponValidated" class="mt-2 text-sm text-green-600 flex items-center">
                            <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Cupom válido!
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-between gap-4 mt-8">
                        <SecondaryButton @click="step = 2" type="button" class="w-full sm:w-auto order-2 sm:order-1">
                            Voltar
                        </SecondaryButton>
                        <PrimaryButton :disabled="processing || !couponValidated" :class="[
                            'w-full sm:w-auto order-1 sm:order-2',
                            couponValidated
                                ? 'bg-pink-500 hover:bg-pink-600 active:bg-pink-700'
                                : 'bg-gray-300 cursor-not-allowed'
                        ]">
                            Finalizar Cadastro
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import { ref, computed, onBeforeUnmount } from 'vue'
import { useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head } from '@inertiajs/vue3'
import { vMask } from 'vue-the-mask'
import axios from 'axios'

const step = ref(1)
const processing = ref(false)
const validatingCoupon = ref(false)
const couponValidated = ref(false)
const showPassword = ref(false)
const passwordStrength = computed(() => {
    let strength = 0
    if (form.password.length >= 8) strength++
    if (/[A-Z]/.test(form.password)) strength++
    if (/[0-9]/.test(form.password)) strength++
    return strength
})

const managerReferral = new URLSearchParams(window.location.search).get('ref');

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    nationality: '',
    cpf: '',
    birth_date: '',
    cep: '',
    street: '',
    number: '',
    complement: '',
    neighborhood: '',
    city: '',
    state: '',
    phone: '',
    social_network: '',
    social_media_username: '',
    coupon_code: '',
    manager_referral: managerReferral
})

const socialMediaPlaceholder = computed(() => {
    switch (form.social_network) {
        case 'instagram':
            return '@seuinstagram';
        case 'tiktok':
            return '@seutiktok';
        case 'kwai':
            return '@seukwai';
        case 'x':
            return '@seutwitter';
        case 'facebook':
            return 'seu.facebook';
        default:
            return '@seuperfil';
    }
})

const nextStep = () => {
    if (step.value === 1) {
        if (form.password !== form.password_confirmation) {
            form.errors.password_confirmation = 'As senhas não conferem'
            return
        }
        step.value = 2
    } else if (step.value === 2) {
        step.value = 3
    }
}

const fetchAddress = async () => {
    if (form.cep.length < 8) return

    const cep = form.cep.replace(/\D/g, '')
    try {
        const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`)
        const data = await response.json()

        if (!data.erro) {
            form.street = data.logradouro
            form.neighborhood = data.bairro
            form.city = data.localidade
            form.state = data.uf
        }
    } catch (error) {
        console.error('Erro ao buscar CEP:', error)
    }
}

const submit = () => {
    if (!couponValidated.value) {
        return;
    }

    form.social_media_username = form.social_media_username.replace('@', '').trim();

    form.coupon_code = form.coupon_code.toUpperCase().trim();

    processing.value = true;
    form.post(route('ambassador.register'), {
        onSuccess: (response) => {
            processing.value = false;
            if (response?.props?.redirect) {
                window.location = response.props.redirect;
            } else {
                window.location = route('ambassador.pending');
            }
        },
        onError: () => {
            processing.value = false;
            couponValidated.value = false;
        },
        preserveScroll: true
    });
}

const handleCouponInput = (event) => {
    const value = event.target.value.toUpperCase()
    form.coupon_code = value

    // Reset validations
    couponValidated.value = false
    form.errors.coupon_code = null

    // Validação inicial do formato
    if (value.startsWith('GUMMY')) {
        form.errors.coupon_code = 'O código não pode começar com GUMMY'
    }
}

const validateCouponCode = async (value) => {
    if (!value) return

    validatingCoupon.value = true
    couponValidated.value = false
    form.errors.coupon_code = null

    try {
        const response = await axios.post(route('check-coupon'), {
            code: value
        })

        if (!response.data.available) {
            form.errors.coupon_code = response.data.message || 'Este código de cupom já está em uso'
            couponValidated.value = false
            return false
        }

        couponValidated.value = true
        return true
    } catch (error) {
        form.errors.coupon_code = 'Erro ao validar o código do cupom'
        couponValidated.value = false
        return false
    } finally {
        validatingCoupon.value = false
    }
}

const togglePassword = () => {
    showPassword.value = !showPassword.value
}

onBeforeUnmount(() => {
    // Remova o debounce pois não é mais necessário
})
</script>

<style scoped>
.bg-pink-500 {
    background-color: #EC4899;
}

.hover\:bg-pink-600:hover {
    background-color: #DB2777;
}
</style>

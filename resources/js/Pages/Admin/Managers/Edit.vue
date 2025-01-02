<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-lg text-gray-800 leading-tight">
                Editar Manager
            </h2>
        </template>

        <div class="py-6 px-4 sm:px-6">
            <div class="max-w-2xl mx-auto">
                <div class="mb-6">
                    <Link :href="route('admin.managers.index')"
                        class="text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    &larr; Voltar para lista
                    </Link>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="name" value="Nome" />
                            <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email"
                                required />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div>
                            <InputLabel for="password" value="Nova Senha (opcional)" />
                            <TextInput id="password" type="password" class="mt-1 block w-full"
                                v-model="form.password" />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div>
                            <InputLabel for="password_confirmation" value="Confirmar Nova Senha" />
                            <TextInput id="password_confirmation" type="password" class="mt-1 block w-full"
                                v-model="form.password_confirmation" />
                            <InputError class="mt-2" :message="form.errors.password_confirmation" />
                        </div>

                        <div>
                            <InputLabel for="discount_code" value="Código de Desconto" />
                            <TextInput id="discount_code" type="text" class="mt-1 block w-full uppercase"
                                v-model="form.discount_code" required
                                @input="form.discount_code = $event.target.value.toUpperCase()" />
                            <InputError class="mt-2" :message="form.errors.discount_code" />
                        </div>

                        <div class="flex justify-end">
                            <PrimaryButton :disabled="form.processing">
                                Salvar Alterações
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    manager: Object
});

const form = useForm({
    name: props.manager.name,
    email: props.manager.email,
    password: '',
    password_confirmation: '',
    discount_code: props.manager.discount_code
});

const submit = () => {
    form.patch(route('admin.managers.update', props.manager.id));
};
</script>

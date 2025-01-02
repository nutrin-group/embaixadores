<template>
    <div class="min-h-screen bg-gray-50">
        <div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
            <div class="sm:mx-auto sm:w-full sm:max-w-md">
                <img class="mx-auto h-12 w-auto" src="/images/adesivos-02.svg" alt="Logo" />
            </div>

            <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                <div class="bg-white py-8 px-4 shadow-sm sm:rounded-lg sm:px-10">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>

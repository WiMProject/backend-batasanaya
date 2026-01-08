<script setup lang="ts">
import { Loader2 } from 'lucide-vue-next'

defineProps<{
    isOpen: boolean
    title: string
    description: string
    isLoading?: boolean
    confirmText?: string
    cancelText?: string
    variant?: 'primary' | 'destructive'
}>()

const emit = defineEmits(['confirm', 'close'])
</script>

<template>
    <Teleport to="body">
        <div v-if="isOpen" class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity animate-in fade-in duration-300" @click="emit('close')"></div>
            
            <!-- Modal Card -->
            <div class="relative w-full max-w-sm rounded-2xl border border-white/10 bg-white dark:bg-zinc-900 p-6 shadow-2xl animate-in zoom-in-95 duration-300 overflow-hidden">
                <!-- Warm/Cozy Gradient Accent -->
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-rose-400 to-orange-400"></div>

                <div class="flex flex-col space-y-3 text-center sm:text-left mt-2">
                    <h3 class="text-xl font-bold tracking-tight text-zinc-800 dark:text-zinc-100">{{ title }}</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed">{{ description }}</p>
                </div>
                
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 gap-3 mt-8">
                    <button @click="emit('close')" class="px-5 py-2.5 text-sm font-semibold text-zinc-600 bg-zinc-100 hover:bg-zinc-200 rounded-xl transition-all duration-200">
                        {{ cancelText || 'Cancel' }}
                    </button>
                    <button 
                        @click="emit('confirm')" 
                        :disabled="isLoading"
                        :class="[
                            'px-5 py-2.5 text-sm font-bold text-white rounded-xl shadow-lg transition-all duration-200 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transform active:scale-95',
                            variant === 'destructive' 
                                ? 'bg-gradient-to-r from-red-500 to-rose-600 hover:shadow-red-500/25' 
                                : 'bg-gradient-to-r from-indigo-500 to-violet-600 hover:shadow-indigo-500/25'
                        ]"
                    >
                        <Loader2 v-if="isLoading" class="w-4 h-4 animate-spin" />
                        {{ confirmText || 'Confirm' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

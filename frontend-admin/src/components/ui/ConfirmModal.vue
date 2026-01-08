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
            <div class="relative w-full max-w-sm rounded-xl border border-border bg-background p-6 shadow-2xl animate-in zoom-in-95 duration-300 overflow-hidden">
                <div class="flex flex-col space-y-3 text-center sm:text-left mt-2">
                    <h3 class="text-lg font-bold tracking-tight text-foreground">{{ title }}</h3>
                    <p class="text-sm text-muted-foreground leading-relaxed">{{ description }}</p>
                </div>
                
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 gap-3 mt-8">
                    <button @click="emit('close')" class="px-4 py-2 text-sm font-medium text-muted-foreground bg-muted/50 hover:bg-muted rounded-lg transition-colors">
                        {{ cancelText || 'Cancel' }}
                    </button>
                    <button 
                        @click="emit('confirm')" 
                        :disabled="isLoading"
                        :class="[
                            'px-4 py-2 text-sm font-bold text-white rounded-lg shadow-sm transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95',
                            variant === 'destructive' 
                                ? 'bg-red-600 hover:bg-red-700' 
                                : 'bg-slate-900 hover:bg-slate-800 dark:bg-slate-50 dark:text-slate-900 dark:hover:bg-slate-200'
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

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
    <div v-if="isOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-background/80 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-lg border bg-card p-6 shadow-lg animate-in fade-in zoom-in-95 duration-200">
            <div class="flex flex-col space-y-2 text-center sm:text-left">
                <h3 class="text-lg font-semibold">{{ title }}</h3>
                <p class="text-sm text-muted-foreground">{{ description }}</p>
            </div>
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 gap-2 mt-6">
                <button @click="emit('close')" class="px-4 py-2 text-sm font-medium border rounded-md hover:bg-muted transition-colors">
                    {{ cancelText || 'Cancel' }}
                </button>
                <button 
                    @click="emit('confirm')" 
                    :disabled="isLoading"
                    :class="[
                        'px-4 py-2 text-sm font-medium rounded-md transition-colors flex items-center justify-center gap-2 disabled:opacity-50',
                        variant === 'destructive' 
                            ? 'bg-destructive text-destructive-foreground hover:bg-destructive/90' 
                            : 'bg-primary text-primary-foreground hover:bg-primary/90'
                    ]"
                >
                    <Loader2 v-if="isLoading" class="w-4 h-4 animate-spin" />
                    {{ confirmText || 'Confirm' }}
                </button>
            </div>
        </div>
    </div>
</template>

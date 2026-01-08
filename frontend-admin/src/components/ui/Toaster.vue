<script setup lang="ts">
import { useToast } from '@/lib/toast'
import { X, CheckCircle, AlertCircle, Info } from 'lucide-vue-next'

const { toasts, remove } = useToast()
</script>

<template>
    <div class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2 w-full max-w-xs sm:max-w-sm pointer-events-none">
        <TransitionGroup 
            enter-active-class="transform ease-out duration-300 transition" 
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2" 
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" 
            leave-active-class="transition ease-in duration-100" 
            leave-from-class="opacity-100" 
            leave-to-class="opacity-0"
        >
            <div 
                v-for="toast in toasts" 
                :key="toast.id" 
                class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-background border shadow-lg ring-1 ring-black ring-opacity-5"
            >
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <CheckCircle v-if="toast.type === 'success'" class="h-6 w-6 text-green-400" aria-hidden="true" />
                            <AlertCircle v-else-if="toast.type === 'error'" class="h-6 w-6 text-red-400" aria-hidden="true" />
                            <Info v-else class="h-6 w-6 text-blue-400" aria-hidden="true" />
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p v-if="toast.title" class="text-sm font-medium text-foreground">{{ toast.title }}</p>
                            <p class="text-sm text-muted-foreground">{{ toast.message }}</p>
                        </div>
                        <div class="ml-4 flex flex-shrink-0">
                            <button type="button" @click="remove(toast.id)" class="inline-flex rounded-md bg-background text-muted-foreground hover:text-foreground focus:outline-none focus:ring-2 focus:ring-offset-2">
                                <span class="sr-only">Close</span>
                                <X class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </TransitionGroup>
    </div>
</template>

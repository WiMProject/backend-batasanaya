import { ref } from 'vue'

export interface Toast {
    id: string
    title?: string
    message: string
    type: 'success' | 'error' | 'info'
    duration?: number
}

const toasts = ref<Toast[]>([])

export const useToast = () => {
    const add = (toast: Omit<Toast, 'id'>) => {
        const id = Math.random().toString(36).substring(2, 9)
        const newToast: Toast = {
            id,
            duration: 3000,
            ...toast
        }
        toasts.value.push(newToast)

        if (newToast.duration) {
            setTimeout(() => {
                remove(id)
            }, newToast.duration)
        }
    }

    const remove = (id: string) => {
        toasts.value = toasts.value.filter(t => t.id !== id)
    }

    const success = (message: string, title?: string) => add({ type: 'success', message, title })
    const error = (message: string, title?: string) => add({ type: 'error', message, title })
    const info = (message: string, title?: string) => add({ type: 'info', message, title })

    return {
        toasts,
        add,
        remove,
        success,
        error,
        info
    }
}

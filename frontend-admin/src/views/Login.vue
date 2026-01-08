<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { LayoutDashboard, Loader2 } from 'lucide-vue-next'

const router = useRouter()
const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
    loading.value = true
    error.value = ''
    try {
        const res = await fetch('/api/auth/admin-login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email.value, password: password.value })
        })
        const data = await res.json()
        
        if (res.ok && data.access_token) {
            localStorage.setItem('adminToken', data.access_token)
            router.push('/')
        } else {
            error.value = data.message || 'Login failed'
        }
    } catch (e) {
        error.value = 'Connection error'
    } finally {
        loading.value = false
    }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-background px-4">
    <div class="w-full max-w-sm space-y-6">
      <div class="text-center space-y-2">
        <div class="h-12 w-12 bg-primary/10 text-primary rounded-lg flex items-center justify-center mx-auto">
            <LayoutDashboard class="w-6 h-6" />
        </div>
        <h1 class="text-2xl font-bold tracking-tight">Admin Portal</h1>
        <p class="text-sm text-muted-foreground">Sign in to manage Batasanaya backend</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <div v-if="error" class="p-3 text-sm text-destructive bg-destructive/10 rounded-md">
            {{ error }}
        </div>
        
        <div class="space-y-2">
            <label class="text-sm font-medium leading-none">Email</label>
            <input 
                v-model="email" 
                type="email" 
                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" 
                placeholder="admin@example.com"
                required
            />
        </div>
        <div class="space-y-2">
            <label class="text-sm font-medium leading-none">Password</label>
            <input 
                v-model="password" 
                type="password" 
                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                required 
            />
        </div>
        
        <button 
            type="submit" 
            :disabled="loading"
            class="inline-flex items-center justify-center w-full h-10 px-4 py-2 text-sm font-medium text-primary-foreground bg-primary rounded-md hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 transition-colors"
        >
            <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
            Sign In
        </button>
      </form>
    </div>
  </div>
</template>

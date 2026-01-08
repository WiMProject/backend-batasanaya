<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Loader2, ArrowRight } from 'lucide-vue-next'

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
            router.push('/dashboard')
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
  <div class="min-h-screen flex items-center justify-center bg-zinc-950 font-sans relative overflow-hidden">
    
    <!-- Background FX -->
    <div class="absolute inset-0 z-0 bg-dot-pattern opacity-20 pointer-events-none"></div>
    <div class="absolute inset-0 z-0 bg-gradient-to-b from-transparent to-black/80 pointer-events-none"></div>

    <!-- Login Card -->
    <div class="w-full max-w-md relative z-10 p-6">
        <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl shadow-2xl p-8 md:p-10 animate-in fade-in zoom-in-95 duration-700">
            
            <div class="text-center space-y-3 mb-8">
                <div class="h-14 w-14 bg-white/10 rounded-2xl flex items-center justify-center mx-auto shadow-lg border border-white/10 mb-4">
                    <span class="text-white font-bold text-2xl">B</span>
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-white">Welcome Back</h1>
                <p class="text-zinc-400">Enter your credentials to access the Batasanaya Core.</p>
            </div>

            <form @submit.prevent="handleLogin" class="space-y-6">
                <div v-if="error" class="p-4 text-sm text-red-200 bg-red-500/20 border border-red-500/30 rounded-xl flex items-center gap-2 animate-in slide-in-from-top-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    {{ error }}
                </div>
                
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-xs font-semibold uppercase tracking-wider text-zinc-500 ml-1">Email</label>
                        <input 
                            v-model="email" 
                            type="email" 
                            class="w-full h-12 rounded-xl border border-white/10 bg-black/20 px-4 text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:bg-black/40 transition-all font-medium" 
                            placeholder="admin@batasanaya.com"
                            required
                        />
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-semibold uppercase tracking-wider text-zinc-500 ml-1">Password</label>
                            <a href="#" class="text-xs text-primary hover:text-primary/80 transition-colors">Forgot?</a>
                        </div>
                        <input 
                            v-model="password" 
                            type="password" 
                            class="w-full h-12 rounded-xl border border-white/10 bg-black/20 px-4 text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:bg-black/40 transition-all font-medium"
                            placeholder="••••••••"
                            required 
                        />
                    </div>
                </div>
                
                <button 
                    type="submit" 
                    :disabled="loading"
                    class="group w-full h-12 flex items-center justify-center gap-2 bg-white text-black hover:bg-zinc-200 font-bold rounded-xl shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <Loader2 v-if="loading" class="w-5 h-5 animate-spin" />
                    <span v-else>Sign In to Dashboard</span>
                    <ArrowRight v-if="!loading" class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-white/5 text-center">
                 <p class="text-xs text-zinc-500">
                    &copy; 2025 Batasanaya Project. Secure Access Only.
                </p>
            </div>
        </div>
    </div>
  </div>
</template>

<style scoped>
.animate-pulse-slow {
    animation: pulse 8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>

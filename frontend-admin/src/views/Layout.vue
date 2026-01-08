<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { LayoutDashboard, Users, FileImage, User, LogOut, Menu, Music, Video, Image as ImageIcon, X, ChevronRight, Sun, Moon } from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()

// Theme Logic
const isDark = ref(false)

const toggleTheme = () => {
    isDark.value = !isDark.value
    if (isDark.value) {
        document.documentElement.classList.add('dark')
        localStorage.setItem('theme', 'dark')
    } else {
        document.documentElement.classList.remove('dark')
        localStorage.setItem('theme', 'light')
    }
}

onMounted(() => {
    const saved = localStorage.getItem('theme')
    const system = window.matchMedia('(prefers-color-scheme: dark)').matches
    if (saved === 'dark' || (!saved && system)) {
        isDark.value = true
        document.documentElement.classList.add('dark')
    }
})

const menuItems = [
  { id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard, path: '/dashboard' },
  { id: 'users', label: 'Users', icon: Users, path: '/users' },
  { id: 'assets', label: 'Assets', icon: FileImage, path: '/assets' },
  { id: 'songs', label: 'Songs', icon: Music, path: '/songs' },
  { id: 'videos', label: 'Videos', icon: Video, path: '/videos' },
  { id: 'backgrounds', label: 'Backgrounds', icon: ImageIcon, path: '/backgrounds' },
]

const handleLogout = () => {
    localStorage.removeItem('adminToken')
    router.push('/login')
}

const activeId = computed(() => {
    const p = route.path
    if (p.includes('dashboard')) return 'dashboard'
    if (p.includes('users')) return 'users'
    if (p.includes('assets')) return 'assets'
    if (p.includes('songs')) return 'songs'
    if (p.includes('videos')) return 'videos'
    if (p.includes('backgrounds')) return 'backgrounds'
    return ''
})

const pageTitle = computed(() => {
    const item = menuItems.find(i => i.id === activeId.value)
    return item ? item.label : 'Admin'
})

const isMobileMenuOpen = ref(false)

// Close menu when route changes
watch(route, () => {
    isMobileMenuOpen.value = false
})
</script>

<template>
  <div class="flex h-screen bg-background text-foreground overflow-hidden font-sans select-none transition-colors duration-300">
    
    <!-- Mobile Backdrop -->
    <div 
        v-if="isMobileMenuOpen" 
        class="fixed inset-0 bg-black/60 z-40 md:hidden backdrop-blur-sm transition-opacity"
        @click="isMobileMenuOpen = false"
    ></div>

    <!-- Sidebar -->
    <!-- Pro Design: Always dark sidebar for contrast -->
    <aside 
        class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-slate-100 flex flex-col transition-transform duration-300 ease-out md:translate-x-0 md:static md:flex shadow-2xl"
        :class="isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="p-6 flex items-center justify-between border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-purple-400 flex items-center justify-center shadow-lg shadow-purple-500/20">
                <span class="font-bold text-white text-lg">B</span>
            </div>
            <div>
                <h1 class="text-lg font-bold tracking-tight text-white leading-none">Batasanaya</h1>
                <p class="text-[11px] text-slate-400 mt-1 uppercase tracking-wider font-semibold">Backend Engine</p>
            </div>
        </div>
        <button @click="isMobileMenuOpen = false" class="md:hidden p-1 text-slate-400 hover:text-white transition-colors">
            <X class="w-5 h-5" />
        </button>
      </div>
      
      <div class="p-4">
          <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-widest mb-2">Main Menu</p>
          <nav class="space-y-1">
            <router-link 
              v-for="item in menuItems" 
              :key="item.id"
              :to="item.path"
              class="group flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 relative overflow-hidden"
              :class="activeId === item.id 
                  ? 'bg-primary text-white shadow-lg shadow-primary/25' 
                  : 'text-slate-400 hover:bg-white/5 hover:text-white'"
            >
              <component :is="item.icon" class="w-5 h-5 transition-transform group-hover:scale-110" :class="activeId === item.id ? 'text-white' : 'text-slate-500 group-hover:text-white'" />
              <span class="relative z-10">{{ item.label }}</span>
              
              <!-- Active Indicator Line -->
              <div v-if="activeId === item.id" class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-white/20 rounded-r-full"></div>
            </router-link>
          </nav>
      </div>

      <div class="mt-auto p-4 border-t border-white/5 bg-black/20">
        <button @click="handleLogout" class="w-full flex items-center gap-3 text-slate-400 hover:bg-white/5 hover:text-red-400 px-4 py-3 rounded-xl transition-all duration-200 group">
            <LogOut class="w-5 h-5 group-hover:-translate-x-1 transition-transform" />
            <span class="font-medium">Sign Out</span>
        </button>
      </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
        
        <!-- Glass Header -->
        <header class="h-16 flex items-center justify-between px-4 md:px-8 border-b bg-background/80 backdrop-blur-xl sticky top-0 z-30 transition-all duration-200">
            <div class="flex items-center gap-4">
                <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="md:hidden p-2 -ml-2 text-muted-foreground hover:text-primary transition-colors">
                    <Menu class="w-6 h-6" />
                </button>
                
                <!-- Breadcrumbs Simulation -->
                <div class="hidden md:flex items-center text-sm text-muted-foreground">
                    <span class="hover:text-foreground transition-colors cursor-pointer">Admin</span>
                    <ChevronRight class="w-4 h-4 mx-2 text-border" />
                    <span class="font-semibold text-foreground bg-primary/10 text-primary px-2 py-0.5 rounded-md">{{ pageTitle }}</span>
                </div>
                <!-- Mobile Title -->
                 <span class="md:hidden font-bold text-foreground">{{ pageTitle }}</span>
            </div>

            <div class="flex items-center gap-4">
                <!-- Theme Toggle -->
                <button @click="toggleTheme" class="p-2 rounded-full text-muted-foreground hover:bg-accent hover:text-accent-foreground transition-all">
                    <Sun v-if="!isDark" class="w-5 h-5" />
                    <Moon v-else class="w-5 h-5" />
                </button>

                <!-- User Profile -->
                <div class="flex items-center gap-3 pl-1">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-semibold text-foreground leading-tight">Administrator</p>
                        <p class="text-[10px] text-muted-foreground">Super Admin</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 border-2 border-white dark:border-slate-700 shadow-sm overflow-hidden transition-transform hover:scale-105 active:scale-95 duration-200">
                        <User class="w-full h-full p-2 text-slate-400 bg-slate-200 dark:bg-slate-700" />
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-background relative transition-colors duration-300">
            <!-- Background Decoration (Tech/Pro Vibe) -->
            <div class="absolute inset-0 pointer-events-none -z-10 bg-dot-pattern opacity-60 dark:opacity-20"></div>

            <div class="max-w-7xl mx-auto w-full pb-10">
                <router-view v-slot="{ Component }">
                    <transition name="slide-fade" mode="out-in">
                        <component :is="Component" />
                    </transition>
                </router-view>
            </div>
        </main>
    </div>
  </div>
</template>

<style>
/* Custom Transitions for Pro Feel */
.slide-fade-enter-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); /* Ease Out Expo */
}

.slide-fade-leave-active {
  transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-fade-enter-from {
  opacity: 0;
  transform: translateY(10px) scale(0.98);
}

.slide-fade-leave-to {
  opacity: 0;
  transform: scale(0.99);
}
</style>

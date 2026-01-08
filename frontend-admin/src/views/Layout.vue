<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { LayoutDashboard, Users, FileImage, User, LogOut, Menu, Music, Video, Image as ImageIcon, X } from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()

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

const isMobileMenuOpen = ref(false)

// Close menu when route changes
watch(route, () => {
    isMobileMenuOpen.value = false
})
</script>

<template>
  <div class="flex h-screen bg-background text-foreground overflow-hidden font-sans">
    
    <!-- Mobile Backdrop -->
    <div 
        v-if="isMobileMenuOpen" 
        class="fixed inset-0 bg-black/50 z-40 md:hidden backdrop-blur-sm transition-opacity"
        @click="isMobileMenuOpen = false"
    ></div>

    <!-- Sidebar -->
    <!-- Mobile: Fixed, slide-in. Desktop: Static, visible. -->
    <aside 
        class="fixed inset-y-0 left-0 z-50 w-64 bg-card border-r border-border flex flex-col transition-transform duration-300 ease-in-out md:translate-x-0 md:static md:flex"
        :class="isMobileMenuOpen ? 'translate-x-0 shadow-xl' : '-translate-x-full'"
    >
      <div class="p-6 border-b border-border flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight">Admin Panel</h1>
            <p class="text-xs text-muted-foreground">Batasanaya Backend</p>
        </div>
        <!-- Close button for mobile -->
        <button @click="isMobileMenuOpen = false" class="md:hidden p-1 text-muted-foreground hover:text-foreground">
            <X class="w-5 h-5" />
        </button>
      </div>
      
      <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
        <router-link 
          v-for="item in menuItems" 
          :key="item.id"
          :to="item.path"
          :class="[
            'w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors',
            activeId === item.id 
              ? 'bg-primary text-primary-foreground shadow-sm' 
              : 'hover:bg-accent hover:text-accent-foreground text-muted-foreground'
          ]"
        >
          <component :is="item.icon" class="w-4 h-4" />
          {{ item.label }}
        </router-link>
      </nav>

      <div class="p-4 border-t border-border mt-auto bg-muted/20">
        <div class="flex items-center gap-3 mb-4 px-2">
            <div class="w-8 h-8 rounded-full bg-muted flex items-center justify-center border border-border">
                <User class="w-4 h-4 text-muted-foreground" />
            </div>
            <div class="overflow-hidden">
                <p class="text-sm font-medium truncate">Administrator</p>
                <p class="text-xs text-muted-foreground truncate">admin@system.com</p>
            </div>
        </div>
        <button @click="handleLogout" class="w-full flex items-center gap-2 text-destructive hover:bg-destructive/10 px-3 py-2 rounded-md transition-colors text-sm">
            <LogOut class="w-4 h-4" />
            Logout
        </button>
      </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Mobile Header -->
        <div class="md:hidden h-16 border-b bg-card flex items-center justify-between px-4 z-30 shrink-0">
            <span class="font-bold">Admin Panel</span>
            <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="p-2 -mr-2 text-muted-foreground hover:text-foreground">
                <Menu class="w-6 h-6" />
            </button>
        </div>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-muted/20 scroll-smooth">
            <div class="max-w-7xl mx-auto w-full">
                <router-view v-slot="{ Component }">
                    <transition name="fade" mode="out-in">
                        <component :is="Component" />
                    </transition>
                </router-view>
            </div>
        </main>
    </div>
  </div>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>

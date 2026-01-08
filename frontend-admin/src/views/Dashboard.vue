<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { api } from '@/lib/api'
import { Users, FileImage, HardDrive, Music, Video, ArrowUpRight, Activity, Clock } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'

const stats = ref<any>({})
const recentUsers = ref<any[]>([])
const recentAssets = ref<any[]>([])
const loading = ref(true)

const greeting = computed(() => {
    const hour = new Date().getHours()
    if (hour < 12) return 'Good morning'
    if (hour < 18) return 'Good afternoon'
    return 'Good evening'
})

const fetchDashboard = async () => {
    try {
        const res = await api.get('/admin/stats')
        if (res.ok) stats.value = await res.json()
        
        const uRes = await api.get('/admin/users?page=1') // Using standard pagination endpoint
        if (uRes.ok) {
             const d = await uRes.json()
             recentUsers.value = d.data ? d.data.slice(0, 5) : []
        }
        
        // Use generic assets endpoint or similar
        const aRes = await api.get('/assets?page=1') 
        if (aRes.ok) {
             const d = await aRes.json()
             recentAssets.value = d.data ? d.data.slice(0, 5) : []
        }
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
}

onMounted(fetchDashboard)
</script>

<template>
    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        
        <!-- Welcome Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 to-violet-950 p-8 md:p-10 text-white shadow-2xl shadow-slate-900/20">
            <div class="absolute top-0 right-0 -mt-10 -mr-10 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-10 -ml-10 h-40 w-40 rounded-full bg-black/10 blur-2xl"></div>
            
            <div class="relative z-10">
                <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight mb-2">{{ greeting }}, Admin.</h1>
                <p class="text-primary-foreground/80 md:text-lg max-w-2xl">
                    Welcome to the Batasanaya Command Center. Connectivity is optimal. Systems are running smoothly.
                </p>
                
                <div class="flex gap-4 mt-6">
                    <button class="px-5 py-2.5 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-xl font-medium text-sm transition-colors border border-white/10 flex items-center gap-2">
                        <Activity class="w-4 h-4" />
                        System Health: 100%
                    </button>
                    <RouterLink to="/users" class="px-5 py-2.5 bg-white text-primary hover:bg-gray-100 rounded-xl font-bold text-sm transition-colors shadow-lg shadow-black/10 flex items-center gap-2">
                        View Reports
                        <ArrowUpRight class="w-4 h-4" />
                    </RouterLink>
                </div>
            </div>
        </div>

        <!-- Metric Grid -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
             <!-- Card 1 -->
            <div class="relative group p-6 rounded-3xl bg-card border border-border/50 shadow-sm hover:shadow-xl hover:shadow-primary/5 transition-all duration-300 overflow-hidden">
                 <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                 <div class="relative flex justify-between items-start">
                     <div>
                         <p class="text-sm font-medium text-muted-foreground uppercase tracking-wider">Total Users</p>
                         <h3 class="text-3xl font-bold mt-2 text-foreground">{{ stats.users || 0 }}</h3>
                     </div>
                     <div class="p-3 bg-primary/10 rounded-2xl text-primary group-hover:scale-110 transition-transform">
                         <Users class="w-6 h-6" />
                     </div>
                 </div>
                 <div class="mt-4 flex items-center text-xs font-medium text-green-500 bg-green-500/10 w-fit px-2 py-1 rounded-lg">
                     <ArrowUpRight class="w-3 h-3 mr-1" />
                     +12% from last month
                 </div>
            </div>

             <!-- Card 2 -->
            <div class="relative group p-6 rounded-3xl bg-card border border-border/50 shadow-sm hover:shadow-xl hover:shadow-blue-500/5 transition-all duration-300 overflow-hidden">
                 <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                 <div class="relative flex justify-between items-start">
                     <div>
                         <p class="text-sm font-medium text-muted-foreground uppercase tracking-wider">Total Assets</p>
                         <h3 class="text-3xl font-bold mt-2 text-foreground">{{ stats.assets || 0 }}</h3>
                     </div>
                     <div class="p-3 bg-blue-500/10 rounded-2xl text-blue-500 group-hover:scale-110 transition-transform">
                         <FileImage class="w-6 h-6" />
                     </div>
                 </div>
                 <div class="mt-4 flex items-center gap-3">
                     <div class="flex items-center gap-1 text-xs text-muted-foreground bg-muted/50 px-2 py-1 rounded-md">
                         <Music class="w-3 h-3" /> {{ stats.songs || 0 }}
                     </div>
                     <div class="flex items-center gap-1 text-xs text-muted-foreground bg-muted/50 px-2 py-1 rounded-md">
                         <Video class="w-3 h-3" /> {{ stats.videos || 0 }}
                     </div>
                 </div>
            </div>

             <!-- Card 3 -->
            <div class="relative group p-6 rounded-3xl bg-card border border-border/50 shadow-sm hover:shadow-xl hover:shadow-orange-500/5 transition-all duration-300 overflow-hidden">
                 <div class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                 <div class="relative flex justify-between items-start">
                     <div>
                         <p class="text-sm font-medium text-muted-foreground uppercase tracking-wider">Storage Usage</p>
                         <h3 class="text-3xl font-bold mt-2 text-foreground">{{ stats.storage_mb || 0 }} <span class="text-lg text-muted-foreground font-normal">MB</span></h3>
                     </div>
                     <div class="p-3 bg-orange-500/10 rounded-2xl text-orange-500 group-hover:scale-110 transition-transform">
                         <HardDrive class="w-6 h-6" />
                     </div>
                 </div>
                 <div class="mt-4 w-full bg-muted/40 h-2 rounded-full overflow-hidden">
                     <div class="bg-orange-500 h-full rounded-full w-[45%]"></div>
                 </div>
            </div>
        </div>

        <!-- Bento Grid Bottom -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-7">
            <!-- Recent Users Widget -->
            <div class="col-span-4 rounded-3xl border border-border/50 bg-card shadow-sm overflow-hidden flex flex-col">
                <div class="p-6 border-b border-border/50 flex items-center justify-between bg-muted/10">
                    <h3 class="font-bold flex items-center gap-2">
                        <Users class="w-4 h-4 text-primary" />
                        Fresh Recruits
                    </h3>
                    <RouterLink to="/users" class="text-xs font-medium text-primary hover:underline">View All</RouterLink>
                </div>
                <div class="flex-1 p-0 overflow-y-auto max-h-[400px]">
                    <div v-if="loading" class="p-10 flex justify-center text-muted-foreground">Loading...</div>
                    <div v-else class="divide-y divide-border/40">
                        <div v-for="u in recentUsers" :key="u.id" class="p-4 flex items-center gap-4 hover:bg-muted/30 transition-colors group cursor-default">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-primary to-purple-400 p-[2px] shadow-sm">
                                <div class="w-full h-full rounded-full bg-card flex items-center justify-center text-xs font-bold uppercase tracking-wider">
                                     {{ u.full_name?.substring(0,2) }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-foreground group-hover:text-primary transition-colors">{{ u.full_name }}</p>
                                <p class="text-xs text-muted-foreground">{{ u.email }}</p>
                            </div>
                            <div class="text-xs text-muted-foreground flex items-center gap-1">
                                <Clock class="w-3 h-3" />
                                {{ new Date(u.created_at).toLocaleDateString() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Assets Widget -->
            <div class="col-span-3 rounded-3xl border border-border/50 bg-card shadow-sm overflow-hidden flex flex-col">
                <div class="p-6 border-b border-border/50 flex items-center justify-between bg-muted/10">
                    <h3 class="font-bold flex items-center gap-2">
                        <FileImage class="w-4 h-4 text-blue-500" />
                        Latest Uploads
                    </h3>
                    <RouterLink to="/assets" class="text-xs font-medium text-primary hover:underline">All Assets</RouterLink>
                </div>
                <div class="flex-1 p-0 overflow-y-auto max-h-[400px]">
                    <div v-if="loading" class="p-10 flex justify-center text-muted-foreground">Loading...</div>
                    <div v-else class="divide-y divide-border/40">
                        <div v-for="a in recentAssets" :key="a.id" class="p-3 flex items-center gap-3 hover:bg-muted/30 transition-colors">
                            <div class="h-12 w-16 rounded-lg bg-secondary overflow-hidden flex-shrink-0 border border-border/50">
                                <img v-if="a.type === 'image' || a.category === 'background'" :src="`/api/assets/${a.id}/file`" class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center bg-muted">
                                    <Music v-if="a.category === 'audio' || a.title" class="w-5 h-5 text-muted-foreground" />
                                    <Video v-else class="w-5 h-5 text-muted-foreground" />
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium truncate">{{ a.file_name || a.title }}</p>
                                <p class="text-xs text-muted-foreground uppercase tracking-wider font-semibold">{{ a.type || 'Asset' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

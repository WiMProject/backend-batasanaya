<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { api } from '@/lib/api'
import { Users, FileImage, HardDrive, Music, Video, Image } from 'lucide-vue-next'

const stats = ref<any>({})
const recentUsers = ref<any[]>([])
const recentAssets = ref<any[]>([])
const loading = ref(true)

const fetchDashboard = async () => {
    try {
        const res = await api.get('/admin/stats')
        if (res.ok) stats.value = await res.json()
        
        const uRes = await api.get('/admin/users?limit=5')
        if (uRes.ok) {
             const d = await uRes.json()
             recentUsers.value = d.data || []
        }
        
        const aRes = await api.get('/admin/assets?limit=5')
        if (aRes.ok) {
             const d = await aRes.json()
             recentAssets.value = d.data || []
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
    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
        <div>
            <h2 class="text-3xl font-bold tracking-tight">Dashboard</h2>
            <p class="text-muted-foreground">Overview of system metrics.</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div class="p-6 rounded-xl border bg-card text-card-foreground shadow-sm">
                 <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                     <h3 class="tracking-tight text-sm font-medium">Total Users</h3>
                     <Users class="w-4 h-4 text-muted-foreground" />
                 </div>
                 <div class="text-2xl font-bold">{{ stats.users || 0 }}</div>
            </div>
            <div class="p-6 rounded-xl border bg-card text-card-foreground shadow-sm">
                 <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                     <h3 class="tracking-tight text-sm font-medium">Total Assets</h3>
                     <FileImage class="w-4 h-4 text-muted-foreground" />
                 </div>
                 <div class="text-2xl font-bold">{{ stats.assets || 0 }}</div>
            </div>
            <div class="p-6 rounded-xl border bg-card text-card-foreground shadow-sm">
                 <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                     <h3 class="tracking-tight text-sm font-medium">Storage Used</h3>
                     <HardDrive class="w-4 h-4 text-muted-foreground" />
                 </div>
                 <div class="text-2xl font-bold">{{ stats.storage_mb || 0 }} MB</div>
            </div>
        </div>
        
        <div class="grid gap-4 md:grid-cols-3">
             <div class="p-4 rounded-xl border bg-card/50 text-card-foreground shadow-sm flex items-center gap-4">
                 <div class="p-2 bg-blue-500/10 text-blue-500 rounded-lg"><Music class="w-5 h-5"/></div>
                 <div>
                     <p class="text-sm font-medium text-muted-foreground">Songs</p>
                     <p class="text-xl font-bold">{{ stats.songs || 0 }}</p>
                 </div>
             </div>
             <div class="p-4 rounded-xl border bg-card/50 text-card-foreground shadow-sm flex items-center gap-4">
                 <div class="p-2 bg-purple-500/10 text-purple-500 rounded-lg"><Video class="w-5 h-5"/></div>
                 <div>
                     <p class="text-sm font-medium text-muted-foreground">Videos</p>
                     <p class="text-xl font-bold">{{ stats.videos || 0 }}</p>
                 </div>
             </div>
             <div class="p-4 rounded-xl border bg-card/50 text-card-foreground shadow-sm flex items-center gap-4">
                 <div class="p-2 bg-orange-500/10 text-orange-500 rounded-lg"><Image class="w-5 h-5"/></div>
                 <div>
                     <p class="text-sm font-medium text-muted-foreground">Backgrounds</p>
                     <p class="text-xl font-bold">{{ stats.backgrounds || 0 }}</p>
                 </div>
             </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
            <div class="col-span-4 rounded-xl border bg-card text-card-foreground shadow-sm">
                <div class="p-6 border-b">
                    <h3 class="font-semibold">Recent Users</h3>
                </div>
                <div class="p-0">
                    <div v-if="loading" class="p-6 text-center text-muted-foreground">Loading...</div>
                    <div v-else class="divide-y">
                        <div v-for="u in recentUsers" :key="u.id" class="p-4 flex items-center justify-between hover:bg-muted/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs uppercase">
                                    {{ u.full_name?.substring(0,2) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium">{{ u.full_name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ u.email }}</p>
                                </div>
                            </div>
                            <span class="text-xs text-muted-foreground">{{ new Date(u.created_at).toLocaleDateString() }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-span-3 rounded-xl border bg-card text-card-foreground shadow-sm">
                <div class="p-6 border-b">
                    <h3 class="font-semibold">Recent Assets</h3>
                </div>
                <div class="p-0">
                    <div v-if="loading" class="p-6 text-center text-muted-foreground">Loading...</div>
                    <div v-else class="divide-y h-full overflow-hidden">
                        <div v-for="a in recentAssets" :key="a.id" class="p-4 flex items-center gap-3 hover:bg-muted/50 transition-colors">
                            <div class="w-10 h-10 rounded-md border bg-muted flex-shrink-0 flex items-center justify-center overflow-hidden">
                                <img v-if="a.type === 'image' || a.type === 'background'" :src="`/api/assets/${a.id}/file`" class="w-full h-full object-cover" />
                                <Music v-else-if="a.type === 'audio'" class="w-5 h-5 text-muted-foreground" />
                                <Video v-else class="w-5 h-5 text-muted-foreground" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium truncate">{{ a.file_name }}</p>
                                <p class="text-xs text-muted-foreground uppercase">{{ a.type }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { api } from '@/lib/api'
import { useToast } from '@/lib/toast'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import { Loader2, Search, Trash2, Edit, Gamepad2, X, Trophy, CheckCircle, Lock, Unlock, Clock, User as UserIcon, UserPlus } from 'lucide-vue-next'

const users = ref<any[]>([])
const loading = ref(true)
const search = ref('')
const toast = useToast()

// Confirmation State
const confirmState = ref({
    isOpen: false,
    title: '',
    description: '',
    isLoading: false,
    variant: 'primary' as 'primary' | 'destructive',
    onConfirm: async () => {}
})

const openConfirm = (title: string, description: string, onConfirm: () => Promise<void> | void, variant: 'primary' | 'destructive' = 'primary') => {
    confirmState.value = {
        isOpen: true,
        title,
        description,
        isLoading: false,
        variant,
        onConfirm: async () => { await onConfirm() }
    }
}

const handleConfirm = async () => {
    confirmState.value.isLoading = true
    try {
        await confirmState.value.onConfirm()
        confirmState.value.isOpen = false
    } catch (e) {
        console.error(e)
    } finally {
        confirmState.value.isLoading = false
    }
}

// Game Data Modal State
const isGameModalOpen = ref(false)
const selectedUser = ref<any>(null)
const gameData = ref<any>(null)
const loadingGameData = ref(false)
const activeGameTab = ref<'carihijaiyah' | 'pasangkanhuruf'>('carihijaiyah')

// Add User Modal State
const isAddUserOpen = ref(false)
const isCreatingUser = ref(false)
const addUserForm = ref({
    fullName: '',
    email: '',
    phone_number: '',
    password: '',
    role: 'user'
})

// Edit User Modal State
const isEditUserOpen = ref(false)
const isUpdatingUser = ref(false)
const editUserForm = ref({
    id: '',
    fullName: '',
    email: '',
    phone_number: ''
})

const fetchUsers = async () => {
    loading.value = true
    try {
        const query = search.value ? `?search=${search.value}` : ''
        // Use the admin endpoint for users
        const res = await api.get(`/admin/users${query}`)
        if (res.ok) {
            const data = await res.json()
            users.value = data.data || []
        }
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
}

let timeout: any
const onSearch = () => {
    clearTimeout(timeout)
    timeout = setTimeout(fetchUsers, 500)
}

// Delete Logic
const openDeleteModal = (id: string) => {
    openConfirm('Delete User', 'Are you sure you want to delete this user? This action cannot be undone.', async () => {
        try {
            const res = await api.delete(`/users/${id}`)
            if (res.ok) {
                fetchUsers()
                toast.success('User deleted successfully')
            } else {
                const data = await res.json()
                toast.error(data.message || data.error || 'Failed to delete user')
            }
        } catch (e) {
            console.error(e)
            toast.error('Error deleting user')
        }
    }, 'destructive')
}

const openEditUser = (user: any) => {
    editUserForm.value = {
        id: user.id,
        fullName: user.full_name,
        email: user.email,
        phone_number: user.phone_number
    }
    isEditUserOpen.value = true
}

const updateUser = async () => {
    if (!editUserForm.value.fullName || !editUserForm.value.email) {
        toast.error("Name and Email are required")
        return
    }

    openConfirm('Confirm Update', 'Are you sure you want to save changes?', async () => {
        isUpdatingUser.value = true
        try {
            const payload = {
                fullName: editUserForm.value.fullName,
                email: editUserForm.value.email,
                _method: 'PATCH'
            }
            
            const res = await api.post(`/users/${editUserForm.value.id}`, payload)

            if (res.ok) {
                isEditUserOpen.value = false
                toast.success('User updated successfully')
                fetchUsers()
            } else {
                 const data = await res.json()
                 toast.error(data.message || data.error || 'Update failed')
            }

        } catch (e) {
            console.error(e)
            toast.error('An error occurred during update')
        } finally {
            isUpdatingUser.value = false
        }
    })
}

const createUser = async () => {
    if (!addUserForm.value.fullName || !addUserForm.value.email || !addUserForm.value.password || !addUserForm.value.phone_number) {
        toast.error("Please fill all required fields.")
        return
    }

    openConfirm('Confirm Create', 'Are you sure you want to create this user?', async () => {
        isCreatingUser.value = true
        try {
            const res = await api.post('/users', addUserForm.value)
            if (res.ok) {
                isAddUserOpen.value = false
                addUserForm.value = {
                    fullName: '',
                    email: '',
                    phone_number: '',
                    password: '',
                    role: 'user'
                }
                toast.success("User created successfully!")
                fetchUsers()
            } else {
                const data = await res.json()
                toast.error(data.message || data.error || 'Failed to create user')
            }
        } catch (e) {
            console.error(e)
            toast.error("Error creating user")
        } finally {
            isCreatingUser.value = false
        }
    })
}

const openGameData = async (user: any) => {
    selectedUser.value = user
    isGameModalOpen.value = true
    loadingGameData.value = true
    gameData.value = null
    activeGameTab.value = 'carihijaiyah' // Default Tab
    
    try {
        const res = await api.get(`/admin/users/${user.id}/game-progress`)
        if (res.ok) {
            const data = await res.json()
            gameData.value = data
        }
    } catch (e) {
        console.error(e)
    } finally {
        loadingGameData.value = false
    }
}

const getInitials = (name: string) => name ? name.substring(0, 2).toUpperCase() : '??'

const formatDate = (dateString: string) => {
    if (!dateString) return '-'
    return new Date(dateString).toLocaleDateString() + ' ' + new Date(dateString).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})
}

onMounted(fetchUsers)
</script>

<template>
    <div class="space-y-8 animate-in fade-in duration-500">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-6 border-b border-border/40">
            <div>
                 <h2 class="text-3xl font-bold tracking-tight text-foreground">User Management</h2>
                <p class="text-muted-foreground mt-2 text-lg">Oversee all registered players and administrators.</p>
            </div>
            <button @click="isAddUserOpen = true" class="inline-flex items-center justify-center h-10 px-6 py-2 text-sm font-medium text-white transition-colors bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm dark:bg-slate-50 dark:text-slate-900 dark:hover:bg-slate-200">
                <UserPlus class="w-4 h-4 mr-2" />
                Add New User
            </button>
        </div>

        <!-- Toolbar -->
         <div class="relative w-full max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Search class="h-5 w-5 text-muted-foreground" />
            </div>
            <input 
                v-model="search"
                @input="onSearch"
                type="text"
                class="block w-full pl-10 pr-3 py-3 border border-input rounded-xl leading-5 bg-card/50 backdrop-blur-sm placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm transition-all shadow-sm"
                placeholder="Find users by name or email..."
            />
        </div>

        <!-- Users Table -->
        <div class="rounded-2xl border bg-card/50 backdrop-blur-sm shadow-sm overflow-hidden">
            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="bg-muted/30">
                        <tr class="border-b transition-colors">
                            <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider text-xs">User Profile</th>
                            <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider text-xs">Role</th>
                            <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider text-xs">Game Progress</th>
                            <th class="h-12 px-6 text-left align-middle font-semibold text-muted-foreground uppercase tracking-wider text-xs">Joined Date</th>
                            <th class="h-12 px-6 text-right align-middle font-semibold text-muted-foreground uppercase tracking-wider text-xs">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/50">
                        <tr v-if="loading">
                             <td colspan="5" class="h-32 text-center">
                                 <div class="flex items-center justify-center gap-2 text-muted-foreground">
                                     <Loader2 class="h-5 w-5 animate-spin" /> Loading data...
                                 </div>
                             </td>
                        </tr>
                        <tr v-else-if="users.length === 0">
                             <td colspan="5" class="h-32 text-center text-muted-foreground">No users found matching your search.</td>
                        </tr>
                        <tr v-else v-for="user in users" :key="user.id" class="transition-colors hover:bg-muted/40 group">
                            <td class="p-4 px-6 align-middle">
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-primary to-purple-400 p-[2px] shadow-sm">
                                        <div class="w-full h-full rounded-full bg-card flex items-center justify-center font-bold text-sm text-foreground">
                                            {{ getInitials(user.full_name) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-foreground group-hover:text-primary transition-colors">{{ user.full_name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ user.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 px-6 align-middle">
                                <span 
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset transition-colors"
                                    :class="user.role?.name === 'admin' ? 'bg-purple-50 text-purple-700 ring-purple-600/20 dark:bg-purple-900/30 dark:text-purple-300 dark:ring-purple-400/30' : 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-400/30'"
                                >
                                    {{ user.role?.name || 'User' }}
                                </span>
                            </td>
                             <td class="p-4 px-6 align-middle">
                                <button @click="openGameData(user)" class="text-xs font-medium border border-input bg-background/50 hover:bg-accent hover:text-accent-foreground rounded-lg px-3 py-1.5 flex items-center gap-2 transition-all shadow-sm">
                                    <Gamepad2 class="w-3.5 h-3.5" />
                                    View Stats
                                </button>
                            </td>
                            <td class="p-4 px-6 align-middle text-muted-foreground text-sm">
                                <div class="flex items-center gap-2">
                                    <Clock class="w-3.5 h-3.5" />
                                    {{ new Date(user.created_at).toLocaleDateString() }}
                                </div>
                            </td>
                            <td class="p-4 px-6 align-middle text-right">
                                <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                     <button @click="openEditUser(user)" class="p-2 hover:bg-blue-50 text-muted-foreground hover:text-blue-600 rounded-lg transition-colors" title="Edit User">
                                        <Edit class="h-4 w-4" />
                                    </button>
                                     <button @click="openDeleteModal(user.id)" class="p-2 hover:bg-red-50 text-muted-foreground hover:text-red-600 rounded-lg transition-colors" title="Delete User">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add User Modal -->
        <Teleport to="body">
            <div v-if="isAddUserOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
                <div class="w-full max-w-lg rounded-2xl border bg-card p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-foreground">Add New User</h3>
                        <button @click="isAddUserOpen = false" class="p-2 hover:bg-muted rounded-full text-muted-foreground transition-colors">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="space-y-5">
                        <div class="space-y-2">
                             <label class="text-sm font-semibold">Full Name</label>
                             <div class="relative">
                                <UserIcon class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                <input v-model="addUserForm.fullName" type="text" placeholder="John Doe" class="flex h-10 w-full rounded-xl border border-input bg-muted/30 pl-9 pr-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 transition-all" />
                             </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                 <label class="text-sm font-semibold">Email</label>
                                 <input v-model="addUserForm.email" type="email" placeholder="john@example.com" class="flex h-10 w-full rounded-xl border border-input bg-muted/30 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 transition-all" />
                            </div>
                            <div class="space-y-2">
                                 <label class="text-sm font-semibold">Phone</label>
                                 <input v-model="addUserForm.phone_number" type="tel" placeholder="08..." class="flex h-10 w-full rounded-xl border border-input bg-muted/30 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 transition-all" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                 <label class="text-sm font-semibold">Password</label>
                                 <input v-model="addUserForm.password" type="password" placeholder="Min. 6 chars" class="flex h-10 w-full rounded-xl border border-input bg-muted/30 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 transition-all" />
                            </div>
                            <div class="space-y-2">
                                 <label class="text-sm font-semibold">Role</label>
                                 <select v-model="addUserForm.role" class="flex h-10 w-full rounded-xl border border-input bg-muted/30 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 transition-all appearance-none">
                                     <option value="user">User</option>
                                     <option value="admin">Admin</option>
                                 </select>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-6">
                            <button @click="isAddUserOpen = false" class="px-5 py-2.5 text-sm font-medium border rounded-xl hover:bg-muted transition-colors">
                                Cancel
                            </button>
                            <button 
                                @click="createUser" 
                                :disabled="isCreatingUser"
                                class="px-5 py-2.5 text-sm font-bold bg-primary text-white rounded-xl hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center gap-2 disabled:opacity-50"
                            >
                                <Loader2 v-if="isCreatingUser" class="w-4 h-4 animate-spin" />
                                {{ isCreatingUser ? 'Creating...' : 'Create Account' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Edit User Modal -->
        <Teleport to="body">
            <div v-if="isEditUserOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
                <div class="w-full max-w-lg rounded-2xl border bg-card p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold">Edit User</h3>
                        <button @click="isEditUserOpen = false" class="p-2 hover:bg-muted rounded-full text-muted-foreground transition-colors">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="space-y-5">
                        <div class="space-y-2">
                             <label class="text-sm font-semibold">Full Name</label>
                             <input v-model="editUserForm.fullName" type="text" class="flex h-10 w-full rounded-xl border border-input bg-muted/30 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 transition-all" />
                        </div>

                        <div class="space-y-2">
                             <label class="text-sm font-semibold">Email</label>
                             <input v-model="editUserForm.email" type="email" class="flex h-10 w-full rounded-xl border border-input bg-muted/30 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 transition-all" />
                        </div>
                        
                        <div class="space-y-2">
                             <label class="text-sm font-semibold text-muted-foreground">Phone Number (Locked)</label>
                             <input :value="editUserForm.phone_number" disabled type="text" class="flex h-10 w-full rounded-xl border border-input bg-muted/50 px-3 py-2 text-sm text-muted-foreground cursor-not-allowed opacity-70" />
                        </div>

                        <div class="flex justify-end gap-3 pt-6">
                            <button @click="isEditUserOpen = false" class="px-5 py-2.5 text-sm font-medium border rounded-xl hover:bg-muted transition-colors">
                                Cancel
                            </button>
                            <button 
                                @click="updateUser" 
                                :disabled="isUpdatingUser"
                                class="px-5 py-2.5 text-sm font-bold bg-primary text-white rounded-xl hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center gap-2 disabled:opacity-50"
                            >
                                <Loader2 v-if="isUpdatingUser" class="w-4 h-4 animate-spin" />
                                {{ isUpdatingUser ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Game Data Modal -->
        <Teleport to="body">
            <div v-if="isGameModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
                <div class="w-full max-w-lg md:max-w-3xl lg:max-w-4xl max-h-[90vh] flex flex-col rounded-3xl border bg-card shadow-2xl animate-in fade-in zoom-in-95 duration-200 overflow-hidden">
                    <!-- Header -->
                    <div class="p-6 border-b flex items-center justify-between bg-muted/20">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                                <Trophy class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="text-xl font-bold">Game Analysis</h3>
                                <p class="text-sm text-muted-foreground">Detailed progress for <span class="text-foreground font-medium">{{ selectedUser?.full_name }}</span></p>
                            </div>
                        </div>
                        <button @click="isGameModalOpen = false" class="p-2 hover:bg-muted rounded-full text-muted-foreground transition-colors">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Tabs -->
                    <div v-if="!loadingGameData && gameData" class="flex border-b px-6 bg-card shrink-0">
                        <button 
                            @click="activeGameTab = 'carihijaiyah'" 
                            :class="['px-6 py-4 text-sm font-semibold border-b-2 transition-all', activeGameTab === 'carihijaiyah' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground']">
                            Cari Hijaiyyah
                        </button>
                        <button 
                             @click="activeGameTab = 'pasangkanhuruf'"
                             :class="['px-6 py-4 text-sm font-semibold border-b-2 transition-all', activeGameTab === 'pasangkanhuruf' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground']">
                            Pasangkan Huruf
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 overflow-y-auto p-8 space-y-10 bg-slate-50/50">
                        <div v-if="loadingGameData" class="flex flex-col items-center justify-center h-64 gap-4">
                            <Loader2 class="w-10 h-10 animate-spin text-primary" />
                            <p class="text-muted-foreground font-medium">Fetching game records...</p>
                        </div>
                        
                        <div v-else-if="gameData" class="space-y-8 animate-in slide-in-from-bottom-2 duration-500">
                             <!-- Stats Summary Cards -->
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="relative overflow-hidden p-6 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 text-white shadow-lg shadow-indigo-500/20">
                                    <div class="relative z-10">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-white/80">Levels Completed</p>
                                        <div class="flex items-baseline gap-2 mt-2">
                                            <span class="text-4xl font-extrabold">{{ gameData[activeGameTab].stats.total_completed }}</span>
                                            <span class="text-lg opacity-60">/ 15</span>
                                        </div>
                                        <div class="w-full bg-black/20 h-1.5 rounded-full mt-4 overflow-hidden">
                                            <div class="bg-white h-full rounded-full" :style="`width: ${(gameData[activeGameTab].stats.total_completed / 15) * 100}%`"></div>
                                        </div>
                                    </div>
                                    <Trophy class="absolute right-4 bottom-4 w-24 h-24 text-white/10 rotate-12" />
                                </div>

                                <div class="p-6 rounded-2xl bg-card border border-border flex items-center gap-6 shadow-sm">
                                    <div class="h-14 w-14 rounded-xl bg-orange-100 flex items-center justify-center text-orange-600">
                                        <Gamepad2 class="w-7 h-7" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Total Sessions Played</p>
                                        <p class="text-3xl font-extrabold text-foreground mt-1">{{ gameData[activeGameTab].stats.total_sessions }}</p>
                                    </div>
                                </div>
                             </div>

                             <!-- Level Grid -->
                             <div class="space-y-4">
                                <h4 class="font-bold text-lg flex items-center gap-2">
                                    <Trophy class="w-5 h-5 text-yellow-500" />
                                    Level Progress
                                </h4>
                                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                                    <div 
                                        v-for="level in gameData[activeGameTab].progress" 
                                        :key="level.level_number" 
                                        class="relative group rounded-xl border p-3 flex flex-col items-center justify-center gap-2 transition-all hover:shadow-md h-24"
                                        :class="[
                                            level.is_completed ? 'bg-green-50 border-green-200' : 
                                            level.is_unlocked ? 'bg-blue-50 border-blue-200' : 'bg-gray-50 border-gray-200 opacity-60'
                                        ]"
                                    >
                                        <div class="absolute top-2 left-2 text-[10px] font-bold uppercase tracking-wider opacity-50">#{{ level.level_number }}</div>
                                        
                                        <div v-if="level.is_completed" class="h-8 w-8 rounded-full bg-green-500 text-white flex items-center justify-center shadow-sm mt-1">
                                            <CheckCircle class="w-5 h-5" />
                                        </div>
                                        <div v-else-if="level.is_unlocked" class="h-8 w-8 rounded-full bg-blue-500 text-white flex items-center justify-center shadow-sm animate-pulse mt-1">
                                            <Unlock class="w-4 h-4" />
                                        </div>
                                        <div v-else class="h-8 w-8 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center mt-1">
                                            <Lock class="w-4 h-4" />
                                        </div>

                                        <div class="text-[10px] font-medium text-muted-foreground">
                                            {{ level.attempts }} tries
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Sessions Table -->
                             <div class="space-y-4">
                                <h4 class="font-bold text-lg flex items-center gap-2">
                                    <Clock class="w-5 h-5 text-blue-500" />
                                    Recent Activity Log
                                </h4>
                                <div class="rounded-xl border bg-card overflow-hidden shadow-sm">
                                    <table class="w-full text-sm">
                                        <thead class="bg-muted/30">
                                            <tr>
                                                <th class="p-4 text-left font-semibold w-24">Level</th>
                                                <th class="p-4 text-left font-semibold">Completed At</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y">
                                            <tr v-for="session in gameData[activeGameTab].recent_sessions" :key="session.id" class="hover:bg-muted/50 transition-colors">
                                                <td class="p-4 font-bold text-primary">Map #{{ session.level_number }}</td>
                                                <td class="p-4 text-muted-foreground">{{ formatDate(session.completed_at) }}</td>
                                            </tr>
                                            <tr v-if="gameData[activeGameTab].recent_sessions.length === 0">
                                                <td colspan="2" class="p-8 text-center text-muted-foreground italic">
                                                    No recent activity recorded.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border-t bg-muted/20 text-center text-xs text-muted-foreground">
                        Data is synced in real-time.
                    </div>
                </div>
            </div>
        </Teleport>

        <ConfirmModal 
            :is-open="confirmState.isOpen"
            :title="confirmState.title"
            :description="confirmState.description"
            :is-loading="confirmState.isLoading"
            :variant="confirmState.variant"
            @confirm="handleConfirm"
            @close="confirmState.isOpen = false"
        />
    </div>
</template>

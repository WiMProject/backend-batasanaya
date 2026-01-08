<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { api } from '@/lib/api'
import { useToast } from '@/lib/toast'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import { Loader2, Search, Trash2, Edit, Gamepad2, X, Trophy, Plus } from 'lucide-vue-next'

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
    <div class="space-y-6 relative">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight">Users</h2>
                <p class="text-muted-foreground">Manage registered users & game progress.</p>
            </div>
            <button @click="isAddUserOpen = true" class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium text-primary-foreground bg-primary rounded-md hover:bg-primary/90 transition-colors w-full md:w-auto">
                <Plus class="w-4 h-4 mr-2" />
                Add User
            </button>
        </div>

        <div class="rounded-xl border bg-card text-card-foreground shadow-sm">
            <div class="p-6 flex items-center justify-between border-b gap-4">
                 <div class="relative w-full max-w-sm">
                     <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                     <input 
                        v-model="search"
                        @input="onSearch"
                        type="search" 
                        placeholder="Search users..." 
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring pl-9"
                     />
                 </div>
            </div>

            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b">
                        <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">User</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Role</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Progress</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Joined</th>
                            <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0">
                        <tr v-if="loading">
                             <td colspan="5" class="h-24 text-center">
                                 <div class="flex items-center justify-center">
                                     <Loader2 class="h-6 w-6 animate-spin text-muted-foreground" />
                                 </div>
                             </td>
                        </tr>
                        <tr v-else-if="users.length === 0">
                             <td colspan="5" class="h-24 text-center text-muted-foreground">No users found.</td>
                        </tr>
                        <tr v-else v-for="user in users" :key="user.id" class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                            <td class="p-4 align-middle">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
                                        {{ getInitials(user.full_name) }}
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ user.full_name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ user.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 align-middle">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80">
                                    {{ user.role?.name || 'User' }}
                                </span>
                            </td>
                             <td class="p-4 align-middle">
                                <button @click="openGameData(user)" class="text-xs border rounded px-2 py-1 hover:bg-muted flex items-center gap-1">
                                    <Gamepad2 class="w-3 h-3" />
                                    View Data
                                </button>
                            </td>
                            <td class="p-4 align-middle text-muted-foreground text-xs">
                                {{ new Date(user.created_at).toLocaleDateString() }}
                            </td>
                            <td class="p-4 align-middle text-right">
                                <div class="flex justify-end gap-2">
                                     <button @click="openEditUser(user)" class="p-2 hover:bg-muted rounded-md text-foreground hover:text-primary transition-colors" title="Edit User">
                                        <Edit class="h-4 w-4" />
                                    </button>
                                     <button @click="openDeleteModal(user.id)" class="p-2 hover:bg-muted rounded-md text-destructive hover:bg-destructive/10 transition-colors" title="Delete User">
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
        <div v-if="isAddUserOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-background/80 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-lg border bg-card p-6 shadow-lg animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold">Add New User</h3>
                    <button @click="isAddUserOpen = false" class="p-1 hover:bg-muted rounded text-muted-foreground">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="space-y-2">
                         <label class="text-sm font-medium">Full Name</label>
                         <input v-model="addUserForm.fullName" type="text" placeholder="John Doe" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                             <label class="text-sm font-medium">Email</label>
                             <input v-model="addUserForm.email" type="email" placeholder="john@example.com" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                        </div>
                        <div class="space-y-2">
                             <label class="text-sm font-medium">Phone Number</label>
                             <input v-model="addUserForm.phone_number" type="tel" placeholder="08..." class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                             <label class="text-sm font-medium">Password</label>
                             <input v-model="addUserForm.password" type="password" placeholder="Min. 6 characters" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                        </div>
                        <div class="space-y-2">
                             <label class="text-sm font-medium">Role</label>
                             <select v-model="addUserForm.role" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                 <option value="user">User</option>
                                 <option value="admin">Admin</option>
                             </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button @click="isAddUserOpen = false" class="px-4 py-2 text-sm font-medium border rounded-md hover:bg-muted transition-colors">
                            Cancel
                        </button>
                        <button 
                            @click="createUser" 
                            :disabled="isCreatingUser"
                            class="px-4 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors flex items-center gap-2 disabled:opacity-50"
                        >
                            <Loader2 v-if="isCreatingUser" class="w-4 h-4 animate-spin" />
                            {{ isCreatingUser ? 'Creating...' : 'Create User' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div v-if="isEditUserOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-background/80 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-lg border bg-card p-6 shadow-lg animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold">Edit User</h3>
                    <button @click="isEditUserOpen = false" class="p-1 hover:bg-muted rounded text-muted-foreground">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="space-y-2">
                         <label class="text-sm font-medium">Full Name</label>
                         <input v-model="editUserForm.fullName" type="text" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                    </div>

                    <div class="space-y-2">
                         <label class="text-sm font-medium">Email</label>
                         <input v-model="editUserForm.email" type="email" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                    </div>
                    
                    <div class="space-y-2">
                         <label class="text-sm font-medium text-muted-foreground">Phone Number (Cannot be changed)</label>
                         <input :value="editUserForm.phone_number" disabled type="text" class="flex h-10 w-full rounded-md border border-input bg-muted px-3 py-2 text-sm text-muted-foreground cursor-not-allowed" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button @click="isEditUserOpen = false" class="px-4 py-2 text-sm font-medium border rounded-md hover:bg-muted transition-colors">
                            Cancel
                        </button>
                        <button 
                            @click="updateUser" 
                            :disabled="isUpdatingUser"
                            class="px-4 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors flex items-center gap-2 disabled:opacity-50"
                        >
                            <Loader2 v-if="isUpdatingUser" class="w-4 h-4 animate-spin" />
                            {{ isUpdatingUser ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Game Data Modal -->
        <div v-if="isGameModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-background/80 backdrop-blur-sm">
            <div class="w-full max-w-4xl max-h-[90vh] flex flex-col rounded-lg border bg-card shadow-lg animate-in fade-in zoom-in-95 duration-200">
                <!-- Header -->
                <div class="p-6 border-b flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">Game Progress</h3>
                        <p class="text-sm text-muted-foreground">{{ selectedUser?.full_name }} ({{ selectedUser?.email }})</p>
                    </div>
                    <button @click="isGameModalOpen = false" class="p-1 hover:bg-muted rounded text-muted-foreground">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-y-auto p-6 space-y-8">
                    <div v-if="loadingGameData" class="flex justify-center p-12">
                        <Loader2 class="w-8 h-8 animate-spin text-muted-foreground" />
                    </div>
                    
                    <div v-else-if="gameData">
                        <!-- Cari Hijaiyah -->
                        <div class="space-y-4">
                            <h4 class="font-medium flex items-center gap-2 text-primary">
                                <Trophy class="w-4 h-4" />
                                Cari Hijaiyah
                            </h4>
                            <div class="rounded-md border">
                                <table class="w-full text-sm">
                                    <thead class="bg-muted/50">
                                        <tr>
                                            <th class="p-3 text-left font-medium">Difficulty</th>
                                            <th class="p-3 text-left font-medium">Score</th>
                                            <th class="p-3 text-left font-medium">Played At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="record in gameData.cari_hijaiyah" :key="record.id" class="border-t">
                                            <td class="p-3 capitalize">{{ record.difficulty }}</td>
                                            <td class="p-3 font-semibold">{{ record.score }}</td>
                                            <td class="p-3 text-muted-foreground">{{ formatDate(record.created_at) }}</td>
                                        </tr>
                                        <tr v-if="!gameData.cari_hijaiyah?.length">
                                            <td colspan="3" class="p-4 text-center text-muted-foreground text-xs">No records found.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pasangkan Huruf -->
                        <div class="space-y-4">
                            <h4 class="font-medium flex items-center gap-2 text-primary">
                                <Trophy class="w-4 h-4" />
                                Pasangkan Huruf
                            </h4>
                            <div class="rounded-md border">
                                <table class="w-full text-sm">
                                    <thead class="bg-muted/50">
                                        <tr>
                                            <th class="p-3 text-left font-medium">Difficulty</th>
                                            <th class="p-3 text-left font-medium">Time (s)</th>
                                            <th class="p-3 text-left font-medium">Attempts</th>
                                             <th class="p-3 text-left font-medium">Score</th>
                                            <th class="p-3 text-left font-medium">Played At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="record in gameData.pasangkan_huruf" :key="record.id" class="border-t">
                                            <td class="p-3 capitalize">{{ record.difficulty }}</td>
                                            <td class="p-3">{{ record.completion_time_seconds }}s</td>
                                            <td class="p-3">{{ record.attempts }}</td>
                                             <td class="p-3 font-semibold">{{ record.score }}</td>
                                            <td class="p-3 text-muted-foreground">{{ formatDate(record.created_at) }}</td>
                                        </tr>
                                         <tr v-if="!gameData.pasangkan_huruf?.length">
                                            <td colspan="5" class="p-4 text-center text-muted-foreground text-xs">No records found.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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

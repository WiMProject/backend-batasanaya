<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { api } from '@/lib/api'
import { useToast } from '@/lib/toast'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import { Loader2, Search, Trash2, Edit, Upload, X, CheckCircle } from 'lucide-vue-next'

const backgrounds = ref<any[]>([])
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

// Upload State
const isUploadOpen = ref(false)
const isUploading = ref(false)
const uploadForm = ref({
    name: '',
    file: null as File | null
})

// Edit State
const isEditOpen = ref(false)
const isUpdating = ref(false)
const editForm = ref({
    id: 0,
    name: '',
    is_active: false,
    file: null as File | null
})

const handleEditFileChange = (e: Event) => {
    const input = e.target as HTMLInputElement
    if (input.files && input.files[0]) {
        editForm.value.file = input.files[0]
    }
}

const openEdit = (bg: any) => {
    editForm.value = {
        id: bg.id,
        name: bg.name,
        is_active: Boolean(bg.is_active),
        file: null
    }
    isEditOpen.value = true
}

const updateBackground = async () => {
    if (!editForm.value.name) return

    openConfirm('Confirm Update', 'Are you sure you want to save changes?', async () => {
        isUpdating.value = true
        try {
            const formData = new FormData()
            formData.append('name', editForm.value.name)
            formData.append('is_active', editForm.value.is_active ? '1' : '0')
            formData.append('_method', 'PATCH')
            
            if (editForm.value.file) {
                formData.append('file', editForm.value.file)
            }
            
            const res = await api.post(`/backgrounds/${editForm.value.id}`, formData)
            
            if (res.ok) {
                isEditOpen.value = false
                fetchBackgrounds()
                toast.success('Background updated successfully')
            } else {
                const data = await res.json()
                toast.error(data.message || 'Update failed')
            }
        } catch (e) {
            console.error(e)
            toast.error('An error occurred during update')
        } finally {
            isUpdating.value = false
        }
    })
}

// Delete Logic
const openDeleteModal = (id: number) => {
    openConfirm('Delete Background', 'Are you sure you want to delete this background? This action cannot be undone.', async () => {
        try {
            const res = await api.delete(`/backgrounds/${id}`)
            if (res.ok) {
                fetchBackgrounds()
                toast.success('Background deleted successfully')
            } else {
                toast.error('Delete failed')
            }
        } catch(e) {
            console.error(e)
            toast.error('An error occurred during deletion')
        }
    }, 'destructive')
}

const fetchBackgrounds = async () => {
    loading.value = true
    try {
        const query = search.value ? `?search=${search.value}` : ''
        const res = await api.get(`/backgrounds${query}`)
        if (res.ok) {
            const data = await res.json()
            backgrounds.value = data.data || []
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
    timeout = setTimeout(fetchBackgrounds, 500)
}

// Upload Logic
const handleFileChange = (e: Event) => {
    const input = e.target as HTMLInputElement
    if (input.files && input.files[0]) {
        uploadForm.value.file = input.files[0]
    }
}

const handleUpload = async () => {
    if (!uploadForm.value.name || !uploadForm.value.file) {
        toast.error("Please enter name and select file.")
        return
    }

    openConfirm('Confirm Upload', 'Are you sure you want to upload this background?', async () => {
        isUploading.value = true
        try {
            const formData = new FormData()
            formData.append('name', uploadForm.value.name)
            formData.append('file', uploadForm.value.file!) // We know it's not null due to check above
            
            const res = await api.post('/backgrounds', formData)
            
            if (res.ok) {
                isUploadOpen.value = false
                uploadForm.value = { name: '', file: null } // Reset
                fetchBackgrounds()
                toast.success('Background uploaded successfully')
            } else {
                const data = await res.json()
                toast.error(data.message || 'Upload failed')
            }
        } catch (e) {
            console.error(e)
            toast.error("Upload error")
        } finally {
            isUploading.value = false
        }
    })
}

onMounted(fetchBackgrounds)
</script>

<template>
    <div class="space-y-6 relative">
         <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight">Backgrounds</h2>
                <p class="text-muted-foreground">Manage game background images.</p>
            </div>
            <button @click="isUploadOpen = true" class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium text-primary-foreground bg-primary rounded-md hover:bg-primary/90 transition-colors w-full md:w-auto">
                <Upload class="w-4 h-4 mr-2" />
                Upload Background
            </button>
        </div>

        <div class="rounded-xl border bg-card text-card-foreground shadow-sm">
            <div class="p-6 border-b flex items-center justify-between">
                 <div class="relative w-full max-w-sm">
                     <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                     <input 
                        v-model="search"
                        @input="onSearch"
                        type="search" 
                        placeholder="Search backgrounds..." 
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring pl-9"
                     />
                 </div>
            </div>

            <div class="p-6">
                <div v-if="loading" class="flex justify-center py-12">
                    <Loader2 class="w-8 h-8 animate-spin text-muted-foreground" />
                </div>
                <div v-else-if="backgrounds.length === 0" class="text-center py-12 text-muted-foreground">
                    No backgrounds found.
                </div>
                <div v-else class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    <div v-for="bg in backgrounds" :key="bg.id" class="group relative rounded-lg border bg-card overflow-hidden hover:shadow-md transition-shadow">
                        <div class="aspect-video bg-muted flex items-center justify-center overflow-hidden relative">
                             <img :src="`/${bg.file}`" class="w-full h-full object-cover transition-transform group-hover:scale-105" />
                        </div>
                        <div class="p-3">
                            <h3 class="font-medium text-sm truncate" :title="bg.name">{{ bg.name }}</h3>
                            <div class="flex items-center justify-end mt-1">
                                <span class="text-xs px-1.5 py-0.5 rounded bg-green-500/10 text-green-500" v-if="bg.is_active">Active</span>
                            </div>
                        </div>
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                            <button @click="openEdit(bg)" class="p-1.5 bg-background text-foreground rounded-md shadow-sm hover:bg-muted" title="Edit">
                                <Edit class="w-3 h-3" />
                            </button>
                            <button @click="openDeleteModal(bg.id)" class="p-1.5 bg-destructive text-destructive-foreground rounded-md shadow-sm hover:bg-destructive/90" title="Delete">
                                <Trash2 class="w-3 h-3" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <div v-if="isUploadOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-background/80 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-lg border bg-card p-6 shadow-lg animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold">Upload Background</h3>
                    <button @click="isUploadOpen = false" class="p-1 hover:bg-muted rounded text-muted-foreground">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="space-y-2">
                         <label class="text-sm font-medium">Name</label>
                         <input v-model="uploadForm.name" type="text" placeholder="Background Name" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                    </div>

                    <div class="space-y-2">
                         <label class="text-sm font-medium">Image File</label>
                         <div class="border-2 border-dashed border-input rounded-lg p-8 text-center bg-muted/20 hover:bg-muted/40 transition-colors relative">
                             <input type="file" accept="image/*" @change="handleFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                             <div v-if="uploadForm.file" class="flex flex-col items-center gap-2">
                                 <CheckCircle class="w-8 h-8 text-green-500" />
                                 <p class="text-sm font-medium">{{ uploadForm.file.name }}</p>
                                 <p class="text-xs text-muted-foreground">Click to replace</p>
                             </div>
                             <div v-else class="flex flex-col items-center gap-2 text-muted-foreground">
                                 <Upload class="w-8 h-8" />
                                 <p class="text-sm font-medium">Drag & drop or click to upload</p>
                             </div>
                         </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button @click="isUploadOpen = false" class="px-4 py-2 text-sm font-medium border rounded-md hover:bg-muted transition-colors">
                            Cancel
                        </button>
                        <button 
                            @click="handleUpload" 
                            :disabled="isUploading || !uploadForm.file || !uploadForm.name"
                            class="px-4 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors flex items-center gap-2 disabled:opacity-50"
                        >
                            <Loader2 v-if="isUploading" class="w-4 h-4 animate-spin" />
                            {{ isUploading ? 'Uploading...' : 'Upload' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="isEditOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-background/80 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-lg border bg-card p-6 shadow-lg animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold">Edit Background</h3>
                    <button @click="isEditOpen = false" class="p-1 hover:bg-muted rounded text-muted-foreground">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="space-y-2">
                         <label class="text-sm font-medium">Name</label>
                         <input v-model="editForm.name" type="text" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                    </div>

                    <!-- File Input for Edit -->
                    <div class="space-y-2">
                         <label class="text-sm font-medium">Replace Image (Optional)</label>
                         <div class="border-2 border-dashed border-input rounded-lg p-4 text-center bg-muted/20 hover:bg-muted/40 transition-colors relative">
                             <input type="file" accept="image/*" @change="handleEditFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                             <div v-if="editForm.file" class="flex flex-col items-center gap-2">
                                 <CheckCircle class="w-6 h-6 text-green-500" />
                                 <p class="text-sm font-medium">{{ editForm.file.name }}</p>
                                 <p class="text-xs text-muted-foreground">Click to replace</p>
                             </div>
                             <div v-else class="flex flex-col items-center gap-2 text-muted-foreground">
                                 <Upload class="w-6 h-6" />
                                 <p class="text-sm font-medium">Click to select new image</p>
                             </div>
                         </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <input type="checkbox" id="isActive" v-model="editForm.is_active" class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary" />
                        <label for="isActive" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Set as Active
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button @click="isEditOpen = false" class="px-4 py-2 text-sm font-medium border rounded-md hover:bg-muted transition-colors">
                            Cancel
                        </button>
                        <button 
                            @click="updateBackground" 
                            :disabled="isUpdating || !editForm.name"
                            class="px-4 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors flex items-center gap-2 disabled:opacity-50"
                        >
                            <Loader2 v-if="isUpdating" class="w-4 h-4 animate-spin" />
                            {{ isUpdating ? 'Saving...' : 'Save Changes' }}
                        </button>
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

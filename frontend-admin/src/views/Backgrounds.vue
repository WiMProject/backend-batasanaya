<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { api } from '@/lib/api'
import { useToast } from '@/lib/toast'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import { Loader2, Search, Trash2, Edit, Upload, X, CheckCircle, Image as ImageIcon } from 'lucide-vue-next'

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
    <div class="space-y-8 animate-in fade-in duration-500">
         <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-2 border-b border-border/40">
            <div>
                 <h2 class="text-4xl font-extrabold tracking-tight bg-gradient-to-r from-primary to-purple-600 bg-clip-text text-transparent">Backgrounds</h2>
                <p class="text-muted-foreground mt-2 text-lg">Curate immersive scenes and environments.</p>
            </div>
            <button @click="isUploadOpen = true" class="group relative inline-flex items-center justify-center h-11 px-8 py-2 text-sm font-medium text-white transition-all bg-primary rounded-full hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/30 active:scale-95 w-full md:w-auto overflow-hidden">
                <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700 ease-in-out"></span>
                <Upload class="w-5 h-5 mr-2" />
                Upload New
            </button>
        </div>

        <div class="relative w-full max-w-md">
             <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Search class="h-5 w-5 text-muted-foreground" />
            </div>
            <input 
                v-model="search"
                @input="onSearch"
                type="text"
                class="block w-full pl-10 pr-3 py-3 border border-input rounded-xl leading-5 bg-card/50 backdrop-blur-sm placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm transition-all shadow-sm"
                placeholder="Find backgrounds..."
            />
        </div>

        <div class="min-h-[200px]">
            <div v-if="loading" class="flex justify-center py-20">
                <div class="flex flex-col items-center gap-4">
                    <Loader2 class="w-10 h-10 animate-spin text-primary"/>
                    <p class="text-muted-foreground">Loading environments...</p>
                </div>
            </div>
            <div v-else-if="backgrounds.length === 0" class="flex flex-col items-center justify-center py-24 text-muted-foreground border-2 border-dashed border-border/50 rounded-3xl bg-card/20">
                 <ImageIcon class="w-16 h-16 opacity-20 mb-4" />
                <p class="text-lg font-medium">No backgrounds found.</p>
                <p class="text-sm">Upload a new image to get started.</p>
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div 
                    v-for="bg in backgrounds" 
                    :key="bg.id" 
                    class="group relative bg-card rounded-2xl overflow-hidden shadow-md hover:shadow-xl hover:shadow-primary/10 transition-all duration-300 hover:-translate-y-1 border border-border/50"
                >
                    <!-- Image Wrapper -->
                    <div class="aspect-video bg-muted relative overflow-hidden">
                         <img 
                            :src="`/${bg.file}`" 
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" 
                            loading="lazy"
                         />
                         
                         <!-- Active Overlay -->
                         <div v-if="bg.is_active" class="absolute top-2 left-2 z-10">
                             <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-green-500/90 text-white shadow-lg backdrop-blur-sm">
                                 <CheckCircle class="w-3 h-3" /> Active
                             </span>
                         </div>
                    </div>

                    <!-- Info -->
                    <div class="p-5">
                        <div class="flex items-start justify-between">
                             <div class="flex-1 min-w-0 mr-2">
                                <h3 class="font-bold text-foreground truncate text-lg group-hover:text-primary transition-colors">{{ bg.name }}</h3>
                             </div>
                        </div>

                        <div class="flex items-center justify-end mt-4 gap-2 opacity-0 group-hover:opacity-100 transition-all translate-y-2 group-hover:translate-y-0">
                            <button @click="openEdit(bg)" class="p-2 hover:bg-blue-50 text-muted-foreground hover:text-blue-600 rounded-lg transition-colors bg-secondary/50" title="Edit">
                                <Edit class="w-4 h-4"/>
                            </button>
                            <button @click="openDeleteModal(bg.id)" class="p-2 hover:bg-red-50 text-muted-foreground hover:text-red-600 rounded-lg transition-colors bg-secondary/50" title="Delete">
                                <Trash2 class="w-4 h-4"/>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <div v-if="isUploadOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
            <div class="w-full max-w-lg rounded-2xl border bg-card p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold bg-gradient-to-r from-primary to-purple-600 bg-clip-text text-transparent">Upload Background</h3>
                    <button @click="isUploadOpen = false" class="p-2 hover:bg-muted rounded-full text-muted-foreground transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-6">
                    <div class="space-y-2">
                         <label class="text-sm font-semibold">Name</label>
                         <input v-model="uploadForm.name" type="text" placeholder="e.g. Forest Day" class="flex h-11 w-full rounded-xl border border-input bg-muted/30 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 transition-all" />
                    </div>

                    <div class="space-y-2">
                         <label class="text-sm font-semibold">Image File</label>
                         <div class="border-2 border-dashed border-input rounded-xl p-10 text-center bg-muted/20 hover:bg-muted/40 transition-colors relative group">
                             <input type="file" accept="image/*" @change="handleFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                             <div v-if="uploadForm.file" class="flex flex-col items-center gap-3">
                                 <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                                    <CheckCircle class="w-6 h-6" />
                                 </div>
                                 <p class="text-sm font-bold text-foreground">{{ uploadForm.file.name }}</p>
                                 <p class="text-xs text-muted-foreground">Click to replace</p>
                             </div>
                             <div v-else class="flex flex-col items-center gap-3 text-muted-foreground">
                                 <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                                    <Upload class="w-6 h-6" />
                                 </div>
                                 <p class="text-sm font-medium">Drag & drop or click to upload</p>
                             </div>
                         </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button @click="isUploadOpen = false" class="px-5 py-2.5 text-sm font-medium border rounded-xl hover:bg-muted transition-colors">
                            Cancel
                        </button>
                        <button 
                            @click="handleUpload" 
                            :disabled="isUploading || !uploadForm.file || !uploadForm.name"
                            class="px-5 py-2.5 text-sm font-bold bg-primary text-white rounded-xl hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center gap-2 disabled:opacity-50"
                        >
                            <Loader2 v-if="isUploading" class="w-4 h-4 animate-spin" />
                            {{ isUploading ? 'Uploading...' : 'Upload' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="isEditOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
            <div class="w-full max-w-lg rounded-2xl border bg-card p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold">Edit Background</h3>
                    <button @click="isEditOpen = false" class="p-2 hover:bg-muted rounded-full text-muted-foreground transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-6">
                    <div class="space-y-2">
                         <label class="text-sm font-semibold">Name</label>
                         <input v-model="editForm.name" type="text" class="flex h-11 w-full rounded-xl border border-input bg-muted/30 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 transition-all" />
                    </div>

                    <!-- File Input for Edit -->
                    <div class="space-y-2">
                         <label class="text-sm font-semibold">Replace Image (Optional)</label>
                         <div class="border-2 border-dashed border-input rounded-xl p-6 text-center bg-muted/20 hover:bg-muted/40 transition-colors relative">
                             <input type="file" accept="image/*" @change="handleEditFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
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

                    <div class="flex items-center space-x-3 bg-muted/30 p-4 rounded-xl">
                        <input type="checkbox" id="isActive" v-model="editForm.is_active" class="h-5 w-5 rounded border-gray-300 text-primary focus:ring-primary transition-all cursor-pointer" />
                        <label for="isActive" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer select-none">
                            Set as Active (Visible in Game)
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button @click="isEditOpen = false" class="px-5 py-2.5 text-sm font-medium border rounded-xl hover:bg-muted transition-colors">
                            Cancel
                        </button>
                        <button 
                            @click="updateBackground" 
                            :disabled="isUpdating || !editForm.name"
                            class="px-5 py-2.5 text-sm font-bold bg-primary text-white rounded-xl hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center gap-2 disabled:opacity-50"
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

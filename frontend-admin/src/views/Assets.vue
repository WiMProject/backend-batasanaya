<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { api } from '@/lib/api'
import { useToast } from '@/lib/toast'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import { Loader2, Search, Trash2, Upload, Play, Music, Image as ImageIcon, File, X, CheckCircle, ChevronLeft, ChevronRight, CheckSquare, Square, Edit } from 'lucide-vue-next'

const assets = ref<any[]>([])
const loading = ref(true)
const search = ref('')
const filterType = ref('all')
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

// Pagination State
const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)
const from = ref(0)
const to = ref(0)

// Selection State
const selectedAssets = ref<string[]>([])

// Upload Modal State
const isUploadOpen = ref(false)
const isUploading = ref(false)
const uploadForm = ref({
    category: '',
    subcategory: '',
    files: [] as File[]
})

const categories: Record<string, string[]> = {
    'ui': ['button', 'icon', 'background', 'panel', 'other'],
    'game': ['carihijaiyah', 'pasangkanhuruf', 'berlatih', 'quiz', 'reward'],
    'tutorial': ['video', 'image'],
}

const availableSubcategories = computed(() => {
    if (!uploadForm.value.category) return []
    return categories[uploadForm.value.category] || []
})

const availableEditSubcategories = computed(() => {
    if (!editForm.value.category) return []
    return categories[editForm.value.category] || []
})

const fetchAssets = async (page = 1) => {
    loading.value = true
    try {
        let url = `/assets?page=${page}&search=${search.value}`
        if (filterType.value !== 'all') url += `&type=${filterType.value}`
        
        const res = await api.get(url)
        if (res.ok) {
            const data = await res.json()
            assets.value = data.data || []
            currentPage.value = data.current_page
            lastPage.value = data.last_page
            total.value = data.total
            from.value = data.from
            to.value = data.to
            
            // Clear selection on page change (optional, but safer)
            selectedAssets.value = []
        }
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
}

// Watchers
watch(currentPage, (newPage) => fetchAssets(newPage))

let timeout: any
const onSearch = () => {
    currentPage.value = 1 // Reset to page 1
    clearTimeout(timeout)
    timeout = setTimeout(() => fetchAssets(1), 500)
}

const onFilterChange = (type: string) => {
    filterType.value = type
    currentPage.value = 1
    fetchAssets(1)
}

// Bulk Actions
const toggleSelect = (id: string) => {
    if (selectedAssets.value.includes(id)) {
        selectedAssets.value = selectedAssets.value.filter(i => i !== id)
    } else {
        selectedAssets.value.push(id)
    }
}

const isAllSelected = computed(() => {
    return assets.value.length > 0 && assets.value.every(a => selectedAssets.value.includes(a.id))
})

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedAssets.value = []
    } else {
        selectedAssets.value = assets.value.map(a => a.id)
    }
}

// Bulk Delete Logic
const openDeleteModal = () => {
    if (selectedAssets.value.length === 0) return
    
    openConfirm('Delete Assets', `Are you sure you want to delete ${selectedAssets.value.length} selected assets? This action cannot be undone.`, async () => {
        try {
            const res = await api.delete('/assets/batch', {
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ asset_ids: selectedAssets.value })
            })

            if (res.ok) {
                selectedAssets.value = []
                fetchAssets(currentPage.value)
                toast.success('Assets deleted successfully')
            } else {
                 toast.error('Bulk delete failed')
            }
        } catch (e) {
            console.error(e)
            toast.error('An error occurred during deletion')
        }
    }, 'destructive')
}


// Edit Modal State
const isEditOpen = ref(false)
const isUpdating = ref(false)
const editForm = ref({
    id: '',
    filename: '',
    category: '',
    subcategory: '',
    file: null as File | null
})

const openEdit = (asset: any) => {
    editForm.value = {
        id: asset.id,
        filename: asset.filename || asset.file_name,
        category: asset.category,
        subcategory: asset.subcategory || '',
        file: null
    }
    isEditOpen.value = true
}

const handleEditFileChange = (e: Event) => {
    const input = e.target as HTMLInputElement
    if (input.files?.[0]) {
        editForm.value.file = input.files[0]
    }
}

const updateAsset = async () => {
    if (!editForm.value.category) {
        toast.error("Category is required")
        return
    }
    
    openConfirm('Confirm Update', 'Are you sure you want to save changes?', async () => {
        isUpdating.value = true
        try {
            const formData = new FormData()
            formData.append('filename', editForm.value.filename)
            formData.append('category', editForm.value.category)
            formData.append('subcategory', editForm.value.subcategory || '')
            if (editForm.value.file) {
                formData.append('file', editForm.value.file)
            }
            formData.append('_method', 'PATCH')

            const res = await api.post(`/assets/${editForm.value.id}`, formData)
            
            if (res.ok) {
                isEditOpen.value = false
                fetchAssets(currentPage.value)
                toast.success('Asset updated successfully')
            } else {
                const data = await res.json()
                toast.error(data.message || 'Update failed')
            }
        } catch(e) {
            console.error(e)
            toast.error('An error occurred during update')
        } finally {
            isUpdating.value = false
        }
    })
}

// Upload Logic
const handleFileChange = (e: Event) => {
    const input = e.target as HTMLInputElement
    if (input.files) {
        uploadForm.value.files = Array.from(input.files)
    }
}

const handleUpload = async () => {
    if (!uploadForm.value.category || uploadForm.value.files.length === 0) {
        toast.error("Please select a category and files.")
        return
    }

    openConfirm('Confirm Upload', `Are you sure you want to upload ${uploadForm.value.files.length} files?`, async () => {
        isUploading.value = true
        try {
            const formData = new FormData()
            formData.append('category', uploadForm.value.category)
            formData.append('subcategory', uploadForm.value.subcategory)
            
            uploadForm.value.files.forEach((file) => {
                formData.append('files[]', file)
            })

            const res = await api.post('/assets/batch', formData)
            
            if (res.ok) {
                isUploadOpen.value = false
                uploadForm.value = { category: '', subcategory: '', files: [] } 
                fetchAssets(1) // Return to first page to see new uploads
                toast.success('Assets uploaded successfully')
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

const getIcon = (type: string) => {
    if (type === 'audio') return Music
    if (type === 'video') return Play
    if (type === 'image') return ImageIcon
    return File
}

onMounted(() => fetchAssets(1))
</script>

<template>
    <div class="space-y-6 relative">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight">Assets Library</h2>
                <p class="text-muted-foreground">Manage game resources & categorization.</p>
            </div>
            <button @click="isUploadOpen = true" class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium text-primary-foreground bg-primary rounded-md hover:bg-primary/90 transition-colors w-full md:w-auto">
                <Upload class="w-4 h-4 mr-2" />
                Upload New
            </button>
        </div>

        <!-- Main Content -->
        <div class="rounded-xl border bg-card text-card-foreground shadow-sm flex flex-col min-h-[600px]">
            <!-- Toolbar -->
            <div class="p-4 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-muted/20">
                 <!-- Left: Tabs & Select All -->
                 <div class="flex items-center gap-4">
                     <button @click="toggleSelectAll" class="flex items-center gap-2 text-sm font-medium text-muted-foreground hover:text-foreground">
                         <component :is="isAllSelected ? CheckSquare : Square" class="w-5 h-5" />
                         Select All
                     </button>
                     
                     <div class="h-6 w-px bg-border"></div>

                     <!-- Filter Tabs -->
                     <div class="flex items-center p-1 bg-muted rounded-md space-x-1">
                        <button @click="onFilterChange('all')" :class="['px-3 py-1.5 text-sm font-medium rounded-sm transition-all', filterType === 'all' ? 'bg-background shadow-sm text-foreground' : 'text-muted-foreground hover:text-foreground']">All</button>
                        <button @click="onFilterChange('image')" :class="['px-3 py-1.5 text-sm font-medium rounded-sm transition-all', filterType === 'image' ? 'bg-background shadow-sm text-foreground' : 'text-muted-foreground hover:text-foreground']">Images</button>
                        <button @click="onFilterChange('audio')" :class="['px-3 py-1.5 text-sm font-medium rounded-sm transition-all', filterType === 'audio' ? 'bg-background shadow-sm text-foreground' : 'text-muted-foreground hover:text-foreground']">Audio</button>
                     </div>
                 </div>

                 <!-- Right: Actions & Search -->
                 <div class="flex items-center gap-3">
                     <!-- Made button Solid Red for visibility -->
                     <button v-if="selectedAssets.length > 0" @click="openDeleteModal" class="flex items-center gap-2 bg-destructive text-destructive-foreground hover:bg-destructive/90 px-4 py-2 rounded-md text-sm font-medium transition-colors shadow-sm animate-in fade-in zoom-in duration-200">
                         <Trash2 class="w-4 h-4" />
                         Delete ({{ selectedAssets.length }})
                     </button>

                     <div class="relative w-full max-w-xs">
                         <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                         <input 
                            v-model="search"
                            @input="onSearch"
                            type="search" 
                            placeholder="Search assets..." 
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring pl-9"
                         />
                     </div>
                 </div>
            </div>

            <!-- Grid or List -->
            <div class="p-6 flex-1">
                <div v-if="loading" class="flex justify-center py-12">
                    <Loader2 class="w-8 h-8 animate-spin text-muted-foreground" />
                </div>
                <div v-else-if="assets.length === 0" class="text-center py-12 text-muted-foreground">
                    No assets found.
                </div>
                <div v-else class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    <div v-for="asset in assets" :key="asset.id" 
                        class="group relative rounded-lg border bg-card overflow-hidden hover:shadow-md transition-all cursor-pointer"
                        :class="selectedAssets.includes(asset.id) ? 'ring-2 ring-offset-2 ring-primary border-primary' : ''"
                        @click="toggleSelect(asset.id)"
                    >
                        <!-- Selection Checkbox Overlay -->
                        <div class="absolute top-2 left-2 z-20">
                             <div 
                                class="w-6 h-6 rounded-md border-2 flex items-center justify-center transition-colors shadow-sm"
                                :class="selectedAssets.includes(asset.id) ? 'border-primary bg-primary text-primary-foreground' : 'border-white/70 bg-black/30 hover:bg-black/50'"
                             >
                                 <CheckSquare v-if="selectedAssets.includes(asset.id)" class="w-4 h-4" />
                             </div>
                        </div>

                        <div class="absolute top-2 right-2 z-20 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click.stop="openEdit(asset)" class="p-1.5 bg-background text-foreground rounded-md shadow-sm hover:bg-muted" title="Edit">
                                <Edit class="w-3 h-3" />
                            </button>
                        </div>

                        <!-- Preview -->
                        <div class="aspect-square bg-muted flex items-center justify-center overflow-hidden relative">
                             <img v-if="asset.type === 'image' || asset.type === 'background'" :src="`/api/assets/${asset.id}/file`" class="w-full h-full object-cover" />
                             <component v-else :is="getIcon(asset.type)" class="w-10 h-10 text-muted-foreground" />
                             
                             <!-- Category Badge -->
                             <div class="absolute bottom-1 left-1 bg-black/60 text-white text-[10px] px-1.5 py-0.5 rounded backdrop-blur-sm">
                                 {{ asset.category }} {{ asset.subcategory ? `/ ${asset.subcategory}` : '' }}
                             </div>
                        </div>
                        
                        <!-- Info -->
                        <div class="p-3">
                            <p class="font-medium text-sm truncate" :title="asset.filename">{{ asset.file_name }}</p>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs text-muted-foreground uppercase">{{ asset.type }}</span>
                                <span class="text-xs text-muted-foreground">{{ (asset.size / 1024).toFixed(0) }} KB</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t flex flex-col sm:flex-row items-center justify-between gap-4 bg-muted/20">
                <div class="text-sm text-muted-foreground order-2 sm:order-1">
                    Showing <strong>{{ from }}</strong> to <strong>{{ to }}</strong> of <strong>{{ total }}</strong> assets
                </div>
                <div class="flex items-center gap-2 order-1 sm:order-2">
                    <button 
                        @click="currentPage--" 
                        :disabled="currentPage === 1"
                        class="p-2 rounded-md border bg-background hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <ChevronLeft class="w-4 h-4" />
                    </button>
                    <span class="text-sm font-medium">Page {{ currentPage }} of {{ lastPage }}</span>
                    <button 
                        @click="currentPage++" 
                        :disabled="currentPage === lastPage"
                        class="p-2 rounded-md border bg-background hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <ChevronRight class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <div v-if="isUploadOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-background/80 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-lg border bg-card p-6 shadow-lg animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold">Upload Assets</h3>
                    <button @click="isUploadOpen = false" class="p-1 hover:bg-muted rounded text-muted-foreground">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                             <label class="text-sm font-medium">Category</label>
                             <select v-model="uploadForm.category" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                 <option value="" disabled>Select Category</option>
                                 <option v-for="(_subs, cat) in categories" :key="cat" :value="cat">{{ cat.toUpperCase() }}</option>
                             </select>
                        </div>
                        <div class="space-y-2">
                             <label class="text-sm font-medium">Subcategory</label>
                             <select v-model="uploadForm.subcategory" :disabled="!uploadForm.category" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:opacity-50">
                                 <option value="">Any / None</option>
                                 <option v-for="sub in availableSubcategories" :key="sub" :value="sub">{{ sub }}</option>
                             </select>
                        </div>
                    </div>

                    <div class="space-y-2">
                         <label class="text-sm font-medium">Files</label>
                         <div class="border-2 border-dashed border-input rounded-lg p-8 text-center bg-muted/20 hover:bg-muted/40 transition-colors relative">
                             <input type="file" multiple @change="handleFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                             <div v-if="uploadForm.files.length > 0" class="flex flex-col items-center gap-2">
                                 <CheckCircle class="w-8 h-8 text-green-500" />
                                 <p class="text-sm font-medium">{{ uploadForm.files.length }} files selected</p>
                                 <p class="text-xs text-muted-foreground">Click to change</p>
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
                            :disabled="isUploading || uploadForm.files.length === 0 || !uploadForm.category"
                            class="px-4 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors flex items-center gap-2 disabled:opacity-50"
                        >
                            <Loader2 v-if="isUploading" class="w-4 h-4 animate-spin" />
                            {{ isUploading ? 'Uploading...' : 'Upload Files' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Asset Modal -->
        <div v-if="isEditOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-background/80 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-lg border bg-card p-6 shadow-lg animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold">Edit Asset</h3>
                    <button @click="isEditOpen = false" class="p-1 hover:bg-muted rounded text-muted-foreground">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="space-y-2">
                         <label class="text-sm font-medium">Filename</label>
                         <input v-model="editForm.filename" type="text" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                             <label class="text-sm font-medium">Category</label>
                             <select v-model="editForm.category" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                 <option value="" disabled>Select Category</option>
                                 <option v-for="(_subs, cat) in categories" :key="cat" :value="cat">{{ cat.toUpperCase() }}</option>
                             </select>
                        </div>
                        <div class="space-y-2">
                             <label class="text-sm font-medium">Subcategory</label>
                             <select v-model="editForm.subcategory" :disabled="!editForm.category" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:opacity-50">
                                 <option value="">Any / None</option>
                                 <option v-for="sub in availableEditSubcategories" :key="sub" :value="sub">{{ sub }}</option>
                             </select>
                        </div>
                    </div>

                    <div class="space-y-2">
                         <label class="text-sm font-medium">Replace File (Optional)</label>
                         <div class="flex items-center gap-2">
                             <input type="file" @change="handleEditFileChange" class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" />
                         </div>
                         <p v-if="editForm.file" class="text-xs text-green-600 flex items-center gap-1">
                             <CheckCircle class="w-3 h-3" /> Selected: {{ editForm.file.name }}
                         </p>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button @click="isEditOpen = false" class="px-4 py-2 text-sm font-medium border rounded-md hover:bg-muted transition-colors">
                            Cancel
                        </button>
                        <button 
                            @click="updateAsset" 
                            :disabled="isUpdating || !editForm.category"
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

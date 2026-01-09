<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { api } from '@/lib/api'
import { useToast } from '@/lib/toast'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import { Loader2, Search, Trash2, Upload, Play, Music, Image as ImageIcon, File, X, CheckCircle, ChevronLeft, ChevronRight, CheckSquare, Edit, FileBox } from 'lucide-vue-next'

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
    <div class="space-y-8 animate-in fade-in duration-500">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-6 border-b border-border/40">
            <div>
                 <h2 class="text-3xl font-bold tracking-tight text-foreground">Assets Library</h2>
                <p class="text-muted-foreground mt-2 text-lg">Centralized resource management for game interactions.</p>
            </div>
            <button @click="isUploadOpen = true" class="inline-flex items-center justify-center h-10 px-6 py-2 text-sm font-medium text-white transition-colors bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm dark:bg-slate-50 dark:text-slate-900 dark:hover:bg-slate-200">
                <Upload class="w-5 h-5 mr-2" />
                Upload New
            </button>
        </div>

        <!-- Main Content (Glass Card) -->
        <div class="rounded-3xl border border-border/50 bg-card/50 backdrop-blur-sm shadow-xl flex flex-col min-h-[600px] overflow-hidden">
            <!-- Toolbar -->
            <div class="p-4 border-b border-border/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-muted/10">
                 <!-- Left: Tabs & Select All -->
                 <div class="flex items-center gap-6">
                     <button @click="toggleSelectAll" class="flex items-center gap-2 text-sm font-bold text-foreground hover:text-primary transition-colors group">
                         <div :class="isAllSelected ? 'bg-primary border-primary text-white' : 'bg-transparent border-input group-hover:border-primary'" class="w-5 h-5 rounded border flex items-center justify-center transition-colors">
                              <CheckSquare v-if="isAllSelected" class="w-3.5 h-3.5" />
                         </div>
                         Select All
                     </button>
                     
                     <div class="h-6 w-px bg-border/50"></div>

                     <!-- Filter Tabs -->
                     <div class="flex p-1 bg-muted/50 rounded-xl space-x-1">
                        <button @click="onFilterChange('all')" :class="['px-4 py-1.5 text-xs font-bold uppercase tracking-wider rounded-lg transition-all', filterType === 'all' ? 'bg-background shadow-sm text-primary' : 'text-muted-foreground hover:text-foreground']">All</button>
                        <button @click="onFilterChange('image')" :class="['px-4 py-1.5 text-xs font-bold uppercase tracking-wider rounded-lg transition-all', filterType === 'image' ? 'bg-background shadow-sm text-primary' : 'text-muted-foreground hover:text-foreground']">Images</button>
                        <button @click="onFilterChange('audio')" :class="['px-4 py-1.5 text-xs font-bold uppercase tracking-wider rounded-lg transition-all', filterType === 'audio' ? 'bg-background shadow-sm text-primary' : 'text-muted-foreground hover:text-foreground']">Audio</button>
                     </div>
                 </div>

                 <!-- Right: Actions & Search -->
                 <div class="flex items-center gap-3">
                     <transition name="fade">
                         <button v-if="selectedAssets.length > 0" @click="openDeleteModal" class="flex items-center gap-2 bg-red-500 text-white hover:bg-red-600 px-4 py-2 rounded-xl text-sm font-bold transition-all shadow-lg shadow-red-500/20 active:scale-95">
                             <Trash2 class="w-4 h-4" />
                             Delete ({{ selectedAssets.length }})
                         </button>
                     </transition>

                     <div class="relative w-full max-w-xs group">
                         <Search class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground group-focus-within:text-primary transition-colors" />
                         <input 
                            v-model="search"
                            @input="onSearch"
                            type="search" 
                            placeholder="Search assets..." 
                            class="flex h-10 w-full rounded-xl border border-input bg-card/50 px-3 py-1 text-sm shadow-sm transition-all focus:w-full focus:bg-background focus:ring-2 focus:ring-primary/20 pl-10"
                         />
                     </div>
                 </div>
            </div>

            <!-- Grid or List -->
            <div class="p-6 flex-1 bg-gradient-to-b from-transparent to-muted/5">
                <div v-if="loading" class="flex flex-col items-center justify-center py-24 gap-4">
                    <Loader2 class="w-10 h-10 animate-spin text-primary" />
                    <p class="text-muted-foreground font-medium">Loading collection...</p>
                </div>
                <div v-else-if="assets.length === 0" class="flex flex-col items-center justify-center py-24 text-muted-foreground gap-4">
                    <FileBox class="w-16 h-16 opacity-20" />
                    <p class="text-lg">No assets found.</p>
                </div>
                <div v-else class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
                    <div v-for="asset in assets" :key="asset.id" 
                        class="group relative rounded-2xl border border-border/50 bg-card overflow-hidden hover:shadow-xl hover:shadow-primary/5 transition-all duration-300 cursor-pointer hover:-translate-y-1"
                        :class="selectedAssets.includes(asset.id) ? 'ring-2 ring-primary border-transparent' : ''"
                        @click="toggleSelect(asset.id)"
                    >
                        <!-- Selection Checkbox Overlay -->
                        <div class="absolute top-2 left-2 z-20">
                             <div 
                                class="w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all shadow-sm backdrop-blur-md"
                                :class="selectedAssets.includes(asset.id) ? 'border-primary bg-primary text-white' : 'border-white/50 bg-black/20 hover:bg-black/40'"
                             >
                                 <CheckSquare v-if="selectedAssets.includes(asset.id)" class="w-3.5 h-3.5" />
                             </div>
                        </div>

                        <div class="absolute top-2 right-2 z-20 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click.stop="openEdit(asset)" class="p-2 bg-white/90 text-foreground rounded-lg shadow-sm hover:bg-white hover:text-primary transition-colors backdrop-blur-sm" title="Edit">
                                <Edit class="w-3.5 h-3.5" />
                            </button>
                        </div>

                        <!-- Preview -->
                        <div class="aspect-square bg-muted/30 flex items-center justify-center overflow-hidden relative group-hover:bg-muted/50 transition-colors">
                             <img v-if="asset.type === 'image' || asset.type === 'background'" :src="`/api/assets/${asset.id}/file`" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy" />
                             <div v-else class="flex flex-col items-center gap-2">
                                 <div class="p-4 rounded-full bg-primary/5 text-primary group-hover:scale-110 transition-transform duration-300">
                                     <component :is="getIcon(asset.type)" class="w-8 h-8" />
                                 </div>
                             </div>
                             
                             <!-- Category Badge -->
                             <div class="absolute bottom-2 left-2 bg-black/60 text-white text-[10px] font-bold px-2 py-1 rounded-md backdrop-blur-md border border-white/10 flex items-center gap-1">
                                 <span>{{ asset.category }}</span>
                                 <span v-if="asset.subcategory" class="opacity-70 font-normal border-l border-white/20 pl-1 uppercase tracking-wider">{{ asset.subcategory }}</span>
                             </div>
                        </div>
                        
                        <!-- Info -->
                        <div class="p-3 bg-card border-t border-border/50">
                            <p class="font-semibold text-sm truncate text-foreground/90 group-hover:text-primary transition-colors" :title="asset.filename">{{ asset.file_name }}</p>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-[10px] text-muted-foreground font-medium uppercase tracking-wider bg-secondary px-1.5 py-0.5 rounded">{{ asset.type }}</span>
                                <span class="text-[10px] text-muted-foreground">{{ (asset.size / 1024).toFixed(0) }} KB</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-border/50 flex flex-col sm:flex-row items-center justify-between gap-4 bg-muted/10">
                <div class="text-sm text-muted-foreground order-2 sm:order-1 font-medium">
                    Showing <span class="text-foreground">{{ from }}</span> to <span class="text-foreground">{{ to }}</span> of <span class="text-foreground">{{ total }}</span> items
                </div>
                <div class="flex items-center gap-2 order-1 sm:order-2">
                    <button 
                        @click="currentPage--" 
                        :disabled="currentPage === 1"
                        class="p-2.5 rounded-xl border bg-card hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <ChevronLeft class="w-4 h-4" />
                    </button>
                    <span class="text-sm font-bold bg-muted/50 px-3 py-1 rounded-lg">Page {{ currentPage }} / {{ lastPage }}</span>
                    <button 
                        @click="currentPage++" 
                        :disabled="currentPage === lastPage"
                        class="p-2.5 rounded-xl border bg-card hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <ChevronRight class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <Teleport to="body">
            <div v-if="isUploadOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
                <div class="w-full max-w-lg rounded-3xl border bg-card p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-primary/10 rounded-full text-primary">
                                <Upload class="w-5 h-5" />
                            </div>
                            <h3 class="text-xl font-bold">Upload Assets</h3>
                        </div>
                        <button @click="isUploadOpen = false" class="p-2 hover:bg-muted rounded-full text-muted-foreground transition-colors">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                 <label class="text-sm font-bold">Category</label>
                                 <select v-model="uploadForm.category" class="flex h-11 w-full rounded-xl border border-input bg-muted/30 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 transition-all">
                                     <option value="" disabled>Select Category</option>
                                     <option v-for="(_subs, cat) in categories" :key="cat" :value="cat">{{ cat.toUpperCase() }}</option>
                                 </select>
                            </div>
                            <div class="space-y-2">
                                 <label class="text-sm font-bold">Subcategory</label>
                                 <select v-model="uploadForm.subcategory" :disabled="!uploadForm.category" class="flex h-11 w-full rounded-xl border border-input bg-muted/30 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 transition-all disabled:opacity-50">
                                     <option value="">Any / None</option>
                                     <option v-for="sub in availableSubcategories" :key="sub" :value="sub">{{ sub }}</option>
                                 </select>
                            </div>
                        </div>

                        <div class="space-y-2">
                             <label class="text-sm font-bold">Files</label>
                             <div class="border-2 border-dashed border-input rounded-2xl p-10 text-center bg-muted/20 hover:bg-muted/30 transition-colors relative group">
                                 <input type="file" multiple @change="handleFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                                 <div v-if="uploadForm.files.length > 0" class="flex flex-col items-center gap-3">
                                     <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center text-green-600 shadow-sm">
                                        <CheckCircle class="w-6 h-6" />
                                     </div>
                                     <p class="text-lg font-bold text-foreground">{{ uploadForm.files.length }} files selected</p>
                                     <p class="text-xs text-muted-foreground">Click to add more or change</p>
                                 </div>
                                 <div v-else class="flex flex-col items-center gap-3 text-muted-foreground">
                                     <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                                        <Upload class="w-6 h-6" />
                                     </div>
                                     <p class="text-base font-bold text-foreground">Drag & drop files here</p>
                                     <p class="text-xs opacity-60">or click to browse</p>
                                 </div>
                             </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button @click="isUploadOpen = false" class="px-5 py-2.5 text-sm font-medium border rounded-xl hover:bg-muted transition-colors">
                                Cancel
                            </button>
                            <button 
                                @click="handleUpload" 
                                :disabled="isUploading || uploadForm.files.length === 0 || !uploadForm.category"
                                class="px-5 py-2.5 text-sm font-bold bg-primary text-white rounded-xl hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center gap-2 disabled:opacity-50"
                            >
                                <Loader2 v-if="isUploading" class="w-4 h-4 animate-spin" />
                                {{ isUploading ? 'Uploading...' : 'Upload Files' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Edit Asset Modal -->
        <Teleport to="body">
            <div v-if="isEditOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
                <div class="w-full max-w-lg rounded-3xl border bg-card p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold">Edit Asset</h3>
                        <button @click="isEditOpen = false" class="p-2 hover:bg-muted rounded-full text-muted-foreground transition-colors">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="space-y-5">
                        <div class="space-y-2">
                             <label class="text-sm font-bold">Filename</label>
                             <input v-model="editForm.filename" type="text" class="flex h-11 w-full rounded-xl border border-input bg-muted/30 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 transition-all" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                 <label class="text-sm font-bold">Category</label>
                                 <select v-model="editForm.category" class="flex h-11 w-full rounded-xl border border-input bg-muted/30 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 transition-all">
                                     <option value="" disabled>Select Category</option>
                                     <option v-for="(_subs, cat) in categories" :key="cat" :value="cat">{{ cat.toUpperCase() }}</option>
                                 </select>
                            </div>
                            <div class="space-y-2">
                                 <label class="text-sm font-bold">Subcategory</label>
                                 <select v-model="editForm.subcategory" :disabled="!editForm.category" class="flex h-11 w-full rounded-xl border border-input bg-muted/30 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 transition-all disabled:opacity-50">
                                     <option value="">Any / None</option>
                                     <option v-for="sub in availableEditSubcategories" :key="sub" :value="sub">{{ sub }}</option>
                                 </select>
                            </div>
                        </div>

                        <div class="space-y-2">
                             <label class="text-sm font-bold">Replace File</label>
                             <div class="flex items-center gap-3">
                                 <label class="flex-1 cursor-pointer">
                                     <div class="flex items-center gap-2 p-3 border rounded-xl hover:bg-muted transition-colors border-dashed">
                                         <Upload class="w-4 h-4 text-muted-foreground" />
                                         <span class="text-sm text-muted-foreground truncate">{{ editForm.file ? editForm.file.name : 'Choose a new file...' }}</span>
                                     </div>
                                     <input type="file" @change="handleEditFileChange" class="hidden" />
                                 </label>
                             </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button @click="isEditOpen = false" class="px-5 py-2.5 text-sm font-medium border rounded-xl hover:bg-muted transition-colors">
                                Cancel
                            </button>
                            <button 
                                @click="updateAsset" 
                                :disabled="isUpdating || !editForm.category"
                                class="px-5 py-2.5 text-sm font-bold bg-primary text-white rounded-xl hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center gap-2 disabled:opacity-50"
                            >
                                <Loader2 v-if="isUpdating" class="w-4 h-4 animate-spin" />
                                {{ isUpdating ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
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

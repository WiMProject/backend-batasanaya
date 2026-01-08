<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { api } from '@/lib/api'
import { useToast } from '@/lib/toast'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import { Loader2, Search, Upload, Trash2, X, CheckCircle, Edit } from 'lucide-vue-next'

const songs = ref<any[]>([])
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
    title: '',
    file: null as File | null
})

// Edit Logic
const isEditOpen = ref(false)
const isUpdating = ref(false)
const editForm = ref({
    id: 0,
    title: '',
    file: null as File | null
})

const openEdit = (song: any) => {
    editForm.value = {
        id: song.id,
        title: song.title,
        file: null
    }
    isEditOpen.value = true
}

const handleEditFileChange = (e: Event) => {
    const input = e.target as HTMLInputElement
    if (input.files && input.files[0]) {
        editForm.value.file = input.files[0]
    }
}

const updateSong = async () => {
    if (!editForm.value.title) return
    isUpdating.value = true

    openConfirm('Confirm Update', 'Are you sure you want to save changes?', async () => {
        isUpdating.value = true
        try {
            const formData = new FormData()
            formData.append('title', editForm.value.title)
            if (editForm.value.file) {
                formData.append('file', editForm.value.file)
            }
            formData.append('_method', 'PATCH')

            const res = await api.post(`/songs/${editForm.value.id}`, formData)
            
            if (res.ok) {
                fetchSongs()
                isEditOpen.value = false
                toast.success('Song updated successfully')
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

const fetchSongs = async () => {
    loading.value = true
    try {
        const query = search.value ? `?search=${search.value}` : ''
        const res = await api.get(`/songs${query}`)
        if (res.ok) {
            const data = await res.json()
            songs.value = data.data || []
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
    timeout = setTimeout(fetchSongs, 500)
}

// Delete Logic
const openDeleteModal = (id: number) => {
    openConfirm('Delete Song', 'Are you sure you want to delete this song? This action cannot be undone.', async () => {
        try {
            await api.delete(`/songs/${id}`)
            fetchSongs()
            toast.success('Song deleted successfully')
        } catch(e) {
            console.error(e)
            toast.error('An error occurred during deletion')
        }
    }, 'destructive')
}

// Upload Logic
const handleFileChange = (e: Event) => {
    const input = e.target as HTMLInputElement
    if (input.files && input.files[0]) {
        uploadForm.value.file = input.files[0]
    }
}

const handleUpload = async () => {
    if (!uploadForm.value.title || !uploadForm.value.file) {
        toast.error("Please enter title and select file.")
        return
    }

    openConfirm('Confirm Upload', 'Are you sure you want to upload this song?', async () => {
        isUploading.value = true
        try {
            const formData = new FormData()
            formData.append('title', uploadForm.value.title)
            formData.append('file', uploadForm.value.file!)
            
            const res = await api.post('/songs', formData)
            
            if (res.ok) {
                isUploadOpen.value = false
                uploadForm.value = { title: '', file: null } // Reset
                fetchSongs()
                toast.success('Song uploaded successfully')
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

onMounted(fetchSongs)
</script>

<template>
    <div class="space-y-6 relative">
         <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight">Audio Library</h2>
                <p class="text-muted-foreground">Manage game music and sound effects.</p>
            </div>
            <button @click="isUploadOpen = true" class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium text-primary-foreground bg-primary rounded-md hover:bg-primary/90 transition-colors w-full md:w-auto">
                <Upload class="w-4 h-4 mr-2" />
                Upload Audio
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
                        placeholder="Search audio..." 
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring pl-9"
                     />
                 </div>
            </div>

            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b">
                        <tr class="border-b hover:bg-muted/50">
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Title</th>
                             <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Filename</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Preview</th>
                            <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                         <tr v-if="loading"><td colspan="4" class="p-6 text-center"><Loader2 class="mx-auto w-6 h-6 animate-spin"/></td></tr>
                         <tr v-else-if="songs.length === 0"><td colspan="4" class="p-6 text-center text-muted-foreground">No audio files found.</td></tr>
                         <tr v-else v-for="song in songs" :key="song.id" class="border-b transition-colors hover:bg-muted/50">
                             <td class="p-4 align-middle font-medium">{{ song.title || 'Untitled' }}</td>
                             <td class="p-4 align-middle text-muted-foreground text-xs">{{ song.file_name }}</td>
                             <td class="p-4 align-middle">
                                 <audio controls class="h-8 w-48">
                                     <source :src="`/${song.file}`" :type="song.mime_type || 'audio/mpeg'">
                                 </audio>
                             </td>
                             <td class="p-4 align-middle text-right">
                                 <div class="flex justify-end gap-2">
                                     <button @click="openEdit(song)" class="p-2 text-foreground hover:bg-muted rounded-md" title="Edit">
                                         <Edit class="w-4 h-4"/>
                                     </button>
                                     <button @click="openDeleteModal(song.id)" class="p-2 text-destructive hover:bg-destructive/10 rounded-md" title="Delete">
                                         <Trash2 class="w-4 h-4"/>
                                     </button>
                                 </div>
                             </td>
                         </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Upload Modal -->
        <div v-if="isUploadOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-background/80 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-lg border bg-card p-6 shadow-lg animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold">Upload Audio</h3>
                    <button @click="isUploadOpen = false" class="p-1 hover:bg-muted rounded text-muted-foreground">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="space-y-2">
                         <label class="text-sm font-medium">Title</label>
                         <input v-model="uploadForm.title" type="text" placeholder="Song Title" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                    </div>

                    <div class="space-y-2">
                         <label class="text-sm font-medium">Audio File</label>
                         <div class="border-2 border-dashed border-input rounded-lg p-8 text-center bg-muted/20 hover:bg-muted/40 transition-colors relative">
                             <input type="file" accept="audio/*" @change="handleFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
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
                            :disabled="isUploading || !uploadForm.file || !uploadForm.title"
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
                    <h3 class="text-lg font-semibold">Edit Audio</h3>
                    <button @click="isEditOpen = false" class="p-1 hover:bg-muted rounded text-muted-foreground">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="space-y-2">
                         <label class="text-sm font-medium">Title</label>
                         <input v-model="editForm.title" type="text" placeholder="Song Title" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                    </div>

                    <div class="space-y-2">
                         <label class="text-sm font-medium">Replace Audio (Optional)</label>
                         <div class="border-2 border-dashed border-input rounded-lg p-8 text-center bg-muted/20 hover:bg-muted/40 transition-colors relative">
                             <input type="file" accept="audio/*" @change="handleEditFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                             <div v-if="editForm.file" class="flex flex-col items-center gap-2">
                                 <CheckCircle class="w-8 h-8 text-green-500" />
                                 <p class="text-sm font-medium">{{ editForm.file.name }}</p>
                                 <p class="text-xs text-muted-foreground">Click to change</p>
                             </div>
                             <div v-else class="flex flex-col items-center gap-2 text-muted-foreground">
                                 <Upload class="w-8 h-8" />
                                 <p class="text-sm font-medium">Drag & drop to replace</p>
                             </div>
                         </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button @click="isEditOpen = false" class="px-4 py-2 text-sm font-medium border rounded-md hover:bg-muted transition-colors">
                            Cancel
                        </button>
                        <button 
                            @click="updateSong" 
                            :disabled="isUpdating || !editForm.title"
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

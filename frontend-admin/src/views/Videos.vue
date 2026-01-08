<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { api } from '@/lib/api'
import { useToast } from '@/lib/toast'
import { Loader2, Search, Upload, Trash2, X, CheckCircle, Edit } from 'lucide-vue-next'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'

const videos = ref<any[]>([])
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

const openEdit = (video: any) => {
    editForm.value = {
        id: video.id,
        title: video.title,
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

const updateVideo = async () => {
    if (!editForm.value.title) return
    
    openConfirm('Confirm Update', 'Are you sure you want to save changes?', async () => {
        isUpdating.value = true
        try {
            const formData = new FormData()
            formData.append('title', editForm.value.title)
            if (editForm.value.file) {
                formData.append('file', editForm.value.file)
            }
            formData.append('_method', 'PATCH')

            const res = await api.post(`/videos/${editForm.value.id}`, formData)
            
            if (res.ok) {
                fetchVideos()
                isEditOpen.value = false
                toast.success('Video updated successfully')
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

const fetchVideos = async () => {
    loading.value = true
    try {
        const query = search.value ? `?search=${search.value}` : ''
        const res = await api.get(`/videos${query}`)
        if (res.ok) {
            const data = await res.json()
            videos.value = data.data || []
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
    timeout = setTimeout(fetchVideos, 500)
}

// Delete Logic
const isDeleting = ref(false)

const openDeleteModal = (id: number) => {
    openConfirm('Delete Video', 'Are you sure you want to delete this video? This action cannot be undone.', async () => {
        isDeleting.value = true
        try {
            await api.delete(`/videos/${id}`)
            fetchVideos()
            toast.success('Video deleted successfully')
        } catch(e) {
            console.error(e)
            toast.error('Failed to delete video')
        } finally {
            isDeleting.value = false
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

    openConfirm('Confirm Upload', 'Are you sure you want to upload this video?', async () => {
        isUploading.value = true
        try {
            const formData = new FormData()
            formData.append('title', uploadForm.value.title)
            if (uploadForm.value.file) formData.append('file', uploadForm.value.file)
            
            const res = await api.post('/videos', formData)
            
            if (res.ok) {
                isUploadOpen.value = false
                uploadForm.value = { title: '', file: null } // Reset
                fetchVideos()
                toast.success('Video uploaded successfully')
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

onMounted(fetchVideos)
</script>

<template>
    <div class="space-y-6 relative">
         <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight">Video Library</h2>
                <p class="text-muted-foreground">Manage story and tutorial videos.</p>
            </div>
            <button @click="isUploadOpen = true" class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium text-primary-foreground bg-primary rounded-md hover:bg-primary/90 transition-colors w-full md:w-auto">
                <Upload class="w-4 h-4 mr-2" />
                Upload Video
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
                        placeholder="Search videos..." 
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring pl-9"
                     />
                 </div>
            </div>

            <!-- Grid for Videos -->
            <div class="p-6">
                <div v-if="loading" class="flex justify-center py-12"><Loader2 class="w-8 h-8 animate-spin text-muted-foreground"/></div>
                <div v-else-if="videos.length === 0" class="text-center py-12 text-muted-foreground">No videos found.</div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="video in videos" :key="video.id" class="rounded-lg border bg-card overflow-hidden shadow-sm flex flex-col">
                        <div class="aspect-video bg-black relative group">
                            <video controls class="w-full h-full">
                                <source :src="`/${video.file}`" type="application/x-mpegURL">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold truncate">{{ video.title }}</h3>
                             <div class="flex items-center justify-end mt-2 gap-2">
                                <button @click="openEdit(video)" class="text-foreground hover:bg-muted p-2 rounded-md" title="Edit">
                                    <Edit class="w-4 h-4"/>
                                </button>
                                <button @click="openDeleteModal(video.id)" class="text-destructive hover:bg-destructive/10 p-2 rounded-md" title="Delete">
                                    <Trash2 class="w-4 h-4"/>
                                </button>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <div v-if="isUploadOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-background/80 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-lg border bg-card p-6 shadow-lg animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold">Upload Video</h3>
                    <button @click="isUploadOpen = false" class="p-1 hover:bg-muted rounded text-muted-foreground">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="space-y-2">
                         <label class="text-sm font-medium">Title</label>
                         <input v-model="uploadForm.title" type="text" placeholder="Video Title" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                    </div>



                    <div class="space-y-2">
                         <label class="text-sm font-medium">Video File</label>
                         <div class="border-2 border-dashed border-input rounded-lg p-8 text-center bg-muted/20 hover:bg-muted/40 transition-colors relative">
                             <input type="file" accept="video/*" @change="handleFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
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
                    <h3 class="text-lg font-semibold">Edit Video</h3>
                    <button @click="isEditOpen = false" class="p-1 hover:bg-muted rounded text-muted-foreground">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="space-y-2">
                         <label class="text-sm font-medium">Title</label>
                         <input v-model="editForm.title" type="text" placeholder="Video Title" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                    </div>



                    <div class="space-y-2">
                         <label class="text-sm font-medium">Replace Video (Optional)</label>
                         <div class="border-2 border-dashed border-input rounded-lg p-8 text-center bg-muted/20 hover:bg-muted/40 transition-colors relative">
                             <input type="file" accept="video/*" @change="handleEditFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
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
                            @click="updateVideo" 
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

        <!-- Delete Confirmation Modal -->
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


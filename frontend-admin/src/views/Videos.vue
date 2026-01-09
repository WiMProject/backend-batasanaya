<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { api } from '@/lib/api'
import { useToast } from '@/lib/toast'
import { Loader2, Search, Upload, Trash2, X, CheckCircle, Edit, Play, Film } from 'lucide-vue-next'
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
    waitForCompletion: true,
    onConfirm: async () => {}
})

const openConfirm = (
    title: string, 
    description: string, 
    onConfirm: () => Promise<void> | void, 
    variant: 'primary' | 'destructive' = 'primary',
    waitForCompletion: boolean = true
) => {
    confirmState.value = {
        isOpen: true,
        title,
        description,
        isLoading: false,
        variant,
        waitForCompletion,
        onConfirm: async () => { await onConfirm() }
    }
}

const handleConfirm = async () => {
    if (confirmState.value.waitForCompletion) {
        confirmState.value.isLoading = true
        try {
            await confirmState.value.onConfirm()
            confirmState.value.isOpen = false
        } catch (e) {
            console.error(e)
        } finally {
            confirmState.value.isLoading = false
        }
    } else {
        // Close immediately
        confirmState.value.isOpen = false
        confirmState.value.onConfirm()
    }
}

// Upload State
const isUploadOpen = ref(false)
const isUploading = ref(false)
const uploadProgress = ref(0)
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
const openDeleteModal = (id: number) => {
    openConfirm('Delete Video', 'Are you sure you want to delete this video? This action cannot be undone.', async () => {
        try {
            await api.delete(`/videos/${id}`)
            fetchVideos()
            toast.success('Video deleted successfully')
        } catch(e) {
            console.error(e)
            toast.error('Failed to delete video')
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

function generateUUID() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}

const handleUpload = async () => {
    if (!uploadForm.value.title || !uploadForm.value.file) {
        toast.error("Please enter title and select file.")
        return
    }

    const formData = new FormData()
    const uploadId = generateUUID()
    formData.append('title', uploadForm.value.title)
    formData.append('upload_id', uploadId)
    if (uploadForm.value.file) formData.append('file', uploadForm.value.file)

    openConfirm('Confirm Upload', 'Are you sure you want to upload this video?', async () => {
        isUploading.value = true
        uploadProgress.value = 0 // Start
        
        // Simulating visual start
        setTimeout(() => { if(isUploading.value && uploadProgress.value < 5) uploadProgress.value = 5 }, 100)
        
        let pollInterval: any = null

        try {
            pollInterval = setInterval(async () => {
                try {
                    const res = await api.get(`/videos/progress/${uploadId}`)
                    if (res.ok) {
                        const data = await res.json()
                        if (data.progress > uploadProgress.value) {
                            uploadProgress.value = data.progress
                        }
                    }
                } catch(e) {}
            }, 1000)

            const res = await api.post('/videos', formData)
            
            clearInterval(pollInterval)
            
            if (res.ok) {
                // SUCCESS PATH
                // 1. Force visual poll completion
                uploadProgress.value = 100
                
                // 2. Wait for user to register "100%" (1.5 seconds)
                await new Promise(r => setTimeout(r, 1500))
                
                // 3. Close Modal
                isUploadOpen.value = false
                
                // 4. Wait for modal animation to finish
                await new Promise(r => setTimeout(r, 500))
                
                // 5. Reset internal states
                isUploading.value = false
                uploadForm.value = { title: '', file: null } 
                
                // 6. Refresh data
                fetchVideos()
                toast.success('Video uploaded successfully')
            } else {
                throw new Error('Upload failed')
            }
        } catch (e) {
            console.error(e)
            // ERROR PATH
            // Stop uploading, let user see the form again to retry
            isUploading.value = false 
            const msg = e instanceof Error ? e.message : 'Upload failed'
            toast.error(msg)
            
            if (pollInterval) clearInterval(pollInterval)
        }
    }, 'primary', false)
}

onMounted(fetchVideos)
</script>

<template>
    <div class="space-y-8 animate-in fade-in duration-500">
         <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-6 border-b border-border/40">
            <div>
                 <h2 class="text-3xl font-bold tracking-tight text-foreground">Video Library</h2>
                <p class="text-muted-foreground mt-2 text-lg">Manage story content and tutorial clips.</p>
            </div>
            <button @click="isUploadOpen = true" class="inline-flex items-center justify-center h-10 px-6 py-2 text-sm font-medium text-white transition-colors bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm dark:bg-slate-50 dark:text-slate-900 dark:hover:bg-slate-200">
                <Upload class="w-5 h-5 mr-2" />
                Upload Video
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
                placeholder="Find videos..."
            />
        </div>

        <div class="min-h-[200px]">
             <!-- Loading State -->
            <div v-if="loading" class="flex justify-center py-20">
                <div class="flex flex-col items-center gap-4">
                    <Loader2 class="w-10 h-10 animate-spin text-primary"/>
                    <p class="text-muted-foreground">Loading library...</p>
                </div>
            </div>
            
            <!-- Empty State -->
            <div v-else-if="videos.length === 0" class="flex flex-col items-center justify-center py-20 text-muted-foreground border-2 border-dashed border-border/50 rounded-3xl bg-card/20">
                <Film class="w-16 h-16 opacity-20 mb-4" />
                <p class="text-lg font-medium">No videos found.</p>
                <p class="text-sm">Upload a new video to get started.</p>
            </div>
            
            <!-- Video Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div 
                    v-for="video in videos" 
                    :key="video.id" 
                    class="group relative bg-card rounded-2xl overflow-hidden shadow-md hover:shadow-xl hover:shadow-primary/10 transition-all duration-300 hover:-translate-y-1 border border-border/50"
                >
                    <div class="aspect-video bg-black relative overflow-hidden">
                        <video 
                            class="w-full h-full object-cover opacity-90 group-hover:opacity-100 transition-opacity" 
                            preload="metadata"
                        >
                            <source :src="`/${video.file}#t=0.5`" type="video/mp4">
                        </video>
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
                            <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center">
                                <Play class="w-6 h-6 text-white ml-1" />
                            </div>
                        </div>
                         <div class="absolute inset-0 z-10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                              <video controls class="w-full h-full absolute inset-0 opacity-0 hover:opacity-100 transition-opacity">
                                <source :src="`/${video.file}`" type="application/x-mpegURL">
                            </video>
                         </div>
                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-foreground truncate text-lg group-hover:text-primary transition-colors">{{ video.title }}</h3>
                        <div class="flex items-center justify-between mt-4">
                            <span class="text-xs text-muted-foreground font-medium uppercase tracking-wider bg-secondary/50 px-2 py-1 rounded-md">
                                MP4
                            </span>
                             <div class="flex gap-1">
                                <button @click="openEdit(video)" class="p-2 hover:bg-blue-50 text-muted-foreground hover:text-blue-600 rounded-lg transition-colors relative z-20" title="Edit">
                                    <Edit class="w-4 h-4"/>
                                </button>
                                <button @click="openDeleteModal(video.id)" class="p-2 hover:bg-red-50 text-muted-foreground hover:text-red-600 rounded-lg transition-colors relative z-20" title="Delete">
                                    <Trash2 class="w-4 h-4"/>
                                </button>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <Teleport to="body">
            <div v-if="isUploadOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
                <div class="w-full max-w-lg rounded-2xl border bg-card p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-primary to-purple-600 bg-clip-text text-transparent">Upload Video</h3>
                        <button v-if="!isUploading" @click="isUploadOpen = false" class="p-2 hover:bg-muted rounded-full text-muted-foreground transition-colors">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                     <div v-if="isUploading" class="flex flex-col items-center justify-center py-10 space-y-6 text-center animate-in fade-in duration-500">
                        <div class="relative w-32 h-32 flex items-center justify-center">
                            <!-- Circular Progress -->
                            <svg class="w-full h-full transform -rotate-90">
                                <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent" class="text-muted/20" />
                                <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent" :stroke-dasharray="364" :stroke-dashoffset="364 - (364 * uploadProgress) / 100" class="text-primary transition-all duration-1000 ease-out" stroke-linecap="round" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-3xl font-bold text-foreground">{{ uploadProgress }}%</span>
                            </div>
                        </div>
                        <div class="space-y-2 max-w-xs mx-auto">
                            <h3 class="text-xl font-bold text-foreground">
                                {{ uploadProgress < 5 ? 'Initiating Upload...' : (uploadProgress < 100 ? 'Processing Video' : 'Finalizing...') }}
                            </h3>
                            <p class="text-muted-foreground text-sm">
                                {{ uploadProgress < 5 ? 'Preparing secure connection...' : 'Converting to HLS format for streaming...' }}
                            </p>
                        </div>
                        <!-- Linear Progress Bar -->
                        <div class="w-full max-w-xs bg-muted/50 rounded-full h-1.5 overflow-hidden">
                             <div class="h-full bg-primary transition-all duration-1000 ease-in-out" :style="{ width: `${uploadProgress}%` }"></div>
                        </div>
                    </div>

                    <div v-else class="space-y-6 animate-in fade-in">
                        <div class="space-y-2">
                             <label class="text-sm font-semibold">Video Title</label>
                             <input v-model="uploadForm.title" type="text" placeholder="e.g. Tutorial Level 1" class="flex h-11 w-full rounded-xl border border-input bg-muted/30 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 transition-all" />
                        </div>

                        <div class="space-y-2">
                             <label class="text-sm font-semibold">Video File</label>
                             <div class="border-2 border-dashed border-input rounded-xl p-10 text-center bg-muted/20 hover:bg-muted/40 transition-colors relative group">
                                 <input type="file" accept="video/*" @change="handleFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
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
                                     <p class="text-xs opacity-60 max-w-[200px]">Supports MP4, WebM. Max 50MB recommended.</p>
                                 </div>
                             </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button @click="isUploadOpen = false" class="px-5 py-2.5 text-sm font-medium border rounded-xl hover:bg-muted transition-colors">
                                Cancel
                            </button>
                            <button 
                                @click="handleUpload" 
                                :disabled="!uploadForm.file || !uploadForm.title"
                                class="px-5 py-2.5 text-sm font-bold bg-primary text-white rounded-xl hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <Upload class="w-4 h-4" />
                                Upload Video
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Edit Modal -->
        <Teleport to="body">
            <div v-if="isEditOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
                <div class="w-full max-w-lg rounded-2xl border bg-card p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold">Edit Video</h3>
                        <button @click="isEditOpen = false" class="p-2 hover:bg-muted rounded-full text-muted-foreground transition-colors">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="space-y-6">
                        <div class="space-y-2">
                             <label class="text-sm font-semibold">Video Title</label>
                             <input v-model="editForm.title" type="text" class="flex h-11 w-full rounded-xl border border-input bg-muted/30 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 transition-all" />
                        </div>

                        <div class="space-y-2">
                             <label class="text-sm font-semibold">Replace File (Optional)</label>
                             <div class="border-2 border-dashed border-input rounded-xl p-8 text-center bg-muted/20 hover:bg-muted/40 transition-colors relative">
                                 <input type="file" accept="video/*" @change="handleEditFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                                 <div v-if="editForm.file" class="flex flex-col items-center gap-2">
                                     <CheckCircle class="w-6 h-6 text-green-500" />
                                     <p class="text-sm font-medium">{{ editForm.file.name }}</p>
                                     <p class="text-xs text-muted-foreground">Click to change</p>
                                 </div>
                                 <div v-else class="flex flex-col items-center gap-2 text-muted-foreground">
                                     <Upload class="w-6 h-6" />
                                     <p class="text-sm font-medium">Drag & drop to replace</p>
                                 </div>
                             </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button @click="isEditOpen = false" class="px-5 py-2.5 text-sm font-medium border rounded-xl hover:bg-muted transition-colors">
                                Cancel
                            </button>
                            <button 
                                @click="updateVideo" 
                                :disabled="isUpdating || !editForm.title"
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

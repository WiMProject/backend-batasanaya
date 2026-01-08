<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { api } from '@/lib/api'
import { useToast } from '@/lib/toast'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import { Loader2, Search, Upload, Trash2, X, CheckCircle, Edit, Music as MusicIcon, Play, Pause, Clock } from 'lucide-vue-next'

const songs = ref<any[]>([])
const loading = ref(true)
const search = ref('')
const toast = useToast()

// Audio Player State
const currentPlaying = ref<string | null>(null)
const audioRefs = ref<Record<string, HTMLAudioElement>>({})
const durations = ref<Record<string, string>>({})

const togglePlay = (id: string) => {
    // Stop others
    if (currentPlaying.value && currentPlaying.value !== id) {
        const prevAudio = audioRefs.value[currentPlaying.value]
        if (prevAudio) {
            prevAudio.pause()
            prevAudio.currentTime = 0
        }
    }

    const audio = audioRefs.value[id]
    if (!audio) return

    if (audio.paused) {
        audio.play()
        currentPlaying.value = id
    } else {
        audio.pause()
        currentPlaying.value = null
    }
}

const onLoadedMetadata = (e: Event, id: string) => {
    const audio = e.target as HTMLAudioElement
    const min = Math.floor(audio.duration / 60)
    const sec = Math.floor(audio.duration % 60)
    durations.value[id] = `${min}:${sec < 10 ? '0' : ''}${sec}`
}

const onEnded = () => {
    currentPlaying.value = null
}

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
            // Stop playing if deleted
            if (currentPlaying.value === String(id)) {
                audioRefs.value[id]?.pause()
                currentPlaying.value = null
            }
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
        toast.error("Please enter name and select file.")
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
    <div class="space-y-8 animate-in fade-in duration-500">
        <!-- Header Section -->
         <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-6 border-b border-border/40">
            <div>
                 <h2 class="text-3xl font-bold tracking-tight text-foreground">Audio Library</h2>
                <p class="text-muted-foreground mt-2 text-lg">Manage bgm and sfx for the game experience.</p>
            </div>
            <button @click="isUploadOpen = true" class="inline-flex items-center justify-center h-10 px-6 py-2 text-sm font-medium text-white transition-colors bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm dark:bg-slate-50 dark:text-slate-900 dark:hover:bg-slate-200">
                <Upload class="w-4 h-4 mr-2" />
                Upload Audio
            </button>
        </div>

        <!-- Search Bar -->
        <div class="relative w-full max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Search class="h-5 w-5 text-muted-foreground" />
            </div>
            <input 
                v-model="search"
                @input="onSearch"
                type="text"
                class="block w-full pl-10 pr-3 py-3 border border-input rounded-xl leading-5 bg-card/50 backdrop-blur-sm placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm transition-all shadow-sm"
                placeholder="Search by name..."
            />
        </div>

        <!-- Songs Grid -->
        <div v-if="loading" class="flex justify-center py-20">
            <Loader2 class="w-10 h-10 animate-spin text-primary" />
        </div>
        
        <div v-else-if="songs.length === 0" class="text-center py-20 bg-card/30 rounded-3xl border border-dashed border-border">
            <MusicIcon class="mx-auto h-12 w-12 text-muted-foreground/50 mb-4" />
            <h3 class="mt-2 text-sm font-semibold text-foreground">No audio files</h3>
            <p class="mt-1 text-sm text-muted-foreground">Get started by uploading your first audio clip.</p>
            <div class="mt-6">
                <button @click="isUploadOpen = true" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-primary-foreground bg-primary hover:bg-primary/90">
                    <Upload class="-ml-1 mr-2 h-5 w-5" aria-hidden="true" />
                    Upload Songs
                </button>
            </div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div 
                v-for="song in songs" 
                :key="song.id" 
                class="group bg-card hover:bg-card/80 border border-border/50 rounded-2xl p-5 transition-all duration-300 hover:shadow-xl hover:shadow-primary/5 hover:-translate-y-1 relative overflow-hidden"
            >
                <!-- Playing Animation Glow -->
                <div v-if="currentPlaying === song.id" class="absolute inset-0 pointer-events-none border-2 border-primary/30 rounded-2xl animate-pulse"></div>

                <div class="flex items-start justify-between mb-4">
                    <div class="p-3 rounded-full bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-colors duration-300">
                        <MusicIcon class="w-6 h-6" />
                    </div>
                    <div class="flex gap-1">
                        <button @click="openEdit(song)" class="p-2 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-full transition-colors" title="Edit Name">
                            <Edit class="w-4 h-4" />
                        </button>
                        <button @click="openDeleteModal(song.id)" class="p-2 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-full transition-colors" title="Delete Audio">
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <h3 class="font-bold text-lg leading-tight truncate pr-2 mb-1" :title="song.title">{{ song.title || 'Untitled' }}</h3>
                <p class="text-xs text-muted-foreground truncate font-mono mb-4">{{ song.file_name }}</p>

                <!-- Audio Player Visual -->
                <div class="bg-muted/30 rounded-xl p-3 flex items-center justify-between gap-3 border border-border/50">
                    <button 
                        @click="togglePlay(song.id)"
                        class="w-10 h-10 flex items-center justify-center rounded-full bg-primary text-white shadow-lg hover:scale-105 active:scale-95 transition-all"
                    >
                        <Pause v-if="currentPlaying === song.id" class="w-4 h-4 fill-current" />
                        <Play v-else class="w-4 h-4 fill-current ml-0.5" />
                    </button>
                    
                    <div class="flex-1 flex flex-col justify-center h-full">
                         <!-- Hidden Audio Element -->
                         <audio 
                            :ref="(el) => audioRefs[song.id] = (el as HTMLAudioElement)"
                            :src="`/${song.file}`"
                            @ended="onEnded()"
                            @loadedmetadata="(e) => onLoadedMetadata(e, song.id)"
                            preload="metadata"
                            class="hidden"
                        ></audio>
                        
                        <!-- Visualize Bars (Fake) -->
                        <div class="flex items-center gap-0.5 h-6 opacity-60">
                            <div v-for="i in 12" :key="i" 
                                class="w-1 bg-primary rounded-full transition-all duration-300"
                                :class="currentPlaying === song.id ? 'animate-music-bar' : 'h-2'"
                                :style="{ animationDelay: `${i * 0.1}s` }"
                            ></div>
                        </div>
                    </div>

                    <div class="text-xs font-medium text-muted-foreground font-mono flex items-center gap-1">
                        <Clock class="w-3 h-3" />
                        {{ durations[song.id] || '--:--' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <div v-if="isUploadOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
            <div class="w-full max-w-lg rounded-2xl border bg-card p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-foreground">Upload Audio</h3>
                    <button @click="isUploadOpen = false" class="p-2 hover:bg-muted rounded-full text-muted-foreground transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="space-y-2">
                         <label class="text-sm font-semibold">Name</label>
                         <input v-model="uploadForm.title" type="text" placeholder="e.g. Background Menu Music" class="flex h-11 w-full rounded-xl border border-input bg-muted/30 px-4 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 transition-all" />
                    </div>

                    <div class="space-y-2">
                         <label class="text-sm font-semibold">Audio File</label>
                         <div class="border-2 border-dashed border-primary/20 hover:border-primary/50 rounded-2xl p-10 text-center bg-primary/5 hover:bg-primary/10 transition-colors relative cursor-pointer group">
                             <input type="file" accept="audio/*" @change="handleFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                             <div v-if="uploadForm.file" class="flex flex-col items-center gap-3">
                                 <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                                    <CheckCircle class="w-6 h-6" />
                                 </div>
                                 <div>
                                     <p class="text-sm font-bold text-foreground">{{ uploadForm.file.name }}</p>
                                     <p class="text-xs text-muted-foreground mt-1">Click to replace</p>
                                 </div>
                             </div>
                             <div v-else class="flex flex-col items-center gap-3 text-muted-foreground group-hover:text-primary transition-colors">
                                 <div class="p-3 bg-background rounded-full shadow-sm">
                                     <Upload class="w-6 h-6" />
                                 </div>
                                 <div>
                                    <p class="text-sm font-bold text-foreground mb-1">Click to upload</p>
                                    <p class="text-xs">MP3, WAV, OGG (Max 5MB)</p>
                                 </div>
                             </div>
                         </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-6">
                        <button @click="isUploadOpen = false" class="px-5 py-2.5 text-sm font-medium border rounded-xl hover:bg-muted transition-colors">
                            Cancel
                        </button>
                        <button 
                            @click="handleUpload" 
                            :disabled="isUploading || !uploadForm.file || !uploadForm.title"
                            class="px-5 py-2.5 text-sm font-bold bg-primary text-white rounded-xl hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center gap-2 disabled:opacity-50 disabled:shadow-none"
                        >
                            <Loader2 v-if="isUploading" class="w-4 h-4 animate-spin" />
                            {{ isUploading ? 'Uploading...' : 'Upload Audio' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="isEditOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
            <div class="w-full max-w-lg rounded-2xl border bg-card p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold">Edit Audio</h3>
                    <button @click="isEditOpen = false" class="p-2 hover:bg-muted rounded-full text-muted-foreground transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="space-y-2">
                         <label class="text-sm font-semibold">Name</label>
                         <input v-model="editForm.title" type="text" placeholder="Song Name" class="flex h-11 w-full rounded-xl border border-input bg-muted/30 px-4 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 transition-all" />
                    </div>

                    <div class="space-y-2">
                         <label class="text-sm font-semibold">Replace Audio (Optional)</label>
                         <div class="border-2 border-dashed border-input hover:border-primary/50 rounded-2xl p-6 text-center bg-muted/20 hover:bg-muted/40 transition-colors relative">
                             <input type="file" accept="audio/*" @change="handleEditFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                             <div v-if="editForm.file" class="flex items-center justify-center gap-2">
                                 <CheckCircle class="w-5 h-5 text-green-500" />
                                 <span class="text-sm font-medium">{{ editForm.file.name }}</span>
                             </div>
                             <div v-else class="flex flex-col items-center gap-1 text-muted-foreground">
                                 <Upload class="w-5 h-5" />
                                 <span class="text-sm">Click to replace file</span>
                             </div>
                         </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-6">
                        <button @click="isEditOpen = false" class="px-5 py-2.5 text-sm font-medium border rounded-xl hover:bg-muted transition-colors">
                            Cancel
                        </button>
                        <button 
                            @click="updateSong" 
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

<style scoped>
@keyframes music-bar {
  0% { height: 0.5rem; }
  50% { height: 1.5rem; }
  100% { height: 0.5rem; }
}

.animate-music-bar {
  animation: music-bar 0.8s ease-in-out infinite;
}
</style>

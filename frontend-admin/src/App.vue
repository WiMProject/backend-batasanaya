<script setup lang="ts">
// Root Component now just handles routing
import Toaster from '@/components/ui/Toaster.vue'
</script>

<template>
  <router-view />
  <Toaster />
</template>

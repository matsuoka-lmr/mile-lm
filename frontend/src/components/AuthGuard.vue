<template>
  <slot v-if="store.isLogined"></slot>
  <v-empty-state v-else title="Loading...">
    <template #media>
      <v-progress-circular color="primary" indeterminate size="40" class="mb-4"></v-progress-circular>
    </template>
  </v-empty-state>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'

const store = useAuthStore()
if (!store.isLogined) store.reLogin()
</script>

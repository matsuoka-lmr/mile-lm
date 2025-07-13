<template>
  <suspense>
    <TaskRunner :task="task">
      <slot></slot>
    </TaskRunner>
    <template #fallback>
      <v-empty-state title="Loading...">
        <template #media>
          <v-progress-circular color="primary" indeterminate size="40" class="mb-4"></v-progress-circular>
        </template>
      </v-empty-state>
    </template>
  </suspense>
</template>

<script setup lang="ts">
const { task } = defineProps<{ task: () => Promise<unknown> }>()
</script>

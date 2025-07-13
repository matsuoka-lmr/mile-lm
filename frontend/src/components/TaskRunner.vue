<template>
  <slot></slot>
</template>

<script setup lang="ts">
const { task } = defineProps<{ task: () => Promise<unknown> }>()
if (task) {
  console.log('waiting...')
  await task()
  console.log('done!')
}
</script>

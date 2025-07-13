<script setup lang="ts">
import { type MenuItem } from '@/config/menu'
defineProps<{ item: MenuItem; level?: number }>()
</script>

<template>
  <!---Single Item-->
  <div class="mb-1">
    <v-list-item
      :to="item.type === 'external' ? '' : item.to"
      :href="item.type === 'external' ? item.to : ''"
      rounded
      :class="`bg-hover-${item.BgColor || 'primary'}`"
      :color="item.BgColor || 'primary'"
      :ripple="false"
      :disabled="item.disabled"
      :target="item.type === 'external' ? '_blank' : ''"
    >
      <!---If icon-->
      <template #prepend>
        <div :class="['navbox', `bg-hover-${item.BgColor || 'primary'}`]" :color="item.BgColor || 'primary'">
          <span class="icon-box">
            <v-icon
              :icon="item.icon"
              :size="level ? 5 : 20"
              fill="currentColor"
              stroke-width="1.5"
              :class="['iconClass', 'position-relative', 'z-index-2', `texthover-${item.BgColor || 'primary'}`]"
            />
          </span>
        </div>
      </template>
      <v-list-item-title class="text-subtitle-1 font-weight-medium" :color="item.BgColor || 'primary'">{{ item.title }}</v-list-item-title>
      <!---If Caption-->
      <v-list-item-subtitle v-if="item.subCaption" class="text-caption mt-n1 hide-menu">
        {{ item.subCaption }}
      </v-list-item-subtitle>
      <!---If any chip or label-->
      <!-- <template v-if="item.chip" #append>
        <v-chip
          :color="item.chipColor"
          class="sidebarchip hide-menu"
          :size="item.chipIcon ? 'x-small' : 'x-small'"
          :variant="item.chipVariant"
          :prepend-icon="item.chipIcon"
        >
          {{ item.chip }}
        </v-chip>
      </template> -->
    </v-list-item>
  </div>
</template>

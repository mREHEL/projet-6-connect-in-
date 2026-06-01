<script setup>
import EditPost from './EditPost.vue'

const props = defineProps({
  posts: { type: Array, default: () => [] },
  currentUser: { type: Object, default: null },
})

const emit = defineEmits(['post-deleted', 'update-post'])
</script>

<template>
  <div>
    <EditPost
      v-for="p in posts"
      :key="p.id"
      :post="p"
      :currentUser="currentUser"
      @update-post="emit('update-post', $event)"
      @post-deleted="emit('post-deleted', $event)"
    />
  </div>
</template>
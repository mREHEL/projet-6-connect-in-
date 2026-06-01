<template>
  <Transition
    enter-active-class="transform ease-out duration-300 transition"
    enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
    leave-active-class="transition ease-in duration-100"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="showNotification" 
      class="fixed top-5 right-5 z-[100] min-w-[300px] shadow-2xl rounded-xl p-4 flex items-center gap-3 border backdrop-blur-md"
      :class="isError ? 'bg-red-50 border-red-200 text-red-800' : 'bg-emerald-50 border-emerald-200 text-emerald-800'"
    >
      <div class="flex-shrink-0 text-xl">
        <span v-if="isError">⚠️</span>
        <span v-else>✅</span>
      </div>

      <p class="font-medium text-sm">{{ notificationMsg }}</p>

      <button @click="showNotification = false" class="ml-auto opacity-50 hover:opacity-100">
        ✕
      </button>
    </div>
  </Transition>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { emitter } from '../utils/emitter';

const showNotification = ref(false);
const notificationMsg = ref('');
const isError = ref(false);
let timer = null;

const handleNotify = (data) => {
    clearTimeout(timer);
    notificationMsg.value = data.message;
    isError.value = data.type === 'error';
    showNotification.value = true;
    timer = setTimeout(() => showNotification.value = false, 4000);
};

onMounted(() => {
    emitter.on('notify', handleNotify); // J'écoute
});

onUnmounted(() => {
    emitter.off('notify', handleNotify); // J'arrête d'écouter
    clearTimeout(timer);
});
</script>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.4s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateY(20px); }
</style>
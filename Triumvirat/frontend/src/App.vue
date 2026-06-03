<script setup>
import { RouterView } from "vue-router";
import Navbar from "./components/NavBar.vue";
import { onMounted } from "vue";
import { authService } from "./services/authService";
import Toast from "./components/Toast.vue";
import cover from "./assets/pillar.jpg";
import { updateAuthState } from "./utils/authEvents.js";
import { isDemoMode } from "./services/demoStore.js";

onMounted(async () => {
    if (isDemoMode && !localStorage.getItem("token")) {
        authService.startDemoSession();
        return;
    }

    const token = localStorage.getItem("token");
    if (token) {
        try {
            await authService.getProfile();
        } catch (error) {
            localStorage.removeItem("token");
            localStorage.removeItem("user");
            updateAuthState();
        }
    }
});
</script>

<template>
    <div class="flex">
        <Navbar />

        <main
            class="min-h-screen flex-1 pb-20 md:pb-0 md:pl-24 bg-no-repeat bg-center bg-fixed bg-cover"
            :style="{ backgroundImage: `url(${cover})` }"
        >
            <div class="">
                <Toast />
                <RouterView />
            </div>
        </main>
    </div>
</template>

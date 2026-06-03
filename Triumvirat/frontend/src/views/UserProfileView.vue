<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { authState } from "../utils/authEvents.js";
import PostByUser from "../components/PostByUser.vue";
import precedentIcon from "../assets/precedent.png";
import defaultCover from "../assets/cover.png";
import { userService } from "../services/userService.js";

const route = useRoute();
const router = useRouter();

const user = ref(null);
const loading = ref(true);
const errorMessage = ref("");

const apiUrl = "http://localhost:8000";
const userId = computed(() => {
	const value = Number(route.params.id);
	return Number.isFinite(value) ? value : null;
});

const currentUserId = computed(() => authState.value.user?.id ?? null);

const coverUrl = computed(() => {
	if (!user.value?.cover_image_path) return defaultCover;
	return `${apiUrl}/storage/${user.value.cover_image_path}`;
});

const profilePhotoUrl = computed(() => {
	if (!user.value?.profile_photo_path) return "";
	return `${apiUrl}/storage/${user.value.profile_photo_path}`;
});

const redirectIfOwnProfile = () => {
	if (!userId.value || !currentUserId.value) return false;
	if (String(userId.value) !== String(currentUserId.value)) return false;
	router.replace("/profil");
	return true;
};

const goBack = () => {
	router.back();
};

const fetchUser = async () => {
	if (redirectIfOwnProfile()) return;
	if (!userId.value) {
		errorMessage.value = "Identifiant utilisateur invalide.";
		user.value = null;
		loading.value = false;
		return;
	}

	try {
		loading.value = true;
		errorMessage.value = "";
		user.value = await userService.getById(userId.value);
	} catch (err) {
		console.error("Erreur lors de la récupération de l'utilisateur:", err);
		errorMessage.value = err.message || "Impossible de charger l'utilisateur.";
		user.value = null;
	} finally {
		loading.value = false;
	}
};

onMounted(fetchUser);
watch([() => route.params.id, () => currentUserId.value], fetchUser);
</script>

<template>
	<div class="flex justify-between mx-4 lg:mx-96 bg-gray-100 p-3 md:p-5 rounded shadow-lg mt-4">
		<div class="ml-1 mr-3 flex items-center">
			<button type="button" class="text-gray-800 hover:text-gray-600 transition" @click="goBack">
				<img
					:src="precedentIcon"
					alt="Retour"
					class="h-8 w-8 md:h-10 md:w-10 hover:scale-110 transition-transform duration-200 shadow-md shadow-gray-400/30 rounded-full"
				/>
			</button>
			<h1 class="ml-3 md:ml-4 text-xl md:text-4xl font-bold">Profil</h1>
		</div>
	</div>

	<div v-if="loading" class="text-center py-10">
		<p class="text-lg text-gray-600">Chargement...</p>
	</div>

	<div v-else-if="errorMessage" class="text-center py-10">
		<p class="text-lg text-red-600">{{ errorMessage }}</p>
	</div>

	<div v-else-if="user" class="mt-4 md:mt-5 p-4 md:p-6 bg-gray-100 rounded-xl shadow-xl mx-4 lg:mx-96 relative">
		<div class="relative mb-4 h-32 md:h-96 w-full overflow-hidden rounded-lg">
			<img class="h-full w-full object-fill transition duration-300" :src="coverUrl" alt="" />
		</div>

		<div class="absolute top-20 md:top-72 left-4 md:left-10 z-10">
			<div
				class="w-24 h-24 md:w-44 md:h-44 bg-amber-100 flex items-center rounded-full justify-center text-black font-bold overflow-hidden border-4 border-white shadow-sm"
			>
				<img v-if="profilePhotoUrl" :src="profilePhotoUrl" class="w-full h-full object-cover" />
				<span v-else class="text-3xl md:text-6xl font-bold text-blue-600 uppercase">
					{{ user?.first_name?.charAt(0) }}.{{ user?.last_name?.charAt(0) }}
				</span>
			</div>
		</div>

		<h1 class="text-2xl md:text-4xl font-bold mb-2 md:mb-4 pt-12 md:pt-16 text-center md:text-left">
			<span class="text-lg md:text-2xl text-gray-400 pr-1">@</span>{{ user.username }}
		</h1>

		<h2 class="text-lg md:text-2xl font-medium mb-4 text-center md:text-left">{{ user.first_name }} {{ user.last_name }}</h2>

		<div class="flex flex-wrap items-center justify-between gap-4">
			<p class="text-gray-700 text-base md:text-xl border-t border-b border-gray-300 p-3 md:p-5 rounded-lg flex-1 text-center md:text-left">
				{{ user.bio || "Aucune biographie pour le moment." }}
			</p>
		</div>
	</div>

	<div v-if="userId" class="mt-4 p-4 md:p-6 bg-gray-100 rounded-xl shadow-xl mx-4 lg:mx-96 relative mb-6">
		<PostByUser :userId="userId" />
	</div>
</template>

<template>
	<div class="container mx-auto p-4 max-w-2xl bg-gray-50 rounded-xl shadow-lg mt-10">
		<div class="border-b-2 border-gray-200 mb-5 pb-2">
			<button
				class="text-blue-600 hover:text-blue-800 transition-colors font-medium flex items-center gap-2 text-lg"
				@click="goBack"
			>
				<span class="text-lg">←</span>
				<span class="hover:underline">Retour</span>
			</button>
		</div>

		<h1 class="text-2xl font-bold mb-4">Paramètres du compte</h1>

		<div v-if="currentUser" class="flex justify-center">
			<div class="border-2 border-gray-200 bg-white shadow-lg rounded-xl p-6 w-full mb-3">
				<div class="mb-3">
					<h2 class="text-2xl font-semibold mb-4 text-gray-800 text-center">Informations personnelles</h2>
					<div class="flex items-center my-6 gap-5 p-4 bg-gray-100/65 rounded-xl shadow-sm">
						<div class="relative">
							<div
								class="w-14 h-14 md:w-32 md:h-32 bg-amber-100 flex items-center rounded-full justify-center text-black font-bold overflow-hidden border-4 border-white shadow-sm"
							>
								<img
									v-if="currentUser.profile_photo_path"
									:src="profilePhotoUrl"
									alt="Photo de profil"
									class="w-full h-full object-cover"
								/>
								<span v-else class="text-2xl md:text-4xl font-bold text-blue-600 uppercase">
									{{ currentUser.first_name?.charAt(0) }}.{{ currentUser.last_name?.charAt(0) }}
								</span>
							</div>
						</div>
						<div class="text-start">
							<p class="text-2xl md:text-3xl font-bold text-gray-900">
								{{ currentUser.first_name }} {{ currentUser.last_name }}
							</p>
							<p class="text-lg text-gray-500 mt-2">{{ currentUser.email }}</p>
							<p class="text-base font-semibold text-blue-600">@{{ currentUser.username }}</p>
						</div>
					</div>
					<button
						class="w-full px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-all active:scale-95"
						@click="goToEditProfile"
					>
						Modifier le profil
					</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { authState } from "../utils/authEvents.js";

const router = useRouter();

const currentUser = computed(() => authState.value?.user);

const profilePhotoUrl = computed(() => {
	if (!currentUser.value?.profile_photo_path) return null;
	return `http://localhost:8000/storage/${currentUser.value.profile_photo_path}`;
});

onMounted(() => {
	// Si utilisateur pas connecté, rediriger vers la connexion
	if (!authState.value.isLoggedIn || !currentUser.value) {
		router.push("/connexion");
	}
});

function goBack() {
	router.back();
}

//Fonction pour aller sur la page d'édition du profil
function goToEditProfile() {
	router.push("/settings/edit-profile");
}
</script>

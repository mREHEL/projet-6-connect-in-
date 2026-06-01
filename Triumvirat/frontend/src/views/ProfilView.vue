<script setup>
import { ref, onMounted } from "vue";
import { authService } from "../services/authService";
import PostByUser from "../components/PostByUser.vue";
import precedentIcon from "../assets/precedent.png";
import defaultCover from "../assets/cover.png";

const user = ref(null);
const currentUser = ref(null);
const loading = ref(true);
const saving = ref(false);
const isEditing = ref(false);
const errorMessage = ref("");
const bioDraft = ref("");
const profilePhotoFile = ref(null);
const coverImageFile = ref(null);
const profilePhotoPreviewUrl = ref("");
const coverImagePreviewUrl = ref("");
const uploadProgress = ref(0);
const apiUrl = "http://localhost:8000";
const profileInputRef = ref(null);
const coverInputRef = ref(null);

const shouldDeleteCover = ref(false);
const shouldDeleteProfilePhoto = ref(false);

onMounted(async () => {
	try {
		const profileData = await authService.getProfile();
		user.value = profileData;
		currentUser.value = profileData.id;
		bioDraft.value = profileData.bio || "";
	} catch (error) {
		console.error("Erreur lors de la récupération du profil:", error);
		errorMessage.value = error.message || "Erreur lors de la récupération du profil";
	} finally {
		loading.value = false;
	}
});

const handleProfilePhotoChange = (event) => {
	const file = event.target.files?.[0] || null;
	profilePhotoFile.value = file;
	shouldDeleteProfilePhoto.value = false;
	if (profilePhotoPreviewUrl.value) {
		URL.revokeObjectURL(profilePhotoPreviewUrl.value);
		profilePhotoPreviewUrl.value = "";
	}
	if (file) {
		profilePhotoPreviewUrl.value = URL.createObjectURL(file);
	}
};

const handleCoverImageChange = (event) => {
	const file = event.target.files?.[0] || null;
	coverImageFile.value = file;
	shouldDeleteCover.value = false;
	if (coverImagePreviewUrl.value) {
		URL.revokeObjectURL(coverImagePreviewUrl.value);
		coverImagePreviewUrl.value = "";
	}
	if (file) {
		coverImagePreviewUrl.value = URL.createObjectURL(file);
	}
};

const removeCoverImage = () => {
	coverImageFile.value = null;
	if (coverImagePreviewUrl.value) {
		URL.revokeObjectURL(coverImagePreviewUrl.value);
		coverImagePreviewUrl.value = "";
	}
	user.value.cover_image_path = null;
	shouldDeleteCover.value = true;
};

const removeProfilePhoto = () => {
	profilePhotoFile.value = null;
	if (profilePhotoPreviewUrl.value) {
		URL.revokeObjectURL(profilePhotoPreviewUrl.value);
		profilePhotoPreviewUrl.value = "";
	}
	user.value.profile_photo_path = null;
	shouldDeleteProfilePhoto.value = true;
};

const saveProfile = async () => {
	if (!currentUser.value) return;
	try {
		saving.value = true;
		uploadProgress.value = 0;
		errorMessage.value = "";
		const updatedUser = await authService.updateProfile(currentUser.value, {
			bio: bioDraft.value,
			profilePhotoFile: profilePhotoFile.value,
			coverImageFile: coverImageFile.value,
			deleteCover: shouldDeleteCover.value,
			deleteProfilePhoto: shouldDeleteProfilePhoto.value,
			onProgress: (progress) => {
				uploadProgress.value = progress;
			},
		});
		user.value = updatedUser;
		profilePhotoFile.value = null;
		coverImageFile.value = null;
		shouldDeleteCover.value = false;
		shouldDeleteProfilePhoto.value = false;

		if (profilePhotoPreviewUrl.value) {
			URL.revokeObjectURL(profilePhotoPreviewUrl.value);
			profilePhotoPreviewUrl.value = "";
		}
		if (coverImagePreviewUrl.value) {
			URL.revokeObjectURL(coverImagePreviewUrl.value);
			coverImagePreviewUrl.value = "";
		}
		isEditing.value = false;
	} catch (error) {
		errorMessage.value = error.message || "Impossible de mettre a jour le profil";
	} finally {
		saving.value = false;
	}
};

const toggleEdit = () => {
	const nextState = !isEditing.value;
	isEditing.value = nextState;
	if (!nextState) {
		bioDraft.value = user.value?.bio || "";
		profilePhotoFile.value = null;
		coverImageFile.value = null;
		shouldDeleteCover.value = false;
		shouldDeleteProfilePhoto.value = false;
		errorMessage.value = "";
		if (profilePhotoPreviewUrl.value) {
			URL.revokeObjectURL(profilePhotoPreviewUrl.value);
			profilePhotoPreviewUrl.value = "";
		}
		if (coverImagePreviewUrl.value) {
			URL.revokeObjectURL(coverImagePreviewUrl.value);
			coverImagePreviewUrl.value = "";
		}
		authService.getProfile().then((data) => (user.value = data));
	}
};

const triggerProfileInput = () => {
	if (isEditing.value && !saving.value) {
		profileInputRef.value?.click();
	}
};

const triggerCoverInput = () => {
	if (isEditing.value && !saving.value) {
		coverInputRef.value?.click();
	}
};
</script>

<template>
	<div class="flex justify-between mx-4 lg:mx-96 bg-gray-100 p-3 md:p-5 rounded shadow-lg mt-4">
		<div class="ml-1 mr-3 flex items-center">
			<router-link to="/" class="text-gray-800 hover:text-gray-600 transition">
				<img
					:src="precedentIcon"
					alt="Retour"
					class="h-8 w-8 md:h-10 md:w-10 hover:scale-110 transition-transform duration-200 shadow-md shadow-gray-400/30 rounded-full"
				/>
			</router-link>
			<h1 class="ml-3 md:ml-4 text-xl md:text-4xl font-bold">Profil</h1>
		</div>

		<div class="flex items-center gap-2 md:gap-4">
			<button
				class="rounded-xl md:rounded-2xl border border-blue-800 bg-blue-800 px-3 py-1 md:px-4 md:py-2 font-semibold text-sm md:text-lg text-white hover:bg-blue-700 disabled:opacity-60 transition-transform duration-200"
				:disabled="saving"
				@click="toggleEdit"
			>
				{{ isEditing ? "Annuler" : "Modifier" }}
			</button>

			<div v-if="isEditing" class="">
				<div v-if="saving" class="space-y-2">
					<div class="h-2 w-full rounded-full bg-gray-200">
						<div
							class="h-2 rounded-full bg-blue-600 transition-all"
							:style="{ width: `${uploadProgress}%` }"
						></div>
					</div>
					<p class="text-sm text-gray-600">{{ uploadProgress }}%</p>
				</div>

				<button
					class="rounded-xl md:rounded-2xl border border-blue-800 bg-blue-800 px-3 py-1 md:px-4 md:py-2 font-semibold text-sm md:text-lg text-white hover:bg-blue-700 disabled:opacity-60 transition-transform duration-200"
					:disabled="saving"
					@click="saveProfile"
				>
					{{ saving ? "En cours..." : "Valider" }}
				</button>
			</div>
		</div>
	</div>

	<div v-if="loading" class="text-center py-10">
		<p class="text-lg text-gray-600">Chargement...</p>
	</div>

	<div v-else-if="user" class="mt-4 md:mt-5 p-4 md:p-6 bg-gray-100 rounded-xl shadow-xl mx-4 lg:mx-96 relative">
		
        <div class="relative mb-4 h-32 md:h-96 w-full overflow-hidden rounded-lg">
			<button
				type="button"
				class="h-full w-full overflow-hidden rounded-lg group"
				:class="isEditing && !saving ? 'cursor-pointer' : 'cursor-default'"
				:disabled="!isEditing || saving"
				@click="triggerCoverInput"
			>
				<img
					class="h-full w-full object-fill transition duration-300"
					:src="
						coverImagePreviewUrl
							? coverImagePreviewUrl
							: user.cover_image_path
								? `${apiUrl}/storage/${user.cover_image_path}`
								: defaultCover
					"
					alt=""
				/>
				<div
					v-if="isEditing && !saving"
					class="absolute inset-0 flex items-center justify-center bg-black/40 text-white opacity-0 transition duration-300 group-hover:opacity-100"
				>
					<span class="rounded-full bg-black/60 px-2 py-1 md:px-3 md:py-1 text-xs md:text-sm"> Changer la couverture </span>
				</div>
			</button>

			<button
				v-if="isEditing && !saving && (coverImagePreviewUrl || user.cover_image_path)"
				type="button"
				@click.stop="removeCoverImage"
				class="absolute top-2 right-2 md:top-4 md:right-4 z-20 flex h-8 w-8 md:h-10 md:w-10 items-center justify-center rounded-full bg-red-600 text-white shadow-lg hover:bg-red-700 transition-colors"
			>
				<span class="text-xl md:text-2xl font-bold">&times;</span>
			</button>
		</div>

		<input ref="coverInputRef" type="file" accept="image/*" class="hidden" @change="handleCoverImageChange" />

		<div class="absolute top-20 md:top-72 left-4 md:left-10 z-10">
			<button
				type="button"
				class="group relative block rounded-full"
				:class="isEditing && !saving ? 'cursor-pointer' : 'cursor-default'"
				:disabled="!isEditing || saving"
				@click="triggerProfileInput"
			>
				<div
					class="w-24 h-24 md:w-44 md:h-44 bg-amber-100 flex items-center rounded-full justify-center text-black font-bold overflow-hidden border-4 border-white shadow-sm"
				>
					<img
						v-if="profilePhotoPreviewUrl || user?.profile_photo_path"
						:src="profilePhotoPreviewUrl || `${apiUrl}/storage/${user.profile_photo_path}`"
						class="w-full h-full object-cover"
					/>
					<span v-else class="text-3xl md:text-6xl font-bold text-blue-600 uppercase">
						{{ user?.first_name?.charAt(0) }}.{{ user?.last_name?.charAt(0) }}
					</span>
				</div>

				<div
					v-if="isEditing && !saving"
					class="absolute inset-0 flex items-center justify-center rounded-full bg-black/40 text-white opacity-0 transition duration-300 group-hover:opacity-100"
				>
					<span class="text-xs font-medium">Changer</span>
				</div>
			</button>

			<button
				v-if="isEditing && !saving && (profilePhotoPreviewUrl || user.profile_photo_path)"
				type="button"
				@click.stop="removeProfilePhoto"
				class="absolute top-0 right-0 z-20 flex h-6 w-6 md:h-8 md:w-8 items-center justify-center rounded-full bg-red-600 text-white shadow-lg hover:bg-red-700 transition-colors"
			>
				<span class="text-lg md:text-xl font-bold">&times;</span>
			</button>
		</div>

		<input ref="profileInputRef" type="file" accept="image/*" class="hidden" @change="handleProfilePhotoChange" />

		<h1 class="text-2xl md:text-4xl font-bold mb-2 md:mb-4 pt-12 md:pt-16 text-center md:text-left">
			<span class="text-lg md:text-2xl text-gray-400 pr-1">@</span>{{ user.username }}
		</h1>

		<h2 class="text-lg md:text-2xl font-medium mb-4 text-center md:text-left">{{ user.first_name }} {{ user.last_name }}</h2>

		<div class="flex flex-wrap items-center justify-between gap-4">
			<p class="text-gray-700 text-base md:text-xl border-t border-b border-gray-300 p-3 md:p-5 rounded-lg flex-1">
				<span v-if="!isEditing">
					{{ user.bio || "Aucune biographie pour le moment." }}
				</span>
				<textarea
					v-else
					v-model="bioDraft"
					rows="3"
					class="w-full rounded-lg border border-gray-300 bg-white p-2 md:p-3 text-base md:text-lg resize-none"
					placeholder="Parlez un peu de vous..."
					:disabled="saving"
				></textarea>
			</p>
		</div>

		<p v-if="errorMessage" class="text-sm text-red-600 mt-2 text-center md:text-left">
			{{ errorMessage }}
		</p>
	</div>

	<div v-if="currentUser" class="mt-4 p-4 md:p-6 bg-gray-100 rounded-xl shadow-xl mx-4 lg:mx-96 relative mb-6">
		<PostByUser :userId="currentUser" />
	</div>
</template>
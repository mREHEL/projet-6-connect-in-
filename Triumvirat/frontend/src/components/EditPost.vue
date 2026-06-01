<script setup>
import { computed, ref, watch } from "vue";
import { postService } from "../services/postService.js";
import { Link, ThumbsUp } from "lucide-vue-next";

const props = defineProps({
	post: { type: Object, required: true },
	currentUser: { type: Object, default: null },
});

const isOnline = (lastSeen) => {
	if (!lastSeen) return false;
	const lastSeenDate = new Date(lastSeen);
	const diffInMinutes = (Date.now() - lastSeenDate.getTime()) / 1000 / 60;
	return diffInMinutes < 5;
};

const statusClass = (lastSeen) => {
	return isOnline(lastSeen)
		? "bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)]"
		: "bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.3)]";
};

const statusLabel = (lastSeen) => {
	if (!lastSeen) return "Hors ligne";
	if (isOnline(lastSeen)) return "En ligne";
	const diffInMinutes = Math.round((Date.now() - new Date(lastSeen).getTime()) / 1000 / 60);
	if (diffInMinutes < 60) return `Vu il y a ${diffInMinutes} min`;
	return "Hors ligne";
};

// --- RESTES DES COMPUTED & REFS ---
const emit = defineEmits(["update-post", "post-deleted"]);

const isOwner = computed(() => {
	const cu = props.currentUser?.id;
	const ownerId = props.post?.user?.id ?? props.post?.user_id;
	if (cu == null || ownerId == null) return false;
	return String(cu) === String(ownerId);
});

const isEditing = ref(false);
const editedContent = ref("");
const isDeleting = ref(false);
const localLikes = ref([]);
const isLiking = ref(false);

watch(
	() => props.post?.likes,
	(newLikes) => {
		localLikes.value = newLikes || [];
		// Initialiser is_liked si pas déjà défini
		if (props.post && props.post.is_liked === undefined && props.currentUser) {
			props.post.is_liked = localLikes.value.some((l) => l && l.user_id === props.currentUser.id);
		}
	},
	{ immediate: true, deep: true },
);

// CORRECTION ICI : On utilise props.post.is_liked si disponible
const isLiked = computed(() => {
	if (!props.currentUser) return false;
	// Priorité à is_liked si défini
	if (props.post.is_liked !== undefined) return props.post.is_liked;
	// Sinon on calcule depuis localLikes
	return localLikes.value.some((l) => l && l.user_id === props.currentUser.id);
});

async function toggleLike() {
	if (!props.currentUser) {
		alert("Vous devez être connecté pour liker.");
		return;
	}
	if (isLiking.value) return;
	isLiking.value = true;

	try {
		const data = await postService.toggleLike(props.post.id);

		// On synchronise is_liked et likes_count avec le retour du serveur
		props.post.is_liked = data.is_liked;

		// Si le backend renvoie likes_count, on l'utilise
		if (data.likes_count !== undefined) {
			props.post.likes_count = data.likes_count;
		} else {
			// Sinon on incrémente/décrémente manuellement en local
			data.is_liked ? props.post.likes_count++ : props.post.likes_count--;
		}
	} catch (error) {
		console.error("Erreur toggle like:", error);
	} finally {
		isLiking.value = false;
	}
}

function confirmDelete() {
	if (!isOwner.value) return;
	if (confirm("Êtes-vous sûr de vouloir supprimer ce post ?")) {
		isDeleting.value = true;
		emit("post-deleted", props.post.id);
	}
}

watch(
	() => props.post?.content,
	(newVal) => {
		editedContent.value = newVal ?? "";
	},
	{ immediate: true },
);

function startEdit() {
	editedContent.value = props.post?.content ?? "";
	isEditing.value = true;
}

function cancelEdit() {
	editedContent.value = props.post?.content ?? "";
	isEditing.value = false;
}

function saveEdit() {
	const value = editedContent.value.trim();
	if (!value) return;
	emit("update-post", { id: props.post.id, content: value });
	isEditing.value = false;
}

function formatUrl(path) {
	if (!path) return "";
	return `http://localhost:8000/storage/${path}`;
}
</script>

<template>
	<div
		class="bg-gray-100 mx-2 lg:mx-72 px-3 md:px-5 py-3 md:py-4 rounded-xl shadow-xl border mb-4 hover:shadow-md transition"
	>
		<div class="flex items-center justify-between gap-3 mb-4">
			<div class="flex items-center gap-3">
				<div class="flex items-center gap-3">
					<div class="relative w-10 h-10 md:w-14 md:h-14 flex-shrink-0">
						<RouterLink :to="'/users/' + post.user?.id">
							<div
								class="w-full h-full bg-amber-100 flex items-center rounded-full justify-center overflow-hidden border-2 border-white shadow-sm"
							>
								<img
									v-if="post.user?.profile_photo_path"
									:src="formatUrl(post.user.profile_photo_path)"
									class="w-full h-full object-cover"
								/>
								<span v-else class="text-sm md:text-xl font-bold text-blue-600 uppercase">
									{{ post.user?.first_name?.charAt(0) }}.{{ post.user?.last_name?.charAt(0) }}
								</span>
							</div>
						</RouterLink>

						<span
							class="absolute bottom-0 right-0 w-3 h-3 md:w-4 md:h-4 border-2 border-white rounded-full transition-colors duration-500"
							:class="statusClass(post.user?.last_seen_at)"
							:title="statusLabel(post.user?.last_seen_at)"
						></span>
					</div>
				</div>

				<div>
					<RouterLink :to="'/users/' + post.user?.id">
						<p class="font-extrabold text-gray-800 text-base md:text-xl hover:underline w-auto">
							{{ post.user?.username || "Anonyme" }}
						</p>
					</RouterLink>
					<p class="text-xs text-gray-500 border-b border-gray-300">
						Il y a {{ post.formatted_date || "date inconnue" }}
					</p>
				</div>
			</div>

			<button
				v-if="isOwner && !isEditing"
				@click="startEdit"
				class="flex items-center group space-x-2 text-gray-500 hover:text-blue-400 transition"
				title="Modifier"
			>
				<div class="p-1 md:p-2 group-hover:bg-blue-900 rounded-full transition-colors">
					<svg
						xmlns="http://www.w3.org/2000/svg"
						class="w-4 h-4 md:w-5 md:h-5"
						viewBox="0 0 24 24"
						fill="none"
						stroke="currentColor"
						stroke-width="2"
						stroke-linecap="round"
						stroke-linejoin="round"
					>
						<path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
					</svg>
				</div>
			</button>
		</div>

		<div class="text-black leading-relaxed mb-4 mx-2 md:mx-16">
			<div
				v-if="!isEditing"
				class="bg-gray-50 rounded-lg p-2 md:p-3 text-sm md:text-base border-amber-300 border break-words"
			>
				{{ post.content }}
			</div>

			<div v-else class="space-y-2">
				<textarea
					v-model="editedContent"
					rows="4"
					class="w-full rounded-lg bg-gray-100 border border-gray-700 text-gray-900 p-2 md:p-3 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
				></textarea>

				<div class="flex flex-col sm:flex-row items-center justify-between mt-3 gap-2">
					<div class="flex gap-2 w-full sm:w-auto justify-center">
						<button
							@click="saveEdit"
							class="px-3 py-1.5 md:px-4 md:py-1.5 text-xs md:text-sm rounded-lg shadow bg-blue-600 text-white hover:bg-blue-700 transition font-medium flex-1 sm:flex-none"
						>
							Sauvegarder
						</button>
						<button
							@click="cancelEdit"
							class="px-3 py-1.5 md:px-4 md:py-1.5 text-xs md:text-sm rounded-lg shadow border border-gray-600 text-gray-900 hover:bg-gray-800 transition flex-1 sm:flex-none"
						>
							Annuler
						</button>
					</div>

					<button
						@click="confirmDelete"
						:disabled="isDeleting"
						class="px-3 py-1.5 md:px-4 md:py-1.5 text-xs md:text-sm rounded-lg shadow border border-red-500/50 text-red-500 hover:bg-red-600 hover:text-white transition disabled:opacity-50 font-medium flex items-center justify-center gap-2 w-full sm:w-auto"
					>
						<svg
							v-if="!isDeleting"
							xmlns="http://www.w3.org/2000/svg"
							class="w-3.5 h-3.5 md:w-4 md:h-4"
							viewBox="0 0 24 24"
							fill="none"
							stroke="currentColor"
							stroke-width="2"
							stroke-linecap="round"
							stroke-linejoin="round"
						>
							<path d="M3 6h18"></path>
							<path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
							<path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
						</svg>
						{{ isDeleting ? "Suppression..." : "Supprimer" }}
					</button>
				</div>
			</div>

			<div v-if="post.media && post.media.length > 0" class="rounded-xl overflow-hidden mt-4">
				<img
					:src="`http://localhost:8000/storage/${post.media[0].url}`"
					class="w-full h-auto object-cover max-h-64 md:max-h-96"
				/>
			</div>
		</div>

		<div class="flex items-center gap-4 mx-2 md:mx-16 border-t border-gray-200 pt-2">
			<RouterLink
				:to="'/posts/' + post.id"
				class="flex items-center group space-x-2 text-gray-500 hover:text-blue-700 transition hover:bg-blue-500/10 rounded-full px-2 py-2"
			>
				<div>
					<svg
						xmlns="http://www.w3.org/2000/svg"
						class="h-4 w-4 md:h-5 md:w-5 transition-all duration-300"
						fill="none"
						viewBox="0 0 24 24"
						stroke="currentColor"
					>
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2"
							d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
						/>
					</svg>
				</div>
				<span class="text-xs md:text-sm font-medium">{{ post.comments_count }}</span>
			</RouterLink>

			<button
				@click="toggleLike"
				:disabled="isLiking"
				class="flex items-center group space-x-2 transition disabled:opacity-50 hover:bg-blue-500/10 rounded-full px-2 py-2"
				:class="isLiked ? 'text-blue-500' : 'text-gray-500 hover:text-blue-700'"
			>
				<div>
					<ThumbsUp
						class="w-4 h-4 md:w-5 md:h-5 transition-all duration-300"
						:class="isLiked ? 'fill-blue-500 text-blue-100' : 'fill-none text-slate-400'"
					/>
				</div>
				<span class="text-xs md:text-sm font-medium">{{ post.likes_count }}</span>
			</button>
		</div>
	</div>
</template>

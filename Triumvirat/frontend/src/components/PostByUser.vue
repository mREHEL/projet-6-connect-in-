<template>
	<div class="max-w-3xl mx-auto p-5">
		<div v-if="isLoading" class="text-center py-15 px-5 text-lg">
			<p>Chargement...</p>
		</div>

		<div v-else-if="error" class="text-center py-15 px-5 text-lg text-red-600">
			<p>{{ error }}</p>
		</div>

		<div v-else>
			<div>
				<h2 class="text-2xl font-bold mb-5 text-gray-800">
					Publications
					<span class="text-gray-500 font-normal">({{ posts.length }})</span>
				</h2>

				<div
					v-if="posts.length === 0"
					class="text-center py-15 px-5 text-gray-500 text-base bg-gray-50 rounded-xl"
				>
					<p>Aucune publication pour le moment</p>
				</div>

				<div v-else class="flex flex-col gap-5">
					<article
						v-for="post in posts"
						:key="post.id"
						class="bg-gray-100 mx-2 lg:mx-20 px-3 md:px-5 py-3 md:py-4 rounded-xl shadow-xl border mb-4 hover:shadow-md transition"
					>
						<div class="flex items-center justify-between gap-3 mb-4">
							<div class="flex items-center gap-3">
								<div class="flex items-center gap-3">
									<div class="relative w-10 h-10 md:w-14 md:h-14 flex-shrink-0">
										<div
											class="w-full h-full bg-amber-100 flex items-center rounded-full justify-center overflow-hidden border-2 border-white shadow-sm"
										>
											<img
												v-if="user?.profile_photo_path"
												:src="formatUrl(user.profile_photo_path)"
												class="w-full h-full object-cover"
											/>
											<span v-else class="text-sm md:text-xl font-bold text-blue-600 uppercase">
												{{ user?.first_name?.charAt(0) }}.{{ user?.last_name?.charAt(0) }}
											</span>
										</div>
									</div>
								</div>

								<div>
									<p class="font-extrabold text-gray-800 text-base md:text-xl">{{ user.username }}</p>
									<p class="text-xs text-gray-500 border-b border-gray-300">
										Il y a {{ formatDate(post.created_at) }}
									</p>
								</div>
							</div>
						</div>

						<div class="text-black leading-relaxed mb-4 mx-2 md:mx-16">
							<div
								class="bg-gray-50 rounded-lg p-2 md:p-3 text-sm md:text-base border-amber-300 border break-words"
							>
								{{ post.content }}
							</div>

							<div v-if="post.media && post.media.length > 0" class="rounded-xl overflow-hidden mt-4">
								<img
									v-for="media in post.media"
									:key="media.id"
									:src="`${apiUrl}/storage/${media.url}`"
									:alt="'Image du post ' + post.id"
									class="w-full h-auto object-cover max-h-64 md:max-h-96 block"
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
								@click="toggleLike(post)"
								:disabled="isLiking[post.id]"
								class="flex items-center group space-x-2 transition disabled:opacity-50 hover:bg-blue-500/10 rounded-full px-2 py-2"
								:class="isLiked(post) ? 'text-blue-500' : 'text-gray-500 hover:text-blue-700'"
							>
								<div>
									<ThumbsUp
										class="w-4 h-4 md:w-5 md:h-5 transition-all duration-300"
										:class="
											isLiked(post) ? 'fill-blue-500 text-blue-100' : 'fill-none text-slate-400'
										"
									/>
								</div>
								<span class="text-xs md:text-sm font-medium">{{ likeCount(post) }}</span>
							</button>
						</div>
					</article>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { postService } from "../services/postService.js";
import { userService } from "../services/userService.js";
import { authState } from "../utils/authEvents.js";
import { formatDate } from "../utils/dateFormatter.js";
import { ThumbsUp } from "lucide-vue-next";

const props = defineProps({
	userId: {
		type: Number,
		required: true,
	},
});

const user = ref(null);
const posts = ref([]);
const isLoading = ref(true);
const error = ref(null);
const isLiking = ref({});

const currentUser = computed(() => authState.value.user);

const apiUrl = "http://localhost:8000";

const fetchUser = async () => {
	try {
		user.value = await userService.getById(props.userId);
	} catch (err) {
		console.error("Erreur lors de la récupération de l'utilisateur:", err);
		error.value = err.message;
	}
};

const fetchUserPosts = async () => {
	try {
		posts.value = await userService.getPostsByUser(props.userId);
	} catch (err) {
		console.error("Erreur lors de la récupération des posts:", err);
		error.value = err.message;
	}
};

function formatUrl(path) {
	if (!path) return "";
	return `${apiUrl}/storage/${path}`;
}

function ensureLikes(post) {
	if (!Array.isArray(post.likes)) post.likes = [];
	return post.likes;
}

function isLiked(post) {
	if (!currentUser.value) return false;
	// Priorité à is_liked si défini
	if (post.is_liked !== undefined) return post.is_liked;
	// Sinon on calcule depuis les likes
	return ensureLikes(post).some((l) => l && l.user_id === currentUser.value.id);
}

function likeCount(post) {
	if (post.likes_count !== undefined) return post.likes_count;
	const likes = ensureLikes(post);
	return likes.length > 0 ? likes.length : 0;
}

function commentCount(post) {
	return post.comments?.length ?? 0;
}

async function toggleLike(post) {
	if (!currentUser.value) {
		alert("Vous devez être connecté pour liker.");
		return;
	}
	if (isLiking.value[post.id]) return;
	isLiking.value[post.id] = true;

	try {
		const data = await postService.toggleLike(post.id);

		// On synchronise is_liked et likes_count avec le retour du serveur
		post.is_liked = data.is_liked;

		// Si le backend renvoie likes_count, on l'utilise
		if (data.likes_count !== undefined) {
			post.likes_count = data.likes_count;
		} else {
			// Sinon on incrémente/décrémente manuellement en local
			data.is_liked ? post.likes_count++ : post.likes_count--;
		}
	} catch (err) {
		console.error("Erreur toggle like:", err);
	} finally {
		isLiking.value[post.id] = false;
	}
}

const loadData = async () => {
	isLoading.value = true;
	error.value = null;
	await Promise.all([fetchUser(), fetchUserPosts()]);
	isLoading.value = false;
};

watch(
	() => props.userId,
	() => {
		loadData();
	},
	{ immediate: true },
);
</script>

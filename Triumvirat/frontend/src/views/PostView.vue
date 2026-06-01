<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { postService } from "../services/postService";

const route = useRoute();
const router = useRouter();
const post = ref(null);
const newComment = ref("");

// GESTION DE UTILISATEUR CONNECTÉ
// récupère infos utilisateur depuis localStorage pour vérifier s'il est l'auteur d'un commentaire
const currentUser = ref(JSON.parse(localStorage.getItem("user") || "null"));

// ÉTATS ÉDITION DES COMMENTAIRES
const editingCommentId = ref(null);
const editCommentContent = ref("");

const getAvatarUrl = (user) => {
	if (!user)
		return `https://ui-avatars.com/api/?name=Anonyme&background=random&color=fff&rounded=true&size=128&bold=true`;
	if (user.profile_photo_path) {
		if (user.profile_photo_path.startsWith("http")) return user.profile_photo_path;
		return `http://localhost:8000/storage/${user.profile_photo_path}`;
	}
	const name = user.username || "Anonyme";
	return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=random&color=fff&rounded=true&size=128&bold=true`;
};

const formatUrl = (path) => {
	if (!path) return null;
	if (path.startsWith("http")) return path;
	return `http://localhost:8000/storage/${path}`;
};

const fetchPost = async () => {
	try {
		const data = await postService.getById(route.params.id);
		post.value = data;
	} catch (error) {
		console.error("Erreur d'affichage :", error);
	}
};

const submitComment = async () => {
	if (newComment.value.trim() === "") return;

	try {
		await postService.addComment(post.value.id, newComment.value);
		newComment.value = "";
		await fetchPost();
	} catch (error) {
		console.error("Erreur lors de l'ajout du commentaire :", error);
		alert("Erreur lors de l'ajout du commentaire.");
	}
};

// FONCTIONS MODIFICATION & SUPPRESSION

// Démarrer l'édition d'un commentaire
const startEdit = (comment) => {
	editingCommentId.value = comment.id;
	editCommentContent.value = comment.content;
};

// Annuler l'édition
const cancelEdit = () => {
	editingCommentId.value = null;
	editCommentContent.value = "";
};

// Sauvegarder la modification
const saveEdit = async (commentId) => {
	if (editCommentContent.value.trim() === "") return;
	try {
		await postService.updateComment(commentId, editCommentContent.value);
		editingCommentId.value = null;
		editCommentContent.value = "";
		await fetchPost(); // On recharge pour avoir la version à jour
	} catch (error) {
		console.error("Erreur lors de la modification :", error);
		alert("Erreur lors de la modification.");
	}
};

// Supprimer un commentaire
const deleteComment = async (commentId) => {
	if (!confirm("Voulez-vous vraiment supprimer ce commentaire ?")) return;
	try {
		await postService.deleteComment(commentId);
		await fetchPost(); // On recharge la liste
	} catch (error) {
		console.error("Erreur lors de la suppression :", error);
		alert("Erreur lors de la suppression.");
	}
};

onMounted(() => {
	fetchPost();
});
</script>

<template>
	<div class="max-w-2xl mx-auto p-4 sm:p-6 bg-white shadow-lg rounded-xl mt-10">
		<button
			@click="$router.back()"
			class="mb-6 text-blue-500 hover:text-blue-700 transition-colors font-medium flex items-center gap-2"
		>
			<span>←</span>
			<span class="hover:underline">Retour</span>
		</button>

		<div v-if="post">
			<div class="flex gap-4 mb-6 rounded-md p-4 bg-gray-50 shadow-md">
				<div
					class="w-16 h-16 bg-amber-100 flex items-center rounded-full justify-center overflow-hidden border-2 border-white shadow-sm"
				>
					<RouterLink :to="'/users/' + post.user?.id" class="w-full h-full flex items-center justify-center">
						<img
							v-if="post.user?.profile_photo_path"
							:src="formatUrl(post.user.profile_photo_path)"
							class="w-full h-full object-cover"
						/>
						<span v-else class="text-lg md:text-xl font-bold text-blue-600 uppercase">
							{{ post.user?.first_name?.charAt(0) }}.{{ post.user?.last_name?.charAt(0) }}
						</span>
					</RouterLink>
				</div>

				<div class="flex-grow">
					<div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 mb-2">
						<RouterLink :to="'/users/' + post.user?.id">
							<h1 class="text-lg font-bold text-gray-900 hover:underline">
								{{ post.user?.username || "Anonyme" }}
							</h1>
						</RouterLink>
					</div>

					<p class="text-gray-800 text-lg leading-relaxed break-words bg-white shadow-sm p-4 rounded-lg">
						{{ post.content }}
					</p>
				</div>
			</div>

			<div class="border-t border-gray-100 pt-6">
				<h3 class="text-lg font-bold text-gray-800 mb-6">Commentaires ({{ post.comments?.length || 0 }})</h3>

				<div v-if="post.comments && post.comments.length > 0" class="space-y-0 mb-8 flex flex-col gap-1">
					<div
						v-for="comment in post.comments"
						:key="comment.id"
						class="flex gap-3 py-4 border-b last:border-b-0 group rounded-md p-4 bg-gray-50 shadow-md"
					>
						<div
							class="w-14 h-14 flex-shrink-0 bg-amber-100 flex items-center rounded-full justify-center overflow-hidden border-2 border-white shadow-sm"
						>
							<RouterLink
								:to="'/users/' + comment.user?.id"
								class="w-full h-full flex items-center justify-center"
							>
								<img
									v-if="comment.user?.profile_photo_path"
									:src="formatUrl(comment.user.profile_photo_path)"
									class="w-full h-full object-cover"
								/>
								<span v-else class="text-sm md:text-xl font-bold text-blue-600 uppercase">
									{{ comment.user?.first_name?.charAt(0) }}.{{ comment.user?.last_name?.charAt(0) }}
								</span>
							</RouterLink>
						</div>

						<div class="flex-grow">
							<div class="flex items-center justify-between mb-1">
								<RouterLink :to="'/users/' + comment.user?.id">
									<span class="font-bold text-gray-900 text-sm hover:underline">
										{{ comment.user?.username || "Anonyme" }}
									</span>
								</RouterLink>
								<div
									v-if="currentUser && currentUser.id === comment.user_id"
									class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity"
								>
									<button
										@click="startEdit(comment)"
										class="text-xs text-gray-500 hover:text-blue-600 font-medium"
									>
										Modifier
									</button>
									<button
										@click="deleteComment(comment.id)"
										class="text-xs text-gray-500 hover:text-red-600 font-medium"
									>
										Supprimer
									</button>
								</div>
							</div>

							<p
								v-if="editingCommentId !== comment.id"
								class="text-gray-800 break-all overflow-wrap-anywhere whitespace-pre-wrap shadow-sm p-3 rounded-lg bg-white"
							>
								{{ comment.content }}
							</p>

							<div v-else class="mt-2 flex flex-col gap-2">
								<textarea
									v-model="editCommentContent"
									class="w-full p-2 bg-gray-100 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all resize-none text-gray-800 text-sm"
									rows="2"
								></textarea>
								<div class="flex justify-end gap-2">
									<button
										@click="cancelEdit"
										class="text-xs text-gray-500 hover:text-gray-800 font-semibold px-2 py-1"
									>
										Annuler
									</button>
									<button
										@click="saveEdit(comment.id)"
										:disabled="!editCommentContent.trim()"
										class="text-xs bg-blue-500 text-white hover:bg-blue-600 font-bold px-3 py-1.5 rounded-full disabled:opacity-50 transition-colors"
									>
										Enregistrer
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div v-else class="text-gray-500 text-center py-6 mb-6 bg-gray-50 rounded-lg">
					Soyez le premier à commenter cette publication !
				</div>

				<div class="flex gap-3 mt-4">
					<div class="flex-grow flex flex-col gap-2">
						<textarea
							v-model="newComment"
							class="w-full p-3 bg-gray-50 shadow-md border border-transparent rounded-xl focus:bg-white focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all resize-none text-gray-800"
							rows="2"
							placeholder="Postez votre réponse..."
						></textarea>
						<button
							@click="submitComment"
							:disabled="!newComment.trim()"
							class="self-end bg-blue-500 text-white px-5 py-2 rounded-full font-bold text-sm hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
						>
							Répondre
						</button>
					</div>
				</div>
			</div>
		</div>

		<div v-else class="animate-pulse flex space-x-4">
			<div class="rounded-full bg-gray-200 h-12 w-12"></div>
			<div class="flex-1 space-y-4 py-1">
				<div class="h-4 bg-gray-200 rounded w-1/4"></div>
				<div class="space-y-2">
					<div class="h-4 bg-gray-200 rounded"></div>
					<div class="h-4 bg-gray-200 rounded w-5/6"></div>
				</div>
			</div>
		</div>
	</div>
</template>

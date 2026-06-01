<script setup>
import { authState } from "../utils/authEvents.js";
import PostList from "../components/PostList.vue";
import InputPost from "../components/InputPost.vue";
import { postService } from "../services/postService.js";
import { onMounted, ref, computed, watch } from "vue";

const posts = ref([]);
const showSuccessMessage = ref(true);
const count = ref(0);

const currentUser = computed(() => authState.value.user);

const emit = defineEmits(["update-post"]);

onMounted(() => {
	if (count.value === 0) {
		setTimeout(() => {
			showSuccessMessage.value = false;
		}, 2000);
	} else {
		showSuccessMessage.value = false;
	}
});

const handleUpdatePost = async ({ id, content }) => {
	try {
		const updatedPost = await postService.update(id, content);
		const i = posts.value.findIndex((p) => p.id === id);
		if (i !== -1) {
			posts.value[i] = { ...posts.value[i], ...updatedPost };
			if (!updatedPost?.content) posts.value[i].content = content;
		}
	} catch (error) {
		console.error(error);
		alert("Erreur lors de la mise à jour");
	}
};

const fetchPosts = async () => {
	posts.value = await postService.getAll();
};

const deletePost = async (postId) => {
	try {
		await postService.delete(postId);
		posts.value = posts.value.filter((post) => post.id !== postId);
	} catch (error) {
		console.error("Erreur lors de la suppression du post", error);
		alert("Impossible de supprimer le post");
	}
};

watch(
	() => authState.value?.isLoggedIn,
	(loggedIn) => {
		if (loggedIn) fetchPosts();
		else posts.value = [];
	},
	{ immediate: true },
);
</script>

<template>
	<div>
		<div
			v-if="!authState.isLoggedIn"
			class="min-h-screen flex flex-col items-center justify-center text-white px-4"
		>
			<h1 class="text-3xl md:text-5xl font-extrabold mb-4 md:mb-6 text-center">
				{{ "Bienvenue sur Connect In" }}
			</h1>
			<p class="text-base md:text-xl mb-8 md:mb-10 text-center max-w-lg px-4">
				{{ "Le réseau social interne pour partager vos idées..." }}
			</p>
			<div class="space-x-4 flex justify-center">
				<router-link
					to="/connexion"
					class="border-2 bg-white text-blue-600 border-white px-6 md:px-8 py-2 md:py-3 rounded-full font-bold hover:bg-transparent hover:text-white transition"
				>
					{{ "Se connecter" }}
				</router-link>
				<router-link
					to="/inscription"
					class="border-2 border-white px-6 md:px-8 py-2 md:py-3 rounded-full font-bold hover:bg-white hover:text-blue-600 transition"
				>
					{{ "Créer un compte" }}
				</router-link>
			</div>
		</div>

		<div v-else class="container mx-auto p-2 md:p-6">
			<h2 class="ml-2 lg:ml-72 text-2xl md:text-4xl font-bold mb-4 md:mb-6 text-white text-center md:text-left [text-shadow:1px_1px_2px_rgba(0,0,0,0.3)]">
				{{ "Votre Fil d'actualité" }}
			</h2>
			<p v-if="showSuccessMessage" class="text-green-600 font-semibold ml-2 lg:ml-72 text-center md:text-left">
				{{ "Vous êtes connecté !" }}
			</p>
			<InputPost @post-created="fetchPosts" />
			<PostList
				:posts="posts"
				:currentUser="currentUser"
				@update-post="handleUpdatePost"
				@post-deleted="deletePost"
			/>
		</div>
	</div>
</template>
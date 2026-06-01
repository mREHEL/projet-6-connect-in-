<script setup>
import { ref } from "vue"; 
import { postService } from "../services/postService.js";
import { emitter } from '../utils/emitter'; 
import router from "../router/index.js";

const imageFile = ref(null); 
const imagePreview = ref(null); 

const handleImageUpload = (event) => {
	const file = event.target.files[0];
	if (file) {
		imageFile.value = file;
		imagePreview.value = URL.createObjectURL(file); 
	}
};

const triggerNotify = (message, isError = false) => {
    emitter.emit('notify', { 
        message: message, 
        type: isError ? 'error' : 'success' 
    });
};

const removeImage = () => {
	imageFile.value = null;
	imagePreview.value = null;
};

const postContent = ref("");
const emit = defineEmits(["post-created"]); 

const submitPost = async () => {
	if (postContent.value.trim() === "") {
		triggerNotify("Le texte ne peut pas être vide", true);
		return;
	}
	const token = localStorage.getItem("token");
	if (!token) {
		triggerNotify("Vous devez être connecté pour publier un post", true);
		router.push('/connexion');
		return;
	}
	try {
		const postData = {
			content: postContent.value,
			image: imageFile.value, 
		};
		await postService.create(postData);
		postContent.value = "";
		removeImage();
		triggerNotify("Post publié avec succès !");
		emit("post-created");
	} catch (error) {
		triggerNotify(`Erreur : ${error.message}`, true);
	}
};
</script>

<template>
	<div class="p-3 md:p-4 bg-gray-100 shadow rounded-xl border border-amber-200 mb-6 md:mb-10 mx-2 lg:mx-72">
		<h3 class="font-bold text-blue-600 text-lg md:text-2xl">Créer un post</h3>
		<textarea
			v-model="postContent"
			class="w-full mt-2 p-2 bg-gray-50 border border-amber-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-base md:text-lg"
			rows="3"
			placeholder="Quoi de neuf docteur ? 🥕"
			style="resize: none"
		></textarea>
		<div class="flex justify-between items-center pt-3">
			<label class="cursor-pointer text-blue-500 hover:bg-blue-800 hover:text-white p-2 rounded-full transition">
				<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
				</svg>
				<input type="file" class="hidden" accept="image/*" @change="handleImageUpload" />
			</label>
			<button
				@click="submitPost"
				class="rounded-xl bg-blue-800 px-4 py-2 font-semibold text-sm md:text-lg text-white hover:bg-blue-700 disabled:opacity-60 hover:scale-105 transition-transform duration-200"
			>
				{{ "Publier" }}
			</button>
		</div>
		<div v-if="imagePreview" class="relative mt-2">
			<img :src="imagePreview" class="rounded-lg max-h-64 w-full object-cover" />
			<button @click="removeImage" class="absolute top-2 right-2 bg-black/50 text-white rounded-full p-1">
				✕
			</button>
		</div>
	</div>
</template>
<script setup>
import { reactive, ref } from "vue";
import { authService } from "../services/authService";
import { useRouter, useRoute } from "vue-router";

const router = useRouter();
const route = useRoute(); // Pour récupérer le message "redirected"
const errorMessage = ref("");

// On cration d'un objet
const form = reactive({
	email: "",
	password: "",
});

const isLoading = ref(false); // état de chargement

const handleLogin = async () => {
	errorMessage.value = "";
	isLoading.value = true; // chargement

	try {
		await authService.login(form);
		router.push("/");
	} catch (error) {
		if (error.status === 429) {
			errorMessage.value = "Vous avez fait trop de tentatives. Attendez un peu.";
		} else {
			errorMessage.value = error.message || "Identifiants incorrects";
		}
		form.password = "";
	} finally {
		isLoading.value = false; // On désactive le chargement
	}
};
</script>

<template>
	<div class="max-w-md mx-4 md:mx-auto mt-10">
		<div
			v-if="route.query.redirected"
			class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-3 rounded mb-4 text-sm shadow-sm"
		>
			{{ "Veuillez vous connecter pour accéder à cette page." }}
		</div>

		<div class="p-6 bg-white rounded shadow mt-10 md:mt-52">
			<h1 class="text-2xl font-bold mb-4">{{ "Connexion" }}</h1>

			<form class="space-y-4" @submit.prevent="handleLogin">
				<div>
					<label class="block text-sm font-medium text-gray-700">
						{{ "Email" }}
					</label>
					<input
						v-model="form.email"
						type="email"
						class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500"
						placeholder="Entrez votre email"
						required
					/>
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700">
						{{ "Mot de passe" }}
					</label>
					<input
						v-model="form.password"
						type="password"
						class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500"
						placeholder="Entrez votre mot de passe"
						required
					/>
				</div>

				<div v-if="errorMessage" class="text-red-500 text-sm font-medium">
					{{ errorMessage }}
				</div>

				<button
					:disabled="isLoading"
					type="submit"
					class="w-full py-2 px-4 bg-blue-600 text-white rounded font-semibold hover:bg-blue-700 transition-colors shadow-sm"
				>
					{{ isLoading ? "Connexion..." : "Se connecter" }}
				</button>
			</form>

			<p class="mt-4 text-center text-sm text-gray-600">
				{{ "Pas encore de compte ?" }}
				<router-link to="/inscription" class="text-blue-600 hover:underline">
					{{ "Inscrivez-vous" }}
				</router-link>
			</p>
		</div>
	</div>
</template>
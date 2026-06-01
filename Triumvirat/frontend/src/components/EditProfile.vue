<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import { authState, updateAuthState } from "../utils/authEvents.js";
import { authService } from "../services/authService.js";

const router = useRouter();
const currentUser = computed(() => authState.value?.user);
const loading = ref(false);
const errors = ref({});

const form = ref({
	username: "",
	email: "",
	current_password: "",
	password: "",
	password_confirmation: "",
});

onMounted(() => {
	if (!currentUser.value) {
		router.push("/connexion");
	} else {
		form.value.username = currentUser.value.username || "";
		form.value.email = currentUser.value.email || "";
	}
});

async function handlePasswordChange() {
	loading.value = true;
	errors.value = {};

	if (!currentUser.value) return router.push("/connexion");

	if (!form.value.current_password) {
		errors.value.current_password = ["Le mot de passe actuel est requis"];
		loading.value = false;
		return;
	}

	if (!form.value.email) {
		errors.value.email = ["L'adresse email est requise"];
		loading.value = false;
		return;
	}

	const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
	if (!emailRegex.test(form.value.email)) {
		errors.value.email = ["Le format de l'adresse email est invalide"];
		loading.value = false;
		return;
	}

	const wantsPasswordChange = form.value.password || form.value.password_confirmation;

	if (wantsPasswordChange) {
		if (!form.value.password) {
			errors.value.password = ["Le nouveau mot de passe est requis"];
			loading.value = false;
			return;
		}
		if (form.value.password.length < 8) {
			errors.value.password = ["Le mot de passe doit contenir au moins 8 caractères"];
			loading.value = false;
			return;
		}
		if (form.value.password !== form.value.password_confirmation) {
			errors.value.password_confirmation = ["Les mots de passe ne correspondent pas"];
			loading.value = false;
			return;
		}
	}

	try {
		const payload = {
			username: form.value.username,
			email: form.value.email,
			current_password: form.value.current_password,
		};

		if (wantsPasswordChange) {
			payload.password = form.value.password;
			payload.password_confirmation = form.value.password_confirmation;
		}

		const response = await fetch(`http://localhost:8000/api/users/${currentUser.value.id}`, {
			method: "PUT",
			headers: {
				"Content-Type": "application/json",
				Accept: "application/json",
				Authorization: `Bearer ${localStorage.getItem("token")}`,
			},
			body: JSON.stringify(payload),
		});

		const data = await response.json();

		if (!response.ok) {
			if (response.status === 422) errors.value = data.errors;
			else throw new Error(data.message || "Erreur lors de la mise à jour");
		} else {
			localStorage.setItem("user", JSON.stringify(data.user));
			updateAuthState();
			alert("Profil mis à jour avec succès !");
			router.push("/settings");
		}
	} catch (error) {
		alert(error.message || "Une erreur est survenue.");
	} finally {
		loading.value = false;
	}
}

// Fonction pour gérer les deux suppressions
const handleDeleteAccount = async (isHardDelete) => {
	const message = isHardDelete
		? "ATTENTION : Êtes-vous absolument sûr de vouloir TOUT supprimer ? Cette action effacera votre compte, tous vos posts et vos commentaires de façon IRRÉVERSIBLE."
		: "Êtes-vous sûr de vouloir supprimer votre compte ? Vous serez déconnecté, mais vos publications et commentaires seront conservés et anonymisés.";

	const isConfirmed = window.confirm(message);

	if (!isConfirmed) return;

	try {
		// Appel de la méthode en lui passant true ou false
		const data = await authService.deleteAccount(isHardDelete);

		// Vider le localStorage
		localStorage.removeItem("token");
		localStorage.removeItem("user");

		if (typeof updateAuthState === "function") {
			updateAuthState();
		}

		router.push("/connexion");
		alert(data.message || "Votre compte a bien été supprimé.");
	} catch (error) {
		console.error("Erreur lors de la suppression du compte :", error);
		alert("Une erreur est survenue lors de la suppression du compte.");
	}
};
</script>

<template>
	<div class="container mx-auto p-4 max-w-2xl bg-gray-50 rounded-xl shadow-lg my-10">
		<div class="border-b-2 border-gray-200 mb-5 pb-2">
			<button
				class="text-blue-600 hover:text-blue-800 transition-colors font-medium flex items-center gap-2 text-lg"
				@click="$router.back()"
			>
				<span class="text-lg">←</span>
				<span class="hover:underline">Retour</span>
			</button>
		</div>

		<h1 class="text-2xl font-bold mb-6">Modifier mes informations</h1>

		<div class="bg-white shadow-md rounded-xl p-6 border border-gray-200 mb-8">
			<form @submit.prevent="handlePasswordChange" class="space-y-4">
				<div>
					<label class="block text-sm font-medium text-gray-700">Nom d'utilisateur</label>
					<input
						v-model="form.username"
						type="text"
						:class="{ 'border-red-500': errors.username }"
						class="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-500"
					/>
					<p v-if="errors.username" class="text-red-500 text-xs mt-1">{{ errors.username[0] }}</p>
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700">Adresse email</label>
					<input
						v-model="form.email"
						type="email"
						placeholder="Votre adresse email"
						:class="{ 'border-red-500': errors.email }"
						class="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-500"
						required
					/>
					<p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email[0] }}</p>
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700 mb-2"
						>Mot de passe actuel <span class="text-red-500">*</span></label
					>
					<input
						v-model="form.current_password"
						type="password"
						placeholder="Votre mot de passe actuel"
						:class="{ 'border-red-500': errors.current_password }"
						class="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-500"
						required
					/>
					<p v-if="errors.current_password" class="text-red-500 text-xs mt-1">
						{{ errors.current_password[0] }}
					</p>
					<p v-else class="text-gray-500 text-xs mt-1">Requis pour confirmer toute modification du profil.</p>
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
					<input
						v-model="form.password"
						type="password"
						placeholder="Minimum 8 caractères"
						:class="{ 'border-red-500': errors.password }"
						class="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-500"
					/>
					<p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password[0] }}</p>
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700">Confirmer le nouveau mot de passe</label>
					<input
						v-model="form.password_confirmation"
						type="password"
						placeholder="Retapez le nouveau mot de passe"
						:class="{ 'border-red-500': errors.password_confirmation }"
						class="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-500"
					/>
					<p v-if="errors.password_confirmation" class="text-red-500 text-xs mt-1">
						{{ errors.password_confirmation[0] }}
					</p>
				</div>

				<div class="pt-4">
					<button
						type="submit"
						:disabled="loading"
						class="w-full bg-blue-600 text-white py-2 rounded-lg font-bold hover:bg-blue-700 disabled:opacity-50 transition"
					>
						{{ loading ? "Enregistrement..." : "Enregistrer les modifications" }}
					</button>
				</div>
			</form>
		</div>

		<div class="bg-red-50 border border-red-200 rounded-xl p-6 mt-8">
			<h2 class="text-red-700 font-bold mb-4 text-lg">Zone de danger</h2>

			<div class="space-y-4">
				<div class="p-4 bg-white border border-red-100 rounded-lg shadow-sm">
					<h3 class="font-semibold text-gray-800 mb-1">Désactiver mon compte (Anonymisation)</h3>
					<p class="text-sm text-gray-500 mb-3">
						Votre compte sera supprimé, mais vos publications et commentaires resteront visibles sous le nom
						"utilisateur supprimé".
					</p>
					<button
						@click="handleDeleteAccount(false)"
						type="button"
						class="px-4 py-2 bg-orange-500 text-white text-sm font-medium rounded hover:bg-orange-600 transition"
					>
						Désactiver mon compte
					</button>
				</div>

				<div class="p-4 bg-white border border-red-100 rounded-lg shadow-sm">
					<h3 class="font-semibold text-red-600 mb-1">Suppression définitive</h3>
					<p class="text-sm text-gray-500 mb-3">
						Cette action supprimera définitivement votre compte,
						<strong>ainsi que tous vos posts et commentaires</strong>.
					</p>
					<button
						@click="handleDeleteAccount(true)"
						type="button"
						class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700 transition"
					>
						Tout supprimer définitivement
					</button>
				</div>
			</div>
		</div>
	</div>
</template>

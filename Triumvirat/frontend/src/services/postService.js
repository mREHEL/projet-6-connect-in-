import router from "../router";
import { emitter } from "../utils/emitter.js";
import { updateAuthState } from "../utils/authEvents.js";
import { demoStore, isDemoMode } from "./demoStore.js";

const API_URL = "http://localhost:8000/api";

// Fonction helper pour obtenir les headers avec le token
const getHeaders = (requireAuth = true) => {
	const token = localStorage.getItem("token");
	if (requireAuth && !token) {
		// On déclenche l'événement pour que la NavBar se cache
		updateAuthState();

		emitter.emit("notify", {
			message: "Veuillez vous connecter pour effectuer cette action.",
			type: "error",
		});

		router.push("/connexion");
		throw new Error("No token found");
	}
	const headers = {
		Accept: "application/json",
		"Content-Type": "application/json",
	};

	if (token) {
		headers["Authorization"] = `Bearer ${token}`;
	}

	return headers;
};

export async function handleResponse(response) {
	if (response.status === 401) {
		// Nettoyage radical
		localStorage.removeItem("token");
		localStorage.removeItem("user");
		updateAuthState();

		// Notification visuelle
		emitter.emit("notify", {
			message: "Votre session a expiré. Veuillez vous reconnecter.",
			type: "error",
		});

		//  Redirection intelligente
		if (router.currentRoute.value.path !== "/connexion") {
			router.push({
				path: "/connexion",
				query: { message: "session_expirée" },
			});
		}
		return null;
	}

	if (!response.ok) {
		// Gestion sécurisée des erreurs non-JSON
		const errorData = await response.json().catch(() => ({}));
		throw new Error(errorData.message || `Erreur serveur (${response.status})`);
	}

	return await response.json();
}

export const postService = {
	async getAll() {
		if (isDemoMode) return demoStore.getAllPosts();

		const response = await fetch(`${API_URL}/posts`, {
			method: "GET",
			headers: getHeaders(),
		});
		return handleResponse(response); // Utilisation du helper
	},

	async getById(id) {
		if (isDemoMode) return demoStore.getPostById(id);

		const response = await fetch(`${API_URL}/posts/${id}`, {
			method: "GET",
			headers: getHeaders(),
		});
		return handleResponse(response);
	},

	async create(data) {
		if (isDemoMode) return demoStore.createPost(data);

		const token = localStorage.getItem("token");

		if (!token) {
			emitter.emit("notify", {
				message: "Vous devez être connecté pour publier un post.",
				type: "error",
			});
			router.push("/connexion");
			return null;
		}

		let bodyContent;

		if (data instanceof FormData) {
			bodyContent = data;
		} else {
			bodyContent = new FormData();
			if (data.content) bodyContent.append("content", data.content);
			if (data.image) bodyContent.append("image", data.image);
		}

		const response = await fetch(`${API_URL}/posts`, {
			method: "POST",
			headers: { Accept: "application/json", Authorization: `Bearer ${token}` },
			body: bodyContent,
		});
		return handleResponse(response);
	},

	async delete(id) {
		if (isDemoMode) return demoStore.deletePost(id);

		const response = await fetch(`${API_URL}/posts/${id}`, {
			method: "DELETE",
			headers: getHeaders(),
		});
		return handleResponse(response);
	},

	async toggleLike(postId) {
		if (isDemoMode) return demoStore.toggleLike(postId);

		const response = await fetch(`${API_URL}/posts/${postId}/like`, {
			method: "POST",
			headers: getHeaders(),
		});
		return handleResponse(response);
	},
	async addComment(postId, content) {
		if (isDemoMode) return demoStore.addComment(postId, content);

		const response = await fetch(`${API_URL}/posts/${postId}/comments`, {
			method: "POST",
			headers: getHeaders(),
			body: JSON.stringify({ content: content }),
		});
		return handleResponse(response);
	},
	//
	// FONCTIONS COMMENTAIRES ---
	async updateComment(commentId, content) {
		if (isDemoMode) return demoStore.updateComment(commentId, content);

		const response = await fetch(`${API_URL}/comments/${commentId}`, {
			method: "PUT",
			headers: getHeaders(),
			body: JSON.stringify({ content }),
		});
		return handleResponse(response);
	},

	async deleteComment(commentId) {
		if (isDemoMode) return demoStore.deleteComment(commentId);

		const response = await fetch(`${API_URL}/comments/${commentId}`, {
			method: "DELETE",
			headers: getHeaders(),
		});
		return handleResponse(response);
	},
	async update(id, content) {
		if (isDemoMode) return demoStore.updatePost(id, content);

		const response = await fetch(`${API_URL}/posts/${id}`, {
			method: "PUT",
			headers: getHeaders(),
			body: JSON.stringify({ content }),
		});
		return handleResponse(response);
	},
};

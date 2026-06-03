import { demoStore, isDemoMode } from "./demoStore.js";

const API_URL = "http://localhost:8000/api";

const getHeaders = () => ({
	Authorization: `Bearer ${localStorage.getItem("token")}`,
	Accept: "application/json",
});

const handleResponse = async (response, fallbackMessage) => {
	const data = await response.json().catch(() => null);
	if (!response.ok) throw new Error(data?.message || fallbackMessage);
	return data;
};

export const userService = {
	async getById(userId) {
		if (isDemoMode) return demoStore.getUserById(userId);

		const response = await fetch(`${API_URL}/users/${userId}`, {
			headers: getHeaders(),
		});

		return handleResponse(response, "Utilisateur introuvable.");
	},

	async getPostsByUser(userId) {
		if (isDemoMode) return demoStore.getPostsByUser(userId);

		const response = await fetch(`${API_URL}/users/${userId}/posts`, {
			headers: getHeaders(),
		});

		return handleResponse(response, "Impossible de charger les posts");
	},
};

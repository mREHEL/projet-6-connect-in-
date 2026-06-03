import { updateAuthState } from "../utils/authEvents.js";
import router from '../router';
import { demoStore, isDemoMode } from "./demoStore.js";

const API_URL = "http://localhost:8000/api";

export const authService = {
    startDemoSession() {
        if (!isDemoMode) return null;
        return demoStore.startSession();
    },

    async login(credentials) {
        if (isDemoMode) return demoStore.login(credentials);

        const response = await fetch(`${API_URL}/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(credentials),
        })

        const data = await response.json()
        if (!response.ok) throw new Error(data.message || 'Erreur de connexion')

        if (data.token) {
            localStorage.setItem('token', data.token)
            if (data.user) {
                localStorage.setItem('user', JSON.stringify(data.user))
            }
            updateAuthState()
        }
        return data
    },

    async register(userData) {
        if (isDemoMode) return demoStore.register(userData);

        const response = await fetch(`${API_URL}/register`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify(userData),
        });

        const data = await response.json();

        if (!response.ok) {

            const error = new Error(data.message || "Erreur d'inscription");
            error.status = response.status;
            error.errors = data.errors; // 422
            throw error;
        }

        if (data.token) {
            localStorage.setItem('token', data.token);
            if (data.user) {
                localStorage.setItem('user', JSON.stringify(data.user));
            }
            updateAuthState();
        }
        return data;
    },
    async logout() {
        if (isDemoMode) {
            demoStore.logout();
            router.push('/connexion');
            return;
        }

        const token = localStorage.getItem('token');
        try {
            if (token) {
                await fetch(`${API_URL}/logout`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                });
            }
        } catch (e) {
            console.error("Le serveur n'a pas pu invalider le token, mais on nettoie le navigateur.");
        } finally {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            updateAuthState();
            router.push('/connexion');
        }
    },

    async getProfile() {
        if (isDemoMode) return demoStore.getProfile();

        const token = localStorage.getItem("token");
        if (!token) return null;

        try {
            const response = await fetch(`${API_URL}/user`, {
                method: "GET",
                headers: {
                    "Accept": "application/json",
                    "Authorization": `Bearer ${token}`,
                },
            });

            if (!response.ok) {
                this.logout();
                return null;
            }

            const userData = await response.json();
            localStorage.setItem('user', JSON.stringify(userData));
            updateAuthState();
            return userData;
        } catch (e) {
            console.error("Erreur profil:", e);
            return null;
        }
    },

    async updateProfile(userId, { bio, profilePhotoFile, coverImageFile, deleteCover, deleteProfilePhoto, onProgress }) {
        if (isDemoMode) {
            if (typeof onProgress === "function") onProgress(100);
            return demoStore.updateProfile(userId, { bio, profilePhotoFile, coverImageFile, deleteCover, deleteProfilePhoto });
        }

        const token = localStorage.getItem("token");
        if (!token) throw new Error("Non authentifié");

        const formData = new FormData();
        formData.append("_method", "PUT");

        if (bio !== undefined) formData.append("bio", bio);

        // Gestion Photo de profil
        if (profilePhotoFile) {
            formData.append("profile_photo", profilePhotoFile);
        } else if (deleteProfilePhoto) {
            formData.append("delete_profile_photo", "1");
        }

        // Gestion Bannière
        if (coverImageFile) {
            formData.append("cover_image", coverImageFile);
        } else if (deleteCover) {
            formData.append("delete_cover_image", "1");
        }

        return new Promise((resolve, reject) => {
            const request = new XMLHttpRequest();
            request.open("POST", `${API_URL}/users/${userId}`);
            request.setRequestHeader("Accept", "application/json");
            request.setRequestHeader("Authorization", `Bearer ${token}`);

            if (request.upload && typeof onProgress === "function") {
                request.upload.onprogress = (event) => {
                    if (event.lengthComputable) {
                        const percent = Math.round((event.loaded / event.total) * 100);
                        onProgress(percent);
                    }
                };
            }

            request.onload = () => {
                let responseData = null;
                try {
                    responseData = JSON.parse(request.responseText || "{}");
                } catch (error) {
                    reject(new Error("Réponse invalide du serveur"));
                    return;
                }

                if (request.status < 200 || request.status >= 300) {
                    reject(new Error(responseData.message || "Erreur de mise à jour"));
                    return;
                }

                if (responseData.user) {
                    localStorage.setItem('user', JSON.stringify(responseData.user));
                    updateAuthState();
                }

                resolve(responseData.user);
            };

            request.onerror = () => reject(new Error("Erreur réseau"));
            request.send(formData);
        });
    },

    async updateAccount(userId, payload) {
        if (isDemoMode) return demoStore.updateAccount(userId, payload);

        const response = await fetch(`${API_URL}/users/${userId}`, {
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
            const error = new Error(data.message || "Erreur lors de la mise à jour");
            error.status = response.status;
            error.errors = data.errors;
            throw error;
        }

        if (data.user) {
            localStorage.setItem("user", JSON.stringify(data.user));
            updateAuthState();
        }

        return data;
    },

    // FONCTION DE SUPPRESSION
    async deleteAccount(isHardDelete = false) {
        if (isDemoMode) return demoStore.deleteAccount(isHardDelete);

        const token = localStorage.getItem("token");
        if (!token) throw new Error("Non authentifié");

        const response = await fetch(`${API_URL}/user`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            // On envoie le choix de l'utilisateur (Soft ou Hard delete) au backend
            body: JSON.stringify({ hard_delete: isHardDelete })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Erreur lors de la suppression du compte');
        }

        return data;
    }
};

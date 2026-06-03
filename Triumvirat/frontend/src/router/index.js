import { createRouter, createWebHashHistory, createWebHistory } from "vue-router";
import PostView from "../views/PostView.vue";
import RegisterView from "../views/RegisterView.vue";
import ConnectionView from "../views/ConnectionView.vue";
import LandingView from "../views/LandingView.vue";
import ProfilView from "../views/ProfilView.vue";
import UserProfileView from "../views/UserProfileView.vue";
import { emitter } from "../utils/emitter.js";
import SettingsView from "../views/SettingsView.vue";
import EditProfile from "../components/EditProfile.vue";
import { demoStore, isDemoMode } from "../services/demoStore.js";

const routes = [
	{
		path: "/",
		name: "home",
		component: LandingView,
	},
	{
		path: "/posts/:id",
		name: "post-details",
		component: PostView,
		meta: { requiresAuth: true }, // Bloqué si pas connecté
	},
	{
		path: "/users/:id",
		name: "user-profile",
		component: UserProfileView,
		meta: { requiresAuth: true }, // Bloque si pas connecte
	},
	{
		path: "/posts",
		name: "posts",
		component: LandingView,
		meta: { requiresAuth: true }, // Bloqué si pas connecté
	},
	{
		path: "/profil",
		name: "profil",
		component: ProfilView,
		meta: { requiresAuth: true }, // Bloqué si pas connecté
	},
	// ROUTES PUBLIQUES (Toujours accessibles)
	{
		path: "/inscription",
		name: "register",
		component: RegisterView,
	},
	{
		path: "/connexion",
		name: "connection",
		component: ConnectionView,
	},
	{
		path: "/:pathMatch(.*)*", // Capture toutes les URLs qui n'existent pas
		name: "NotFound",
		component: () => import("../views/NotFoundView.vue"),
	},
	{ path: "/settings", name: "settings", component: SettingsView, meta: { requiresAuth: true } }, // Bloqué si pas connecté
	{ path: '/settings/edit-profile', name: "edit-profile", component: EditProfile, meta: { requiresAuth: true } }, // Bloqué si pas connecté
];

const router = createRouter({
	history: isDemoMode ? createWebHashHistory(import.meta.env.BASE_URL) : createWebHistory(import.meta.env.BASE_URL),
	routes,
});

// Navigation Guard
router.beforeEach((to, from, next) => {
	if (isDemoMode && !localStorage.getItem("token")) {
		demoStore.startSession();
	}

	const isAuthenticated = !!localStorage.getItem("token");

	//  L'utilisateur pas connecté et veut aller sur une page privée
	if (to.meta.requiresAuth && !isAuthenticated) {
		emitter.emit("notify", {
			message: "Accès réservé. Veuillez vous connecter pour continuer.",
			type: "error",
		});
		return next({
			path: "/connexion",
			query: { message: "session_requise" },
		});
	}

	//  L'utilisateur EST connecté et veut aller sur Connexion ou Inscription
	if (isAuthenticated && (to.path === "/connexion" || to.path === "/inscription")) {
		emitter.emit("notify", {
			message: "Vous êtes déjà connecté !",
			type: "success",
		});
		return next("/"); // On le renvoie à l'accueil
	}

	// Tout est normal, on laisse passer
	next();
});

export default router;

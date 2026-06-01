<template>
	<nav
		:class="[
			// Sur mobile : Barre horizontale en bas
			'fixed bottom-0 left-0 w-full h-16 flex-row border-t z-50 px-2',
			// Sur PC : Barre latérale gauche
			'md:bottom-auto md:top-0 md:h-screen md:flex-col md:border-r md:border-t-0 md:px-4 md:py-4',
			// Largeur sur PC
			isCollapsed ? 'md:w-24' : 'md:w-80',
		]"
		class="flex items-center bg-white border-gray-200 transition-all duration-300 shadow-[0_-4px_15px_rgba(0,0,0,0.05)] md:shadow-2xl"
	>
		<!--  HEADER DESKTOP (Logo + Toggle) Caché sur mobile -->
		<div class="hidden md:flex md:flex-col md:w-full md:mb-6">
			<!-- Bouton Toggle -->
			<div class="flex items-center justify-center mb-4">
				<button
					@click="toggleSidebar"
					class="p-3 bg-gray-200 rounded-full hover:bg-gray-300 transition-all duration-300"
				>
					<ChevronRight
						class="w-6 h-6 text-gray-600 transition-transform duration-300"
						:class="{ 'rotate-180': !isCollapsed }"
					/>
				</button>
			</div>

			<!-- Logo -->
			<div class="flex flex-col items-center mt-2 overflow-hidden">
				<img
					src="../assets/logo.png"
					alt="Logo"
					class="transition-all"
					:class="isCollapsed ? 'w-16 mb-0' : 'w-32 mb-4'"
				/>
				<span
					v-if="!isCollapsed"
					class="text-yellow-500 font-black text-3xl tracking-tight whitespace-nowrap border-b-4 border-blue-950"
				>
					Triumvirat
				</span>
			</div>
		</div>

		<!-- CONTAINER PRINCIPAL Flex-row sur mobile, flex-col sur desktop -->
		<div class="flex flex-row md:flex-col justify-around md:justify-between w-full h-full md:flex-1">
			
			<!-- NAVIGATION PRINCIPALE-->
			<div class="flex flex-row md:flex-col justify-around md:justify-start w-full md:space-y-2 gap-2 md:gap-0">
				<!-- Accueil -->
				<router-link
					to="/"
					@click="autoClose"
					class="nav-item flex-1 md:flex-none justify-center md:justify-start group"
				>
					<Home
						class="w-7 h-7 md:w-6 md:h-6 md:min-w-[20px] md:ml-2 group-hover:scale-110 transition-transform"
					/>
					<span v-if="!isCollapsed" class="hidden md:inline ml-4 font-semibold text-lg">Accueil</span>
				</router-link>

				<!-- Profil -->
				<router-link 
					v-if="authState.isLoggedIn" 
					to="/profil" 
					@click="autoClose" 
					class="nav-item flex-1 md:flex-none justify-center md:justify-start group"
				>
					<User
						class="w-7 h-7 md:w-6 md:h-6 md:min-w-[20px] md:ml-2 group-hover:scale-110 transition-transform"
					/>
					<span v-if="!isCollapsed" class="hidden md:inline ml-4 font-semibold text-lg">Profil</span>
				</router-link>

				<!-- Paramètres mobile -->
				<router-link 
					v-if="authState.isLoggedIn" 
					to="/settings" 
					@click="autoClose" 
					class="nav-item flex-1 md:hidden justify-center group"
				>
					<Settings class="w-7 h-7 group-hover:scale-110 transition-transform" />
				</router-link>

				<!-- Déconnexion mobile -->
				<button
					v-if="authState.isLoggedIn"
					@click="handleLogout"
					class="nav-item flex-1 md:hidden justify-center text-red-500 hover:bg-red-50 group"
				>
					<DoorClosed class="w-7 h-7 group-hover:scale-110 transition-transform" />
				</button>
			</div>

			<!-- FOOTER DESKTOP (Paramètres + Déconnexion) Caché sur mobile - En bas de la sidebar sur desktop -->
			<div 
				v-if="authState.isLoggedIn" 
				class="hidden md:flex md:flex-col md:space-y-2 md:mt-auto md:pt-4 md:border-t md:border-gray-200"
			>
				<!-- Paramètres (desktop) -->
				<router-link
					to="/settings"
					@click="autoClose"
					class="nav-item group"
					:class="isCollapsed ? '' : 'justify-start'"
				>
					<Settings class="w-6 h-6 min-w-[20px] md:ml-2 group-hover:scale-110 transition-transform" />
					<span v-if="!isCollapsed" class="ml-4 font-semibold text-lg">Paramètres</span>
				</router-link>

				<!-- Déconnexion (desktop) -->
				<button
					@click="handleLogout"
					class="nav-item text-red-500 hover:bg-red-50 group"
					:class="isCollapsed ? '' : 'justify-start'"
				>
					<DoorClosed class="w-6 h-6 min-w-[20px] md:ml-2 group-hover:scale-110 transition-transform" />
					<span v-if="!isCollapsed" class="ml-4 font-bold">Déconnexion</span>
				</button>
			</div>
		</div>
	</nav>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { authService } from "../services/authService.js";
import { authState, updateAuthState } from "../utils/authEvents.js";
import { Home, User, ChevronRight, DoorClosed, Settings } from "lucide-vue-next";

const isCollapsed = ref(true);

const toggleSidebar = () => {
	isCollapsed.value = !isCollapsed.value;
};

const autoClose = () => {
	if (window.innerWidth < 768) isCollapsed.value = true;
};

const router = useRouter();

const handleLogout = async () => {
	try {
		await authService.logout();
		updateAuthState();
		router.push("/connexion");
	} catch (error) {
		localStorage.removeItem("token");
		updateAuthState();
		router.push("/connexion");
	}
};
</script>

<style scoped>
@reference "tailwindcss";

.nav-item {
	@apply flex items-center p-2 md:p-3 text-gray-600 hover:bg-blue-50 hover:text-amber-600 rounded-xl transition-all duration-200 cursor-pointer;
}

/* Force le respect de md:hidden */
@media (min-width: 768px) {
	.md\:hidden {
		display: none !important;
	}
}

.router-link-active {
	@apply bg-blue-600 text-white hover:bg-blue-700 hover:text-white shadow-md;
}
</style>

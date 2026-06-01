<template>
    <div class="min-h-screen flex items-center justify-center bg-transparent relative overflow-hidden text-white px-4 ">
        
        <div class="absolute top-1/4 -left-20 w-80 h-80 bg-red-600 rounded-full mix-blend-multiply filter blur-[120px] opacity-30 animate-pulse"></div>
        <div class="absolute bottom-1/4 -right-20 w-80 h-80 bg-pink-600 rounded-full mix-blend-multiply filter blur-[120px] opacity-30 animate-pulse" style="animation-delay: 2s;"></div>

        <div class="relative z-10 text-center backdrop-blur-sm bg-white border border-white/10 p-12 rounded-3xl shadow-2xl max-w-lg w-full">
            
            <h1 class="text-9xl font-black mb-4 tracking-tighter animate-bounce-slow bg-gray-600 bg-clip-text text-transparent">
                404
            </h1>

            <div class="h-1 w-20 bg-gradient-to-r from-red-500 to-pink-500 mx-auto mb-8 rounded-full"></div>

            <h2 class="text-2xl font-bold mb-4">Perdu dans le vide ?</h2>
            
            <p class="text-gray-500 font-semibold mb-10 leading-relaxed">
                Désolé, mais la page que vous cherchez semble avoir disparu dans une autre dimension.
            </p>

            <router-link 
                to="/" 
                class="group relative inline-flex items-center justify-center px-8 py-4 font-bold transition-all duration-200 bg-gradient-to-r from-red-600 to-pink-600 rounded-xl hover:from-red-500 hover:to-pink-500 hover:shadow-[0_0_20px_rgba(225,29,72,0.4)] active:scale-95"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour à l'accueil
            </router-link>
        </div>
    </div>
</template>



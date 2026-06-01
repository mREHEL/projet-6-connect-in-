<script setup>
import { reactive } from 'vue'
import { authService } from '../services/authService';
import { useRouter } from 'vue-router';
import { ref } from 'vue';

const errorMessage = ref('');
const errors = ref({});
const router = useRouter();

// On crée un objet unique pour tout le formulaire
const form = reactive({
  username: '',
  first_name: '', // On s'assure que ces noms correspondent aux colonnes de la DB Laravel
  last_name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const handleRegister = async () => {
    errorMessage.value = ''
    errors.value = {};
  try {
    await authService.register(form)
    router.push('/')
  } catch (error) {
    if (error.status === 429) {
        errorMessage.value = "Vous avez fait trop de tentatives. Attendez un peu.";
    } else {
        errorMessage.value = error.message;
        if (error.errors) errors.value = error.errors;
    }

  }
}

</script>


<template>
  <div class="max-w-md mx-4 md:mx-auto mt-10 p-6 bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-4">{{ "Inscription" }}</h1>
    <form class="space-y-4" @submit.prevent="handleRegister">
      <div>
        <label class="block text-sm font-medium text-gray-700" >{{ "Nom d'utilisateur" }}</label>
        <input v-model="form.username" type="text" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Entrez votre nom d'utilisateur">
      </div>
      <p v-if="errors.username" class="text-red-500 text-sm">
        {{ errors.username[0] }}
      </p>
       <div>
        <label class="block text-sm font-medium text-gray-700">{{ "Prénom" }}</label>
        <input v-model="form.first_name" type="text" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Entrez votre prénom">
      </div>
      <p v-if="errors.first_name" class="text-red-500 text-sm">
        {{ errors.first_name[0] }}</p>
      <div>
        <label class="block text-sm font-medium text-gray-700">{{ "Nom" }}</label>
        <input v-model="form.last_name" type="text" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Entrez votre nom">
      </div>
      <p v-if="errors.last_name" class="text-red-500 text-sm">
        {{ errors.last_name[0] }}</p>
      <div>
        <label class="block text-sm font-medium text-gray-700">{{ "Email" }}</label>
        <input v-model="form.email" type="email" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Entrez votre email">
      </div>
      <p v-if="errors.email" class="text-red-500 text-sm">
        {{ errors.email[0] }}</p>
      <div>
        <label class="block text-sm font-medium text-gray-700">{{ "Mot de passe" }}</label>
        <input v-model="form.password" type="password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Entrez votre mot de passe">
      </div>
      <p v-if="errors.password" class="text-red-500 text-sm">
        {{ errors.password[0] }}</p>
      <div>
        <label class="block text-sm font-medium text-gray-700">{{ "Confirmer le mot de passe" }}</label>
        <input v-model="form.password_confirmation" type="password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Confirmez votre mot de passe">
      </div>
      <div v-if="errorMessage" class="text-red-500 text-sm">
        {{ errorMessage }}
      </div>
     
        <button type="submit" class="w-full py-2 px-4 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
           {{ "S'inscrire" }}</button>
    </form>
    <p class="mt-4 text-center text-sm text-gray-600">
        {{ "Vous avez un compte ?" }} 
        <router-link to="/connexion" class="text-blue-600 hover:underline">
          {{ "Connectez-vous" }}
        </router-link>
      </p>
    </div>
</template>
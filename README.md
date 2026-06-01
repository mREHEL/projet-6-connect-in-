# 📱 Projet Réseau Social Connect'in

## ✨ Fonctionnalités principales

- **🔒 Authentification** : Inscription, connexion et déconnexion sécurisées.
- **📝 Fil d'actualité** : Création, modification et suppression de posts (avec ou sans images).
- **💬 Interactions** : Système de likes et de commentaires (ajout, édition, suppression) sur les publications.
- **👤 Profil Utilisateur** : Personnalisation du profil (photo de profil, bannière, biographie).

## ✨ Fonctionnalités Détaillées

### 🔒 Authentification & Sécurité (Laravel Sanctum)

- Inscription et connexion avec hachage des mots de passe.
- Gestion des sessions via des tokens d'API (Sanctum).
- Déconnexion sécurisée et révocation des tokens.
- Protection des routes (seuls les utilisateurs connectés peuvent publier/interagir).

### 📝 Fil d'Actualité (Posts)

- **Création** : Publication de messages texte avec possibilité d'attacher une image.
- **Lecture** : Affichage d'un fil d'actualité global chronologique.
- **Modification & Suppression** : Un utilisateur peut modifier ou supprimer ses propres publications.

### 💬 Interactions (Communauté)

- **Commentaires** : Ajout, modification et suppression de commentaires sur n'importe quel post.
- **Likes** : Système de "J'aime" / "Je n'aime plus" sur les publications.
- Affichage dynamique des compteurs (nombre de commentaires et de likes).

### 👤 Profil Utilisateur

- Personnalisation du profil : Photo de profil (avatar), bannière de couverture et biographie.
- Génération automatique d'un avatar avec les initiales si aucune photo n'est fournie.
- Affichage de l'historique personnel : page dédiée listant uniquement les posts d'un utilisateur spécifique.

---

## 🛠️ Technologies & Outils

**Frontend (Dossier `/frontend`) :**

- **Framework :** Vue.js (Composition API & `<script setup>`)
- **Build Tool :** Vite
- **Stylisation :** Tailwind CSS
- **Routage :** Vue Router
- **Requêtes HTTP :** Fetch API intégrée dans des Services (`postService.js`, `authService.js`)

**Backend (Racine du projet) :**

- **Framework :** Laravel (PHP)
- **Authentification :** Laravel Sanctum
- **Base de données :** MySQL (via Eloquent ORM)
- **Stockage :** Gestion locale des images (storage/public)

---

## 🚀 Tutoriel d'installation

Voici les étapes pour lancer le projet en local sur votre machine.

## Prérequis

- Docker + Docker Compose installés.

## Démarrage rapide (après clone / download)

Toutes les commandes ci-dessous sont à exécuter depuis le dossier `Triumvirat/`.

1. Ouvrir un terminal dans `Triumvirat`.
2. Créer le fichier d'environnement Docker :
    ```bash
    cp .env.example .env
    ```
3. Vérifier/compléter au minimum ces variables dans `.env` :

    ```dotenv
    DB_CONNECTION=
    DB_HOST=
    DB_PORT=
    DB_DATABASE=
    DB_USERNAME=
    DB_PASSWORD=

    MYSQL_DATABASE=
    MYSQL_ROOT_PASSWORD=
    ```

4. Lancer les services :
    ```bash
    docker compose up --build -d
    ```

## 🐳 Description Docker

Le projet tourne avec `docker-compose` et démarre 4 services :

- **backend** : API Laravel (PHP) sur `http://localhost:8000`
- **frontend** : application Vue/Vite sur `http://localhost:5173`
- **db** : base de données MySQL 8 sur le port `3306`
- **phpmyadmin** : interface DB sur `http://localhost:8080`

Le fichier `Triumvirat/docker-compose.yml` orchestre ces services, les volumes (persistance MySQL + code monté), les variables d'environnement et les ports exposés.

## ⚙️ Rôle du `entrypoint.sh`

Le script `Triumvirat/backend/entrypoint.sh` est exécuté au démarrage du conteneur backend. Il automatise l'initialisation Laravel.

Cela permet à une personne qui clone le dépôt de lancer le projet sans setup manuel Laravel supplémentaire.

## Commandes utiles

À exécuter également depuis `Triumvirat/`.

- Arrêter :
    ```bash
    docker compose down
    ```
- Réinitialiser complètement la base :
    ```bash
    docker compose down -v
    docker compose up --build -d
    ```

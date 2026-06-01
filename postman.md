# 🚀 Documentation Postman : Maîtriser le test d'API

Postman est la plateforme de référence pour le développement et le test d'API. Ce guide permet de comprendre les bases et de structurer tes tests efficacement.

---


## 🛠️ Configuration d'une Requête

Chaque requête dans Postman se compose de quatre éléments principaux :

* **Méthode HTTP :** * `GET` : Récupérer des données.
    * `POST` : Envoyer/Créer des données.
    * `PUT` / `PATCH` : Mettre à jour des données.
    * `DELETE` : Supprimer.
* **URL (Endpoint) :** L'adresse de l'API (ex: `https://api.example.com/users`).
* **Headers :** Clés/Valeurs pour les métadonnées (ex: `Authorization: Bearer <token>` ou `Content-Type: application/json`).
* **Body :** Les données envoyées (généralement en format **JSON** via l'option `raw`).

---

## Le projet Connect'in

---

Pour avoir accès à la collection Postman il faut importer le fichier **`Connect'inAPI.postman_collection.json`** dans Postman. Ce projet contient des requêtes préconfigurées pour tester les différentes routes de l'API qu'on a développée. Et pour les variables d'environnement, importe le fichier **`Laravel dev.postman_environment.json`** qui contient des variables comme `base_url` pour faciliter la configuration.

---


## Explication des routes et comment s'en servir

Dans un premier temps, vérifier le fichier api.php pour voir les différentes routes disponibles. Ensuite, dans Postman, on trouvera des requêtes préconfigurées pour chacune de ces routes.

Ne pas oublier de sélectionner l'environnement `Laravel dev` dans le menu déroulant en haut à droite pour que les variables d'environnement soient prises en compte. Et mettre {{base_url}} dans l'URL et **/route** demandée

### Premiere étape : S'inscrire et se connecter

Il y a des paramétres pour certaines routes. Pour les routes api/register et api/login, il faut envoyer un body en format JSON avec les champs suivants et mettre le header Accept application/json pour que Laravel sache que qu'on attend une réponse en JSON. Voici un exemple de body pour la route **/register** : 

```json
{
    "username": "",
    "last_name": "",
    "first_name": "",
    "email": "example@epitech.eu ",
    "password": "",
    "password_confirmation": ""
}
```

Pour **/login**, le body doit contenir : 

```json
{
    "email": "example@epitech.eu ",
    "password": ""
}
```

Il y a diverses fonctionnalités de sécurité, l'email qui doit être au format email, le mot de passe qui doit être confirmé, etc. Donc il faut faire attention à respecter ces règles pour que les requêtes soient acceptées par l'API. D'ailleurs j'ai bannis de nombreux mots assez embetants comme admin, root, etc. Donc si tu essayes de t'inscrire avec un de ces mots dans le username, on aura une erreur.

D'ailleurs au bout de 5 tentatives d'inscription avec un username ou email interdit, l'utilisateur ne pourra pas s'inscrire pendant 1 minute. Donc fais attention à ne pas faire n'importe quoi avec les données d'inscription.

Et pour le login, au bout de 3 tentatives de connexion avec un email ou mot de passe incorrect, l'utilisateur ne pourra pas se connecter pendant 1 minute. Donc fais attention à ne pas faire n'importe quoi avec les données de connexion.

Voir config/validation.php pour les règles de validation appliquées à chaque champ. Ainsi que Request et Rules pour la gestion des espace, tiret ou autres contournations possibles.

configuration sanctum dans config/sanctum.php pour l'expiration de la session au bout de 40 minutes, pour tester cela au plus vite regler a 1 minute, puis nettoyer le cache de laravel pour que les changements soient pris en compte.

```bash
php artisan config:cache
```

### Les routes avec des paramètres importantes

#### Connexion, Inscription

Pour les autres routes on a oubligatoirement besoin du token (ne pas oublier le header accept: application/json) donc il faut aller dans autorisation de postman et le mettre en Bearer Token puis copier coller le token qu'aura fourni le backend lors de la connexion ou inscription.

Pour la route **/logout**, il suffit de faire une requete POST avec le token d'autorisation pour se déconnecter.
Sans le token on aura une erreur 401 Unauthorized.

#### Les routes liées aux utilisateurs

Pour **/users** une requete **GET** avec le token d'autorisation nous donnera la liste de tous les utilisateurs.
De plus ajout de securité les mails et password si affichés seront totalement masqués pour les autres utilisateurs que soi même.

Idem pour **/users/{id}** une requete **GET** avec le token d'autorisation nous donnera les informations de l'utilisateur correspondant à l'id, mais les mails et password seront totalement masqués pour les autres utilisateurs que soi même.

Pour **/users/{id}** une requete **PUT** avec le token d'autorisation nous permettra de mettre à jour les informations de l'utilisateur correspondant à l'id, mais on ne pourra pas mettre à jour les informations d'un autre utilisateur que soi même cela affichera egalement une erreur 403 Forbidden.
A prendre en compte que pour cette route, le body doit être au format JSON et peut contenir les champs suivants et que certains champs peuvent être vides si on ne souhaite pas les mettre à jour ou ne pas les mettre:

```json
{
    "username": "",
    "email": "",
    "first_name": "",
    "last_name": "",
    "bio": "",
    "current_password": "",
    "password": "",
    "password_confirmation": ""
}
```
D'ailleurs pour la méthode **PUT** il ya aussi des images à envoyer, pour cela il faut utiliser l'option **form-data** et mettre la methode en **Post** pour mettre les champs suivants :

| Key | Value | Type |
| :--- | :--- | :--- |
| username | {{username}} | Text |
| email | {{email}} | Text |
| first_name | {{first_name}} | Text |
| last_name | {{last_name}} | Text |
| bio | {{bio}} | Text |
| current_password | {{current_password}} | Text |
| password | {{password}} | Text |
| password_confirmation | {{password_confirmation}} | Text |
| cover_image | (fichier image) | File |
| profile_image | (fichier image) | File |
| delete_profile_photo | (boolean) | Text |
| delete_cover_image | (boolean) | Text |
| _method | PUT | Text |


Pour cette route **/users/{id}** une requete **DELETE** avec le token d'autorisation nous permettra de supprimer l'utilisateur correspondant à l'id, mais on ne pourra pas supprimer un autre utilisateur que soi même cela affichera egalement une erreur 403 Forbidden.

Sans le token on aura une erreur 401 Unauthorized.

#### Les routes liées aux posts

Ensuite pour les autres routes comme **/posts** ou **/posts/{id}** il faut aussi le token d'autorisation pour pouvoir les utiliser, et on aura des règles de sécurité similaires pour éviter que les utilisateurs puissent modifier ou supprimer les posts des autres utilisateurs.

Pour la route **/posts** une requete **GET** avec le token d'autorisation nous donnera la liste de tous les posts avec le nombres likes et comments ainsi que les authorisation sur les modification et suppression, et pour **/posts/{id}** une requete **GET** avec le token d'autorisation nous donnera les informations du post correspondant à l'id avec tous les commentaires.

Pour la route **/posts** une requete **POST** avec le token d'autorisation nous permettra de créer un nouveau post, et pour **/posts/{id}** une requete **PUT** avec le token d'autorisation nous permettra de mettre à jour les informations du post correspondant à l'id, mais on ne pourra pas mettre à jour les informations d'un autre post que soi même cela affichera egalement une erreur 403 Forbidden.

```json
{
    "content": "" // si on ne met pas d'image.
}
```

Pour les requêtes **POST** et **PUT** de la route **/posts** et **/posts/{id}** il y a aussi des images à envoyer, pour cela il faut utiliser l'option form-data et mettre la methode en Post pour mettre les champs suivants :

| Key | Value | Type |
| :--- | :--- | :--- |
| content | {{content}} | Text |
| image | (fichier image) | File |
| _method | PUT | Text |

Et pour la route **/posts/{id}** une requete **DELETE** avec le token d'autorisation nous permettra de supprimer le post correspondant à l'id, mais on ne pourra pas supprimer un autre post que soi même cela affichera egalement une erreur 403 Forbidden.

#### La route **/posts/{post_id}/like**

Ensuite on a la route **/posts/{id}/like** une requete **POST** avec le token d'autorisation nous permettra de liker ou unliker le post correspondant à l'id. Ici il faudrait le token exact de l'utilisateur pour le faire fonctionner.

#### La route **/posts/{post_id}/comments**

Pour la route **/posts/{post_id}/comments** une requete **POST** avec le token d'autorisation nous permettra de commenter le post correspondant à l'id, et pour **/comments/{comment_id}** une requete **DELETE** avec le token d'autorisation nous permettra de supprimer le commentaire correspondant à l'id, mais on ne pourra pas supprimer un autre commentaire que soi même cela affichera egalement une erreur 403 Forbidden.
**/comments/{comment_id}** une requete **PUT** avec le token d'autorisation nous permettra de mettre à jour les informations du commentaire correspondant à l'id, mais on ne pourra pas mettre à jour les informations d'un autre commentaire que soi même cela affichera egalement une erreur 403 Forbidden.

```json
{
    "content": ""
}
```
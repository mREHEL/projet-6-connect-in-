#!/bin/sh

cd /app

# Installer les dépendances si vendor n'existe pas
if [ ! -d "vendor" ]; then
    echo "Installation des dépendances PHP..."
    composer install
fi

# Gérer le fichier .env
if [ ! -f .env ]; then
    echo "Création du fichier .env..."
    cp .env.example .env
fi

if [ ! -f config/cors.php ]; then
    echo "Publication de la configuration CORS..."
    php artisan config:publish cors
fi

# Sécurité : Générer la clé si elle est vide
php artisan key:generate --no-interaction --force

# Nettoyage des caches (vital pour Docker)
echo "Nettoyage des caches Laravel..."
php artisan config:clear
php artisan cache:clear

# Attendre MySQL
echo "Attente de MySQL..."
# On utilise les variables pour être sûr que le test de connexion 
# correspond à ce que Laravel va utiliser
until php -r "new PDO('mysql:host=db;dbname=' . \$_ENV['DB_DATABASE'], \$_ENV['DB_USERNAME'], \$_ENV['DB_PASSWORD']);" 2>/dev/null; do
  echo "Attente de la base de données..."
  sleep 2
done

# Exécution des migrations
echo "Lancement des migrations..."
# On ajoute config:cache pour forcer Laravel à lire le .env tout juste créé/modifié
php artisan config:cache
php artisan migrate --force --database=mysql
php artisan storage:link

# Lancement du serveur
echo "Lancement de Laravel sur le port 8000..."
php artisan serve --host=0.0.0.0 --port=8000
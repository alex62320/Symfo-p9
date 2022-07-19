Création d'un nouveau projet

1. créer le dossier via symfony

symfony new --webapp --version=lts symfony

2. Installer les outils et certificat

// outils

sudo apt install libnss3-tools

// les certificat

symfony server:ca:install

3. Lancement du serveur symfony

symfony serve

4. Créer la base de données

// créer la bdd via le script dans installscript

./mkdb.sh

5. Création du fichier .env.local

allez a la racine du projet symfony crée un fichier .env.local

ajouter dans le fichier cette commande si-dessous, en remplacant db_user par l'utilisateur de la bdd, db_password par le mot de passe de l'utilisateur de bdd et db_name par le nom de la bdd

DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7&charset=utf8mb4"

5. commande symfony

// crée une entité 

php bin/console make:entity

6. migration de la table dans la bdd 

6.1pour initier la migration vers la bdd

php bin/console make:migration

// pour envoyer les données vers la bdd

php bin/console do:mi:mi

// verifié la synchro avec la bdd

php bin/console do:sc:va

//installer les fixtures 

composer require --dev orm-fixtures

composer require fakerphp/faker

// créer un controller

php bin/console make:controller

////////

Entity <- représentation des données

Repository <- lecteurs de données

ObjectManager <- fournisseur de Repository + un outils qui permet d'écrire des données
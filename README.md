# Urbaverse 🌱

## Description
Urbaverse est une plateforme web innovante qui vise à encourager et à gérer les actions écologiques dans les quartiers urbains. L'application gamifie les actions durables en proposant des défis, des récompenses et un suivi de progression interactif.

## Fonctionnalités Principales

### Frontoffice (Interface Utilisateur)
- 🎮 Système de défis écologiques avec différentes étapes
- 📸 Validation des actions par reconnaissance d'images
- 🌟 Système de points et récompenses
- 👤 Gestion de profil utilisateur
- 📊 Suivi de progression interactif
- 🏆 Classement et statistiques
- 🎯 Interface gamifiée avec animations

### Backoffice (Administration)
- 📋 Dashboard de gestion
- 🎯 Gestion des défis et des étapes
- 👥 Administration des utilisateurs
- 📊 Statistiques et rapports
- 🔧 Configuration du système

## Technologies Utilisées
- PHP 8.x
- PostgreSQL
- JavaScript
- TensorFlow.js (pour la reconnaissance d'images)
- CSS3
- Composer (gestion des dépendances)
- Bootstrap 5
- jQuery

## Prérequis
- Serveur web (Apache/Nginx)
- PHP 8.x ou supérieur
- PostgreSQL 12 ou supérieur
- Composer
- Module PHP PDO
- Module PHP GD (pour le traitement d'images)

## Installation

1. Cloner le repository
```bash
git clone [URL_DU_REPO]
cd Urbaverse
```

2. Installer les dépendances
```bash
composer install
```

3. Configurer la base de données
- Créer une base de données PostgreSQL
- Importer le fichier `database.sql`
- Configurer les paramètres de connexion dans `config/database.php`

4. Configurer le serveur web
- Pointer le document root vers le dossier `public`
- Assurer que le mod_rewrite est activé (Apache)

5. Permissions
```bash
chmod 755 -R storage/
chmod 755 -R public/uploads/
```

## Structure du Projet
```
Urbaverse/
├── assets/          # Ressources statiques (CSS, JS, images)
├── controller/      # Contrôleurs de l'application
├── model/          # Modèles et logique métier
├── view/           # Vues de l'application
│   ├── frontoffice/ # Interface utilisateur
│   └── backoffice/  # Interface d'administration
├── vendor/         # Dépendances Composer
└── public/         # Point d'entrée public
```

## Utilisation

### Frontoffice
1. Accéder à l'application via l'URL configurée
2. Créer un compte ou se connecter
3. Parcourir les défis disponibles
4. Participer aux défis et gagner des points

### Backoffice
1. Accéder à `/backoffice`
2. Se connecter avec les identifiants administrateur
3. Gérer les défis, utilisateurs et paramètres

## Sécurité
- Authentification sécurisée
- Protection contre les injections SQL
- Validation des entrées utilisateur
- Gestion sécurisée des sessions
- Protection CSRF

## Contribution
Les contributions sont les bienvenues ! Pour contribuer :
1. Fork le projet
2. Créer une branche pour votre fonctionnalité
3. Commiter vos changements
4. Pousser vers la branche
5. Ouvrir une Pull Request

## Licence
Ce projet est sous licence [MIT](LICENSE).

## Contact
Pour toute question ou suggestion, n'hésitez pas à nous contacter :
- Email : contact@urbaverse.fr
- Site web : www.urbaverse.fr

## Remerciements
- Tous les contributeurs
- La communauté open source
- Les utilisateurs de la plateforme

---
Développé avec ❤️ pour un avenir plus vert 
# 📊 BigScreenSurvey

**BigScreenSurvey** est une application web moderne de sondages et d'enquêtes avec une architecture frontend/backend découplée, conçue pour créer, gérer et analyser des sondages de manière intuitive et efficace.

## 📋 Table des matières

- [À propos](#à-propos)
- [Fonctionnalités](#fonctionnalités)
- [Architecture](#architecture)
- [Technologies utilisées](#technologies-utilisées)
- [Installation](#installation)
- [Structure du projet](#structure-du-projet)
- [Configuration](#configuration)
- [Utilisation](#utilisation)
- [API Endpoints](#api-endpoints)
- [Contributeurs](#contributeurs)

## 🎯 À propos

BigScreenSurvey est une plateforme complète permettant de créer des sondages personnalisés, de collecter des réponses et d'analyser les résultats en temps réel. L'application utilise une architecture moderne séparant le frontend du backend pour une meilleure scalabilité et maintenabilité.

## ✨ Fonctionnalités

### Création de sondages
- 📝 Création de sondages personnalisés
- ❓ Plusieurs types de questions (choix multiples, texte libre, échelle, etc.)
- 🎨 Interface de création intuitive
- 📋 Modèles de sondages prédéfinis

### Gestion des réponses
- 📊 Collecte en temps réel des réponses
- 🔒 Sécurité et anonymat des participants
- 💾 Stockage sécurisé des données
- 📥 Export des résultats

### Analyse et visualisation
- 📈 Graphiques et statistiques en temps réel
- 📊 Tableaux de bord interactifs
- 🔍 Filtres et segmentation des données
- 📑 Rapports détaillés

### Interface utilisateur
- 📱 Design responsive (mobile, tablette, desktop)
- 🎨 Interface moderne et épurée
- ⚡ Navigation fluide et rapide
- 🌐 Multi-langues (si applicable)

## 🏗 Architecture

Le projet utilise une architecture **Client-Server** avec séparation complète du frontend et du backend :

```
BigScreenSurvey/
│
├── bigscreen-front-main/      # Application Frontend
│   └── Interface utilisateur React/JavaScript
│
└── BigScreenSurvey_backend/   # API Backend
    └── Serveur et logique métier
```

### Frontend
- Application web moderne
- Interface utilisateur interactive
- Communication avec le backend via API REST

### Backend
- API RESTful
- Gestion de la logique métier
- Connexion à la base de données
- Authentification et autorisation

## 🛠 Technologies utilisées

### Frontend
- **React** 19.1.1 - Framework JavaScript moderne
- **React Router DOM** 7.8.0 - Navigation et routing
- **Axios** 1.11.0 - Client HTTP pour les appels API
- **Chart.js** 4.5.0 + **React-Chartjs-2** 5.3.0 - Visualisation de données
- **React Icons** 5.5.0 - Bibliothèque d'icônes
- **React Toastify** 11.0.5 - Notifications toast
- **React Testing Library** - Tests unitaires et d'intégration

### Backend
- **Laravel** - Framework PHP moderne et élégant
- **Laravel Mix** 6.0.6 - Compilation des assets
- **PHP** - Langage backend
- **MySQL/MariaDB** - Base de données relationnelle (standard Laravel)
- **API RESTful** - Architecture d'API

## 📥 Installation

### Prérequis

**Frontend :**
- Node.js (v14.0 ou supérieur)
- npm ou yarn
- Git

**Backend :**
- PHP (v7.4 ou supérieur, v8.0+ recommandé)
- Composer (gestionnaire de dépendances PHP)
- MySQL ou MariaDB
- Apache ou Nginx
- Laravel (installé via Composer)

### Installation du Backend (Laravel)

```bash
# Naviguer vers le dossier backend
cd BigScreenSurvey_backend

# Installer les dépendances PHP avec Composer
composer install

# Installer les dépendances npm pour Laravel Mix
npm install

# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application Laravel
php artisan key:generate

# Configurer la base de données dans le fichier .env
# Éditer .env avec vos configurations

# Exécuter les migrations de base de données
php artisan migrate

# (Optionnel) Peupler la base de données avec des données de test
php artisan db:seed

# Compiler les assets avec Laravel Mix
npm run dev

# Démarrer le serveur de développement Laravel
php artisan serve
# Le backend sera accessible sur http://localhost:8000
```

### Installation du Frontend (React)

```bash
# Naviguer vers le dossier frontend
cd bigscreen-front-main

# Installer les dépendances npm
npm install

# Configurer l'URL de l'API backend
# Créer un fichier .env à la racine du frontend
echo "REACT_APP_API_URL=http://localhost:8000/api" > .env

# Démarrer l'application React en mode développement
npm start
# L'application sera accessible sur http://localhost:3000
```

## 📁 Structure du projet

```
BigScreenSurvey/
│
├── bigscreen-front-main/           # Application Frontend
│   ├── src/                        # Code source
│   │   ├── components/            # Composants réutilisables
│   │   ├── pages/                 # Pages de l'application
│   │   ├── services/              # Services API
│   │   ├── utils/                 # Utilitaires
│   │   └── App.js                 # Composant principal
│   ├── public/                    # Fichiers statiques
│   └── package.json               # Dépendances frontend
│
└── BigScreenSurvey_backend/        # API Backend
    ├── controllers/               # Contrôleurs
    ├── models/                    # Modèles de données
    ├── routes/                    # Routes API
    ├── middleware/                # Middlewares
    ├── config/                    # Configuration
    └── package.json               # Dépendances backend
```

## ⚙️ Configuration

### Variables d'environnement - Backend (Laravel)

Créer/modifier le fichier `.env` dans `BigScreenSurvey_backend/` :

```env
APP_NAME=BigScreenSurvey
APP_ENV=local
APP_KEY=base64:... # Généré automatiquement par php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000

# Configuration de la base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bigscreensurvey
DB_USERNAME=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe

# Configuration mail (optionnel)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@bigscreensurvey.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Variables d'environnement - Frontend (React)

Créer un fichier `.env` dans `bigscreen-front-main/` :

```env
REACT_APP_API_URL=http://localhost:8000/api
REACT_APP_ENV=development
```

## 🚀 Utilisation

### Démarrage en développement

1. **Démarrer le backend Laravel** (Terminal 1)
```bash
cd BigScreenSurvey_backend
php artisan serve
# Le backend sera disponible sur http://localhost:8000
```

2. **Compiler les assets en mode watch** (Terminal 2 - optionnel)
```bash
cd BigScreenSurvey_backend
npm run watch
```

3. **Démarrer le frontend React** (Terminal 3)
```bash
cd bigscreen-front-main
npm start
# Le frontend sera disponible sur http://localhost:3000
```

4. **Accéder à l'application**
   - Frontend (Interface utilisateur): `http://localhost:3000`
   - Backend API: `http://localhost:8000/api`

### Démarrage en production

```bash
# Build du frontend React
cd bigscreen-front-main
npm run build

# Compiler les assets Laravel pour production
cd ../BigScreenSurvey_backend
npm run production

# Optimiser Laravel pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Configurer le serveur web (Apache/Nginx) pour pointer vers
# le dossier public/ de Laravel
```

## 🔌 API Endpoints

### Authentification
```
POST   /api/auth/register      # Inscription
POST   /api/auth/login         # Connexion
POST   /api/auth/logout        # Déconnexion
GET    /api/auth/me            # Profil utilisateur
```

### Sondages
```
GET    /api/surveys            # Liste des sondages
POST   /api/surveys            # Créer un sondage
GET    /api/surveys/:id        # Détails d'un sondage
PUT    /api/surveys/:id        # Modifier un sondage
DELETE /api/surveys/:id        # Supprimer un sondage
```

### Réponses
```
POST   /api/surveys/:id/responses    # Soumettre une réponse
GET    /api/surveys/:id/results      # Résultats d'un sondage
GET    /api/surveys/:id/analytics    # Analyses détaillées
```

## 🧪 Tests

```bash
# Tests frontend (React Testing Library)
cd bigscreen-front-main
npm test

# Tests backend (Laravel PHPUnit)
cd BigScreenSurvey_backend
php artisan test
# ou
./vendor/bin/phpunit
```

## 📦 Dépendances principales

### Frontend (React)
```json
{
  "react": "^19.1.1",
  "react-router-dom": "^7.8.0",
  "axios": "^1.11.0",
  "chart.js": "^4.5.0",
  "react-chartjs-2": "^5.3.0",
  "react-icons": "^5.5.0",
  "react-toastify": "^11.0.5"
}
```

### Backend (Laravel)
```json
{
  "laravel-mix": "^6.0.6",
  "axios": "^0.21",
  "lodash": "^4.17.19"
}
```

## 📦 Build et Déploiement

### Build Frontend
```bash
cd bigscreen-front-main
npm run build
# Les fichiers de build seront dans le dossier build/
```

### Déploiement
Les fichiers peuvent être déployés sur différentes plateformes :

**Frontend React :**
- Vercel (recommandé pour React)
- Netlify
- GitHub Pages
- AWS S3 + CloudFront
- Firebase Hosting

**Backend Laravel :**
- Heroku
- DigitalOcean
- AWS EC2
- Laravel Forge (recommandé)
- Laravel Vapor (serverless)
- Shared hosting avec cPanel

**Option Full-stack :**
- Docker + Docker Compose
- VPS avec Apache/Nginx

## 👥 Contributeurs

- **Ahmed Ailaoui** - *Développeur principal* - [@AhmedAilaoui](https://github.com/AhmedAilaoui)

## 🚀 Améliorations futures

- [ ] Notifications en temps réel (WebSocket)
- [ ] Mode hors ligne (PWA)
- [ ] Export PDF des rapports
- [ ] Intégration avec outils tiers (Google Forms, Typeform)
- [ ] Analyse IA des réponses ouvertes
- [ ] Templates de sondages personnalisables
- [ ] Tableau de bord administrateur avancé
- [ ] Support multi-tenants
- [ ] API publique pour intégrations tierces
- [ ] Application mobile native (React Native)

## 📞 Support

Pour toute question ou problème :
- Ouvrir une [issue](https://github.com/AhmedAilaoui/BigScreenSurvey/issues)
- Contacter l'équipe de développement

---

⭐ Si vous aimez ce projet, n'hésitez pas à lui donner une étoile sur GitHub !

💡 **Développé avec passion par Ahmed Ailaoui**

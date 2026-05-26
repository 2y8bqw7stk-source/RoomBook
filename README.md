# 🗓️ RoomBook · ESISA

RoomBook est une solution moderne de gestion de réservation de salles de réunion conçue pour l'ESISA.  
Elle permet de piloter l'occupation des espaces en temps réel, d'éviter les conflits d'horaires et de simplifier l'organisation interne via une interface fluide et réactive.

--

## ✨ Fonctionnalités

- **Tableau de Bord Dynamique** : Visualisation en un coup d'œil du nombre de salles, des réservations confirmées, des demandes en attente et des employés actifs.

- **Planning Interactif** : Une grille horaire détaillée (08:00 - 18:00) affichant l'occupation par salle.

- **Réservation Rapide (QuickBook)** : Cliquez sur un créneau vide dans le planning pour ouvrir le formulaire pré-rempli avec l'heure et la salle choisies.

- **Contrôle Anti-Conflit** : Le backend PHP vérifie automatiquement la disponibilité de la salle avant toute insertion pour empêcher les doubles réservations.

- **Filtres Avancés** : Tri instantané par date ou par salle spécifique pour une meilleure visibilité.

- **Design "Glassmorphism"** : Interface sombre élégante avec des effets de transparence et des animations fluides.

--

## 🛠️ Stack Technique

- **Frontend** : HTML5, CSS3 (Variables, Flexbox, Grid), JavaScript (ES6+, Fetch API)
- **Backend** : PHP 8.x avec une architecture API RESTful
- **Base de données** : MySQL (PDO pour la sécurité des requêtes)
- **Design** : Polices Syne et DM Sans pour une typographie professionnelle

--

## 📂 Structure du Projet

├── index.html (Interface utilisateur (SPA) et logique JavaScript)

├── api.php (API backend (GET, POST, PUT, DELETE))

└── database.sql (Schéma SQL et données d'initialisation)

--

## 🚀 Installation & Configuration

### 1. Base de données
Importez le fichier `database.sql` dans votre gestionnaire MySQL (ex: phpMyAdmin).  
Cela créera la base **roombook** et les tables suivantes :

- **salles** : Liste des espaces disponibles
- **employes** : Répertoire du personnel (IT, RH, Marketing, etc.)
- **reservations** : Journal des créneaux réservés

--

### 2. Backend
Vérifiez la configuration de la connexion dans `api.php`. Par défaut :

- Host : localhost  
- DB Name : roombook  
- User : root  

--

### 3. Lancement
Placez le dossier du projet dans votre serveur local (ex: `htdocs` pour XAMPP), puis accédez à :

```
http://localhost/votre-dossier/
```

--

## 📡 API Endpoints

| Méthode | Action        | Description |
|----------|--------------|-------------|
| GET      | salles       | Récupère la liste des salles avec leurs capacités |
| GET      | employes     | Récupère la liste des employés (recherche possible avec `?q=`) |
| GET      | reservations | Récupère les plannings des réservations (filtres : `date`, `salle_id`) |
| POST     | reservation  | Crée une nouvelle réservation après vérification des conflits |
| PUT      | reservation  | Met à jour une réservation existante via son `id` |
| DELETE   | reservation  | Supprime une réservation via son `id` |

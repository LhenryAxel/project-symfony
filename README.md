# 📸 Projet Symfony - Gestion d'Images et Statistiques

Ce projet Symfony se compose de **trois sous-applications distinctes** :

- `image-public` : Permet l'upload d'images depuis une interface utilisateur publique
- `image-api` : Expose une API RESTful pour stocker, récupérer et suivre les statistiques des images
- `image-admin` : Interface d'administration pour visualiser les images, leurs statistiques, générer des fichiers Excel et envoyer des emails de rapport

> **Projet réalisé par Thomas, Quentin, Amin et Axel**

---

## 📦 Fonctionnalités principales

### ✅ `image-public`
- Formulaire pour uploader une image
- Envoie l'image via l'API (serveur `image-api`)

### ✅ `image-api`
- Enregistrement d'images
- Routes pour visualiser/télécharger les images
- Ajout automatique de statistiques (vues, requêtes, téléchargements)
- API REST :
  - `GET /api/images` : Liste des images
  - `POST /api/upload` : Upload d'image
  - `GET /api/image/view/{filename}` : Vue d'une image (+ stat Vue)
  - `GET /api/image/url/{filename}` : Accès direct à l'image (+ stat RequeteUrl)
  - `GET /api/image/download/{filename}` : Téléchargement + stat
  - `GET /api/stat/{filename}` : Statistiques d'une image
  - `GET /api/stat/all` : Statistiques de toutes les images
  - `DELETE /api/image/delete/{id}` : Supprimer une image + stats associées

### ✅ `image-admin`
- Interface de visualisation des images
- Détails et graphiques de stats via Chart.js
- Génération de fichier Excel
- Envoi d'email automatique ou manuel

---

## 🚀 Installation

### 1. Cloner le projet
```bash
git clone <repo>
```

### 2. Configurer `.env`
Configurer la base de données, et pour l'envoi d'email, modifier :
```env
MAILER_DSN=smtp://<identifiants SMTP>
```
> En développement, vous pouvez utiliser [Mailtrap](https://mailtrap.io/) pour tester l'envoi.

### 3. Créer et préparer la base de données
> Une base de données nommée `open_image_db` sera automatiquement créée si elle n'existe pas.
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load --append
```

### 4. Lancer les serveurs Symfony
Dans chaque dossier (`image-api`, `image-admin`, `image-public`), exécutez :
```bash
symfony server:start --port=8000 # pour image-public
symfony server:start --port=8001 # pour image-admin
symfony server:start --port=8002 # pour image-api
```

### 5. Accéder à l'application
- Interface publique : http://localhost:8000
- Interface admin : http://localhost:8001
- API : http://localhost:8002

---

## 📊 Commandes utiles

> À exécuter **dans le dossier `image-api`**

### 🎯 Générer un fichier Excel des statistiques
```bash
php bin/console lc:excel --out monfichier.xlsx
```
> Le fichier sera généré dans `image-api/public/`

### ✉️ Envoyer un email manuellement
```bash
php bin/console lc:email
```

### 🔁 Lancer le worker des tâches planifiées (email hebdo)
```bash
php bin/console messenger:consume scheduler_default -vv
```

---

## ⚙️ Dépendances Composer

Avant de lancer le projet, assurez-vous d’avoir installé toutes les dépendances avec :

```bash
composer install
```

Cela installera automatiquement toutes les librairies nécessaires déclarées dans les fichiers `composer.json` de chaque dossier (`image-api`, `image-admin`, `image-public`).

### Principales librairies utilisées :

- `phpoffice/phpspreadsheet` : Génération de fichiers Excel
- `symfony/mailer` : Envoi d’emails
- `symfony/scheduler` : Tâches planifiées
- `symfony/http-client` : Requêtes entre les applications
- `symfony/messenger` : Gestion des workers pour les emails planifiés

---

## 📁 Arborescence simplifiée
```
project-symfony/
├── image-api/
│   ├── src/...
│   └── public/monfichier2.xlsx
├── image-admin/
│   └── templates/stat/...
├── image-public/
    └── templates/upload.html.twig
```

---

## 🛠️ Technologies utilisées
- Symfony 6+
- Doctrine ORM
- Chart.js
- PhpSpreadsheet
- Mailer + Mailtrap
- Twig + Tailwind + DaisyUI (interface admin)

---

## 📧 Contact
Projet réalisé dans le cadre d’un projet pédagogique.

Si besoin, contactez les auteurs : Thomas, Quentin, Amin, Axel. 😊


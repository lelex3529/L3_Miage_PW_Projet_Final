# L3_Miage_PW_Projet_Final

Projet de fin de semestre en programmation web - Application de gestion de tuteurs, étudiants et visites en entreprise.

**Auteurs :** Raky DIA et Alexandre AUFFRAY

## 📋 Description

Application web développée avec Symfony 6.4 permettant la gestion complète des tuteurs en entreprise, de leurs étudiants et des visites effectuées. Le projet inclut une interface web complète et une API REST.

### Fonctionnalités principales

- 🔐 **Authentification** : Connexion des tuteurs par email
- 👥 **Gestion des étudiants** : CRUD complet avec informations de formation
- 📅 **Gestion des visites** : Planification, suivi et compte rendu des visites
- 📊 **Dashboard** : Vue d'ensemble avec statistiques et visites à venir
- 📄 **Export PDF** : Génération de comptes rendus de visite au format PDF
- 🔍 **Filtres et tri** : Filtrage par statut et tri chronologique des visites
- 🌐 **API REST** : Accès programmatique aux données (API Platform)

## 🛠️ Technologies utilisées

- **Backend** : Symfony 6.4 (PHP 8.1)
- **Base de données** : MySQL 8.0
- **ORM** : Doctrine
- **Template** : Twig
- **Frontend** : Bootstrap 5.3
- **API** : API Platform
- **PDF** : Dompdf
- **Conteneurisation** : Docker & Docker Compose
- **Serveur web** : Nginx

## 📦 Structure du projet

```
mon_projet_docker/
├── docker-compose.yml       # Configuration Docker
├── Dockerfile              # Image PHP-FPM avec Symfony CLI
├── nginx.conf              # Configuration Nginx
└── apptuteur/             # Application Symfony
    ├── config/            # Configuration Symfony
    ├── migrations/        # Migrations de base de données
    ├── public/            # Point d'entrée web
    ├── src/
    │   ├── Controller/    # Contrôleurs
    │   ├── Entity/        # Entités (Tuteur, Etudiant, Visite)
    │   ├── Form/          # Formulaires Symfony
    │   └── Repository/    # Repositories Doctrine
    ├── templates/         # Templates Twig
    └── vendor/            # Dépendances Composer
```

## 🚀 Installation et démarrage

### Prérequis

- Docker Desktop installé et démarré
- Git (pour cloner le projet)

### Étapes d'installation

1. **Cloner le repository**

   ```bash
   git clone https://github.com/lelex3529/L3_Miage_PW_Projet_Final.git
   cd L3_Miage_PW_Projet_Final
   ```

2. **Démarrer les conteneurs Docker**

   ```powershell
   cd mon_projet_docker
   docker-compose up -d
   ```

3. **Installer les dépendances Composer** (si nécessaire)

   ```powershell
   docker exec -it symfony_app composer install
   ```

4. **Appliquer les migrations de base de données**

   ```powershell
   docker exec -it symfony_app php bin/console doctrine:migrations:migrate
   ```

5. **Accéder à l'application**
   - **Application web** : http://localhost:8000
   - **phpMyAdmin** : http://localhost:8081
   - **API REST** : http://localhost:8000/api

### Arrêter les conteneurs

```powershell
docker-compose down
```

## 🗄️ Configuration de la base de données

Les conteneurs Docker créent automatiquement une base de données MySQL avec les paramètres suivants :

- **Host** : `db` (dans Docker) ou `localhost:3306` (depuis l'hôte)
- **Database** : `symfony_db`
- **User** : `user`
- **Password** : `password`
- **Root Password** : `root`

La connexion est configurée dans le fichier `.env` :

```
DATABASE_URL="mysql://user:password@db:3306/symfony_db?charset=utf8mb4"
```

## 👤 Utilisation

### Connexion

1. Accédez à http://localhost:8000/login
2. Entrez l'email d'un tuteur existant en base de données
3. Une fois connecté, vous accédez au dashboard

### Gestion des étudiants

- **Voir la liste** : Menu "Étudiants" ou Dashboard
- **Ajouter** : Bouton "Ajouter un étudiant"
- **Modifier/Supprimer** : Boutons dans le tableau
- **Voir les visites** : Bouton "Visites" pour chaque étudiant

### Gestion des visites

- **Créer une visite** : Depuis la page d'un étudiant
- **Filtrer** : Par statut (prévue, réalisée, annulée)
- **Trier** : Par date (croissant/décroissant)
- **Modifier** : Bouton "Modifier" sur chaque visite
- **Compte rendu** : Bouton "Compte rendu" pour saisir/modifier
- **Export PDF** : Bouton "PDF" pour télécharger le compte rendu

### API REST

L'API est accessible via API Platform à l'adresse http://localhost:8000/api

**Endpoints disponibles :**

- `GET /api/tuteurs` - Liste des tuteurs
- `GET /api/etudiants` - Liste des étudiants
- `GET /api/visites` - Liste des visites
- `POST /api/tuteurs` - Créer un tuteur
- `POST /api/etudiants` - Créer un étudiant
- `POST /api/visites` - Créer une visite
- `PUT /api/tuteurs/{id}` - Modifier un tuteur
- `DELETE /api/tuteurs/{id}` - Supprimer un tuteur
- ... (endpoints complets pour chaque entité)

## 📊 Modèle de données

### Entités

**Tuteur**

- Nom, Prénom
- Email (unique)
- Téléphone
- Relations : plusieurs étudiants, plusieurs visites

**Étudiant**

- Nom, Prénom
- Formation
- Email (optionnel)
- Relations : un tuteur, plusieurs visites

**Visite**

- Date (immutable)
- Commentaire
- Statut (prévue, réalisée, annulée)
- Compte rendu (optionnel)
- Relations : un tuteur, un étudiant

## 🧪 Commandes utiles

### Doctrine

```powershell
# Vérifier le statut des migrations
docker exec -it symfony_app php bin/console doctrine:migrations:status

# Créer une nouvelle migration
docker exec -it symfony_app php bin/console make:migration

# Exécuter les migrations
docker exec -it symfony_app php bin/console doctrine:migrations:migrate

# Réinitialiser la base de données (ATTENTION : supprime toutes les données)
docker exec -it symfony_app php bin/console doctrine:schema:drop --force
docker exec -it symfony_app php bin/console doctrine:migrations:migrate
```

### Cache

```powershell
# Vider le cache
docker exec -it symfony_app php bin/console cache:clear
```

### Logs

```powershell
# Voir les logs des conteneurs
docker-compose logs -f

# Logs d'un conteneur spécifique
docker-compose logs -f app
```

## 🐛 Dépannage

### Les conteneurs ne démarrent pas

- Vérifiez que Docker Desktop est bien démarré
- Vérifiez que les ports 8000, 8081 et 3306 ne sont pas déjà utilisés
- Essayez `docker-compose down` puis `docker-compose up -d`

### Erreur de connexion à la base de données

- Attendez quelques secondes que MySQL soit complètement démarré
- Vérifiez les logs : `docker-compose logs db`
- Vérifiez la configuration dans le fichier `.env`

### Les migrations échouent

- Vérifiez que la base de données est accessible
- Consultez les logs : `docker-compose logs app`
- Essayez de réinitialiser : `docker exec -it symfony_app php bin/console doctrine:schema:drop --force`

### Page blanche ou erreur 500

- Vérifiez les permissions du dossier `var/` : `docker exec -it symfony_app chmod -R 777 var/`
- Videz le cache : `docker exec -it symfony_app php bin/console cache:clear`
- Consultez les logs dans `apptuteur/var/log/`

## 📝 Améliorations futures possibles

- [ ] Système d'authentification avec mots de passe hashés
- [ ] Gestion des rôles (admin, tuteur)
- [ ] Notifications par email pour les visites à venir
- [ ] Calendrier interactif pour les visites
- [ ] Upload de documents (contrats, attestations)
- [ ] Statistiques avancées et graphiques
- [ ] Tests unitaires et fonctionnels
- [ ] Interface d'administration

## 📄 Licence

Projet académique - L3 MIAGE

## 🤝 Contribution

Projet réalisé dans le cadre d'un cours universitaire.

---

**Pour toute question, contactez les auteurs.**

# JobBoard

JobBoard est une application de recherche d'emploi. Les candidats peuvent parcourir les offres et gérer leurs candidatures. Les entreprises disposent d'un espace pour publier des offres et suivre les candidatures reçues. Une interface d'administration est également prévue.

## Technologies

- **Backend :** PHP 8.4+, Symfony 8.1, Doctrine ORM et API JSON avec authentification JWT
- **Base de données :** MariaDB
- **Frontend :** React 19, Vite et React Router

## Structure du projet

```text
JobBoard/
├── backend/    API Symfony, entités, migrations et authentification
├── frontend/   Interface React
└── README.md
```

## Prérequis

- PHP 8.4 ou une version ultérieure
- Composer
- Node.js et npm
- MariaDB
- Symfony CLI pour lancer le serveur Symfony avec les commandes ci-dessous

## Installation et lancement

### Backend

Dans un terminal, configurez la variable `DATABASE_URL` dans `backend/.env.local` pour qu'elle corresponde à votre instance MariaDB. Ne versionnez pas ce fichier ni vos identifiants.

```powershell
cd backend
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

Si les clés JWT ne sont pas encore présentes, générez-les :

```powershell
php bin/console lexik:jwt:generate-keypair
```

Lancez ensuite l'API :

```powershell
symfony server:start
```

### Frontend

Dans un autre terminal, à la racine du projet :

```powershell
cd frontend
npm ci
npm run dev
```

Vite affiche l'adresse locale à ouvrir dans votre navigateur. Pour vérifier le frontend ou produire les fichiers de déploiement :

```powershell
npm run lint
npm run build
```

## Principales routes de l'API

- `GET /annonce` et `GET /annonce/{id}` : consulter les offres
- `POST /api/register` et `POST /api/login` : créer un compte candidat et s'authentifier
- `/api/candidatures` : consulter et gérer ses candidatures (authentification requise)
- `/api/annonces/{id}/candidatures` : consulter les candidatures reçues par une entreprise (authentification et autorisation requises)

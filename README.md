# Vite & Gourmand

- **PHP** ≥ 8.4
- **MySQL** 8.0+
- **Symfony CLI** (optionnel, recommandé) ([symfony.com/download](https://symfony.com/download))

## Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/Zelimkhan-mu/vite-gourmand2.git
cd vite-gourmand2
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configurer l'environnement

Copier le fichier `.env` et créer un `.env.local` avec vos paramètres :

```bash
cp .env .env.local
```

Modifier `.env.local` avec vos identifiants :

```env
DATABASE_URL="mysql://VOTRE_USER:VOTRE_MDP@127.0.0.1:3306/vitegourmand?serverVersion=8.0&charset=utf8mb4"
MAILER_DSN="smtp://VOTRE_CONFIG_SMTP"
APP_SECRET="votre_secret_unique"
```

### 4. Créer la base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Importer les données initiales

Importer le fichier SQL fourni :

```bash
mysql -u VOTRE_USER -p vitegourmand < docs/vite_gourmand.sql
```

### 6. Lancer le serveur de développement

```bash
php -S localhost:8000 -t public/
```

L'application est accessible sur [http://localhost:8000](http://localhost:8000).

## Identifiants de test

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Admin | admin@viteetgourmand.fr | Admin123456! |

## Stack technique

| Composant | Technologie |
|-----------|-------------|
| Front-end | HTML5, Tailwind , JavaScript |
| Back-end | PHP 8.4, Symfony 8.0 |
| BDD relationnelle | MySQL 8.0 |
| BDD non relationnelle | MongoDB (à terminer...)|
| Envoi d'emails | Symfony Mailer (Mailtrap) |

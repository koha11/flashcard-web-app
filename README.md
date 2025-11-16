# G-Flashcard Web App

G-Flashcard is a web application that helps users save and review vocabulary, keywords, and short notes using flashcards.  
This repository contains the Laravel backend (and front-end if using Blade/Inertia/Vite).

---

## 1. Requirements

Make sure the following are installed on your machine:

-   PHP 8.1+ (recommended: 8.2+)
-   Composer
-   A database:
    -   MySQL / MariaDB (or another DB supported by Laravel)
-   Node.js 18+ and npm (for front-end assets with Vite)
-   Git (optional, but recommended)

To verify:

```bash
php -v
composer -V
node -v
npm -v
```

## 2. Clone the project

```bash
git clone <REPO_URL> G-flashcard
cd G-flashcard/flashcard-web-app
```

## 3. Install PHP dependencies

```bash
composer install
```

## 4. Environment configuration

-   Copy the example .env file:

```bash
cp .env.example .env
# On Windows PowerShell, you can use:
# copy .env.example .env
```

-   Generate the application key:

```bash
php artisan key:generate
```

-   Open the .env file and configure your database connection:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=g_flashcard
DB_USERNAME=root
DB_PASSWORD=your_password
```

## 5. Database migration and seeding

```bash
php artisan migrate --seed
```

## 6. Run the development server

```bash
php artisan serve
```

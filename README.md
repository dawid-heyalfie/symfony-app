# Symfony REST API for Managing Properties
## Project Description
This is a REST API for managing properties with authentication.
## Getting Started
1. Install [Docker Compose](https://docs.docker.com/compose/install/) (v2.10+), if not already installed.
2. Build fresh images:
 ```bash
 docker compose build --no-cache
 ```
3. Start the Symfony project:
 ```bash
 docker compose up --pull always -d --wait
 ```
4. Open `https://localhost` in your browser and accept the auto-generated TLS certificate.
5. To stop and remove the containers:
 ```bash
 docker compose down --remove-orphans
 ```
## Installation
### Prerequisites
- [Docker](https://www.docker.com/) and [Docker Compose](https://docs.docker.com/compose/)
- PHP, Composer, and Symfony CLI (if running locally without Docker)
### 1. Clone the Repository
 ```bash
 git clone <repository-url>
 cd <repository-folder>
 ```
### 2. Build and Start the Application
 ```bash
 docker compose build --no-cache
 docker compose up -d
 ```
The application will be available at [https://localhost](https://localhost).
### 3. Generate and Migrate Database
Run the following commands inside the **PHP container**:
 ```bash
 docker compose exec php bin/console doctrine:database:create
 docker compose exec php bin/console doctrine:migrations:migrate
 ```
### 4. Seed Database with Sample Data
 ```bash
 docker compose exec php bin/console doctrine:fixtures:load
 ```
## Running Tests
Run **Behat** tests:
```bash
vendor/bin/behat
```
Run **PHPUnit** tests:
```bash
vendor/bin/phpunit
```

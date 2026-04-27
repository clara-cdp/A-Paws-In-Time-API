<img max-width="1200" height="auto" alt="APIT_logico" src="https://github.com/user-attachments/assets/aaaf15d6-3864-4f90-8b51-f905e3609c3e" />

# 🐾 A Point-and-Click Adventure API 

A Paws in Time is a narrative-driven point-and-click adventure where players must navigate a world frozen 
in a temporal glitch to rescue their kidnapped feline companion. Players must infiltrate a mad scientist’s 
mansion to disrupt a machine designed to halt history forever. Shifting between the Past and Present, 
players interact with a dynamic 2D environment, collecting era-specific items and solving logic-based 
puzzles to reach the Doctor's attic where the cat is trapped, 
restarting the clock before the "Perfect Moment" becomes a permanent cage.

## 📚 Table of Contents

- [Technologies](#-technologies)
- [Getting Started](#-getting-started)
  - [Traditional Setup](#-option-1-traditional-setup)
  - [Docker Setup](#-option-2-docker-setup-recommended)
- [Deployment (Render)](#-deployment-render)
- [Automated Testing (Pest)](#-automated-testing-with-pest)
- [API Testing](#-manual-api-testing)


# 🛠 Technologies
- Framework: Laravel 12 (PHP 8.4+)   
- Authentication: Laravel Passport (OAuth2)   
- Authorization: Spatie Roles & Permissions   
- Testing: Pest Framework   
- Documentation: Scribe
- Deployment: Docker + Render + PostgreSQL

# 🚀 Getting Started
You can run the project in two ways:

🧩 Traditional (PHP + Composer)   
🐳 Docker (recommended)

### 🧩 Option 1: Traditional Setup

#### Prerequisites
* PHP 8.4+
* Composer
* SQLite

**1. Clone the repository**

```
git clone [https://github.com/clara-cdp/A-Paws-In-Time-API.git](https://github.com/clara-cdp/A-Paws-In-Time-API.git)
```
- Ensure you are in the right folder
```
cd A-PAWS-IN-TIME-API
```
**2. Install dependencies**
```
composer install
```

**3. set up enviroment**
- copy enviroment folder
```
cp .env.example .env
```
- edit the following
```properties
APP_NAME='A Paws in Time'
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
 DB_DATABASE=database/database.sqlite
 DB_USERNAME=root
 DB_PASSWORD=
```
**4. 🔑 Generate aap key:**
```
php artisan key:generate
```
**5.Run migrations and seed the world data**
```
php artisan migrate --seed
```

>When prompted:   
>The SQLite database configured for this application does not exist: database/database.sqlite.  
>Would you like to create it? (yes/no) [yes]  

**6. Initialize Passport **
- get the security keys
```
php artisan passport:keys
```
- set up a client: 
```
php artisan passport:client --personal
```
>just press enter for defaults (normally twice)  

> you will need to re-run this command each time you refresh the database

**Access:**

API → http://localhost:8000  
Docs → http://localhost:8000/docs  


### 🐳 Option 2: Docker Setup (Recommended)

Prerequisites  
⚠️ Docker Desktop installed and running  
**1. Clone repository**
```
git clone https://github.com/clara-cdp/A-Paws-In-Time-API.git
cd A-Paws-In-Time-API
```
**3. Set up environment**
```
cp .env.example .env
```
- Edit .env:
```
APP_NAME="A Paws in Time"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=/data/database.sqlite
SESSION_DRIVER=file
```
**3. Build and run containers**
```  
docker compose up --build
```
**4. Generate the application key**

Open a NEW terminal and run:
```
docker compose exec app php artisan key:generate
```
**5. Create the Passport personal access client**
```
docker compose exec app php artisan passport:client --personal
```
>When prompted, press 'Enter' for the default values.

**Access**
API → http://localhost:8000   
Docs → http://localhost:8000/docs  

### 😸 Useful Docker Commands

Stop containers
```
docker compose down
```
Remove containers + volumes (full reset)
```
docker compose down -v
```
Run migrations
```
docker compose exec app php artisan migrate
```
Seed database
```
docker compose exec app php artisan db:seed
```
Fresh reset
```
docker compose exec app php artisan migrate:fresh --seed
```
Regenerate Scribe docs
```
docker compose exec app php artisan scribe:generate
```

# ☁️ Deployment (Render)

This API is deployed using Render + Docker + PostgreSQL.

Production Stack   
Hosting: Render   
Runtime: Docker     
Database: PostgreSQL (Render managed)   
Environment Variables (Render)   

**🔍 How to Review the Deployment**

You can test the API directly from the browser using Scribe:

Open the documentation:  
https://a-paws-in-time-api.onrender.com/docs  

>⚠️ Note: The service runs on Render’s free tier.
>The first request may take 20-50 seconds due to cold starts.


# 🧪 Automated Testing with Pest

This project includes automated tests built with Pest and Laravel testing tools.

Run all tests locally
```
php artisan test
```
You can also run:
```
composer test
```
Run only one test file:
```
php artisan test tests/Feature/NameOfTest.php
```
Example:
```
php artisan test tests/Feature/GamePlayTest.php
```
<img width="1130" height="791" alt="Screenshot 2026-04-19 103413" src="https://github.com/user-attachments/assets/dffb0aa1-5c97-4695-9d1f-6702f1bb4e09" />
<img width="1104" height="651" alt="Screenshot 2026-04-19 103429" src="https://github.com/user-attachments/assets/1e9b1c24-e491-4e1f-a113-5ef0cbaaf892" />


# ✅ Manual API Testing

## ✍️ SCRIBE

The API documentation will be available at 
http://localhost:8000/docs

Use the [test credentials](#test-credentials) below.

### 1. run the localhost:
```
php artisan serve
```

### 2. set up a client (if you haven't done so):
```
php artisan passport:client --personal
```


## 👩‍🚀 POSTMAN

Scribe includes a collection to test the api in postman. 
- In the scribe documentation menu, under the enpoints click on the _**View Postman collection**_
- Open Postman  
- Create a new workspace  
- once inside the workspace, select import  
- paste de json created in **View Postman collection**  

<img width="300" height="auto" alt="import" src="https://github.com/user-attachments/assets/4942ed78-730e-4c30-a1e4-4d9ddcd87838" />

## TEST CREDENTIALS

| Role | Name | Email | Password |
| :--- | :--- | :--- | :--- |
| **Admin** | `Admin` | `adminino@apaws.com` | `Pawsword1!` |
| **User** | `Edgar Allan Paw` | `paw@apaws.com` | `Pawsword2!` |

## ENDPOINTS:
| Group | Method | Endpoint | Description | Auth |
| :--- | :--- | :--- | :--- | :---: |
| **Auth** | `POST` | `/api/auth/register` | Create a new adventurer account | Public |
| **Auth** | `POST` | `/api/auth/login` | Log in and receive Bearer Token | Public |
| **Auth** | `POST` | `/api/auth/logout` | Revoke current access token | 🔑 |
| **Profile** | `GET` | `/api/me` | View your personal profile data | 🔑 |
| **Profile** | `PUT` | `/api/me` | Update your account details | 🔑 |
| **Profile** | `DELETE` | `/api/me` | Permanently delete your account | 🔑 |
| **Game** | `GET` | `/api/games` | List all your saved game slots | 🔑 |
| **Game** | `POST` | `/api/games` | Start a new journey (Create save) | 🔑 |
| **Game** | `GET` | `/api/games/{game}` | Load a specific game save | 🔑 |
| **Game** | `PUT` | `/api/games/{game}` | Update game progress or avatar | 🔑 |
| **Game** | `DELETE` | `/api/games/{game}` | Delete a specific save slot | 🔑 |
| **Engine** | `POST` | `/api/games/{game}/actions` | **PLAY:** Interaction engine | 🔑 |
| **Admin** | `GET` | `/api/admin/users` | View all users (Paginated) | 🛡️ |
| **Admin** | `GET` | `/api/admin/users/{user}` | View specific user details | 🛡️ |
| **Admin** | `PUT` | `/api/admin/users/{user}` | Edit any user profile | 🛡️ |
| **Admin** | `PUT` | `/api/admin/users/{user}/block` | Toggle user active/blocked status | 🛡️ |
| **Admin** | `DELETE` | `/api/admin/users/{user}` | Force delete a user account | 🛡️ |
| **Admin** | `GET` | `/api/admin/metadata` | View world enums and constants | 🛡️ |


<img width="2565" height="1716" alt="POST_api-auth-login" src="https://github.com/user-attachments/assets/d194cef5-1749-48e5-968f-b98e13c3c98b" />
<img width="1122" height="918" alt="POST-api-games-6-actions-PLAY" src="https://github.com/user-attachments/assets/dbbbc9ad-d375-49a6-afe7-7f5b72fe129a" />


---
## Author
Clara Cerdà de Palou

## Acknowledgments
Barcelona Activa Fullstack PHP Bootcamp (2025/2026)



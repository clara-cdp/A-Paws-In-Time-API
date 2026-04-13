<img max-width="1200" height="auto" alt="APIT_logico" src="https://github.com/user-attachments/assets/aaaf15d6-3864-4f90-8b51-f905e3609c3e" />

# 🐾 A Point-and-Click Adventure API 

A Paws in Time is a narrative-driven point-and-click adventure where players must navigate a world frozen 
in a temporal glitch to rescue their kidnapped feline companion. Players must infiltrate a mad scientist’s 
mansion to disrupt a machine designed to halt history forever. Shifting between the Past and Present, 
players interact with a dynamic 2D environment, collecting era-specific items and solving logic-based 
puzzles to reach the Doctor's attic where the cat is trapped, 
restarting the clock before the "Perfect Moment" becomes a permanent cage.

# 🛠 Technologies
- Framework: Laravel 12 (PHP 8.4+)   
- Authentication: Laravel Passport (OAuth2)   
- Authorization: Spatie Roles & Permissions   
- Testing: Pest Framework   
- Documentation: Scribe

# 🚀 Getting Started
### Prerequisites
* PHP 8.4+
* Composer
* SQLite

## Installation & Setup

### 1. Clone the repository

```
git clone [https://github.com/clara-cdp/A-Paws-In-Time-API.git](https://github.com/clara-cdp/A-Paws-In-Time-API.git)
```
- Ensure you are in the right folder
```
cd A-PAWS-IN-TIME-API
```
### 2. Install dependencies
```
composer install
```

### 3. set up enviroment
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
### 4. 🔑 Generate aap key:
```
php artisan key:generate
```
### 5.Run migrations and seed the world data
```
php artisan migrate --seed
```
>if prompted:  
>WARN  The SQLite database configured for this application does not exist: database/database.sqlite.  
>Would you like to create it? (yes/no) [yes]  

6. Initialize Passport 
- get the security keys
```
php artisan passport:keys
```
- set up a client: 
```
php artisan passport:client --personal
```
when prompted:  
just press enter (normally twice)  

> you will need to re-run this command each time you refresh the database

## View documentation:
The API documentation will be available at 
http://localhost:8000/docs

### 1. run the localhost:
```
php artisan serve
```

### 2. set up a client (if you haven't done so):
```
php artisan passport:client --personal
```
> when prompted: just press enter (normally twice)

# POSTMAN TESTING:

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





# GovConnect — Voice for the People

A web platform where citizens of Bangladesh can report everyday public problems (crime, fire, medical emergencies, government services) and track them until they are resolved. Admins verify each report and assign it to the right response team, and teams update progress from their own dashboard.

![Home page](docs/screenshots/home.png)

## Features

- **Three roles:** citizen, admin and response team, each with its own dashboard
- **Problem reporting** with category, description, suggestion, photo upload and map location (auto-detected or picked)
- **Emergency SOS button** that sends the user's current location to the police category instantly
- **Admin workflow:** verify or reject reports, set priority (low / medium / high) and assign a response team
- **Response teams** register and wait for admin approval, then see assigned problems sorted by priority and mark them as working or resolved
- **Feedback and rating** from citizens after a problem is handled
- **Profiles** with profile pictures, location and password change
- Passwords stored as bcrypt hashes and all queries use PDO prepared statements

## Screenshots

| User dashboard | Admin dashboard | Response team dashboard |
|---|---|---|
| ![User](docs/screenshots/user-dashboard.png) | ![Admin](docs/screenshots/admin-dashboard.png) | ![Response](docs/screenshots/response-dashboard.png) |

## Tech stack

PHP (PDO) · MySQL · HTML/CSS · Bootstrap · JavaScript · OpenStreetMap Nominatim (reverse geocoding)

## Run it locally (XAMPP)

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL**.
2. Copy this folder into `C:\xampp\htdocs\` and make sure the folder is named **`govconnect`** (the links in the code use `/govconnect/...`).
3. Open `http://localhost/phpmyadmin`, go to **Import**, and import `database/schema.sql`.
4. Copy `config.example.php` to `config.php` and set your MySQL password (XAMPP's default is empty).
5. Visit `http://localhost/govconnect/`.

To create an admin account, see the comment at the bottom of `database/schema.sql`.

## Project structure

```
govconnect/
├── index.php                 # Landing page
├── login.php / register.php  # Auth pages for all three roles
├── user_dashboard.php        # Citizen dashboard + problem list
├── submit_problem.php        # Report form (with SOS)
├── admin_dashboard.php       # Verify, prioritise, assign
├── response_dashboard.php    # Team view of assigned problems
├── *_process.php / *_controller.php   # Form handlers
├── db_connect.php            # PDO connection (reads config.php)
├── database/schema.sql       # Database tables
└── docs/screenshots/
```

## Team

- Md Injabin Alam
- Md. Al Shahariyar
- Manisha Choudhury
- Binita Gope

## License

MIT — see [LICENSE](LICENSE).

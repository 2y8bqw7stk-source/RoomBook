# 🗓️ RoomBook · ESISA

RoomBook is a modern meeting room reservation management solution designed for ESISA.  
It allows real-time monitoring of space occupancy, prevents scheduling conflicts, and simplifies internal organization through a smooth and reactive interface.

## ✨ Features

- **Dynamic Dashboard**: At-a-glance visualization of the number of rooms, confirmed reservations, pending requests, and active employees.

- **Interactive Planning**: A detailed hourly grid (08:00 - 18:00) displaying occupancy by room.

- **Quick Reservation (QuickBook)**: Click on an empty slot in the schedule to open a pre-filled form with the selected time and room.

- **Anti-Conflict Control**: The PHP backend automatically checks room availability before any insertion to prevent double bookings.

- **Advanced Filters**: Instant sorting by date or specific room for better visibility.

- **"Glassmorphism" Design**: Elegant dark interface with transparency effects and smooth animations.

## 🛠️ Technical Stack

- **Frontend**: HTML5, CSS3 (Variables, Flexbox, Grid), JavaScript (ES6+, Fetch API)
- **Backend**: PHP 8.x with a RESTful API architecture
- **Database**: MySQL (PDO for query security)
- **Design**: Syne and DM Sans fonts for professional typography

## 📂 Project Structure

```
├── index.html        (User interface (SPA) and JavaScript logic)
├── api.php           (Backend API (GET, POST, PUT, DELETE))
└── database.sql      (SQL schema and initialization data)
```

## 🚀 Installation & Configuration

### 1. Database
Import the `database.sql` file into your MySQL manager (e.g. phpMyAdmin).  
This will create the **roombook** database and the following tables:

- **salles**: List of available spaces
- **employes**: Staff directory (IT, HR, Marketing, etc.)
- **reservations**: Log of reserved time slots

### 2. Backend
Check the connection configuration in `api.php`. By default:

- Host: `localhost`
- DB Name: `roombook`
- User: `root`

### 3. Launch
Place the project folder in your local server (e.g. `htdocs` for XAMPP), then navigate to:

```
http://localhost/your-folder/
```

## 📡 API Endpoints

| Method | Action | Description |
|--------|--------|-------------|
| GET | `salles` | Retrieves the list of rooms with their capacities |
| GET | `employes` | Retrieves the list of employees (search available with `?q=`) |
| GET | `reservations` | Retrieves reservation schedules (filters: `date`, `salle_id`) |
| POST | `reservation` | Creates a new reservation after conflict verification |
| PUT | `reservation` | Updates an existing reservation by its `id` |
| DELETE | `reservation` | Deletes a reservation by its `id` |

## 👥 Team

| Name | Role |
|------|------|
| Ali Alami Marktani | Developer |
| Noha Mahfoudi | Developer |
| Youness Dahmani | Developer |
| Anis Helheit | Developer |
| Abir Badaoui | Developer |
| Salma Fennane | Supervisor |

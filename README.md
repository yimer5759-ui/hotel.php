# 🏨 Hotel Booking System

A full-stack Hotel Booking System built with **PHP**, **MySQL**, **HTML**, **CSS**, and **JavaScript**. The application allows customers to browse hotel rooms, make reservations, and manage bookings, while administrators can manage rooms, bookings, and users through an organized MVC architecture.

---

## 🚀 Features

- 🔐 User Authentication (Login & Registration)
- 🏨 Browse Available Hotel Rooms
- 📅 Online Room Booking
- 🛏️ Room Details and Availability
- 👤 User Profile Management
- ❌ Booking Cancellation
- 🛠️ Admin Dashboard
- 📊 Booking Management
- 🏠 Room Management (Add, Edit, Delete)
- 👥 User Management
- 📱 Responsive Design

---

## 🛠️ Technologies Used

### Frontend
- HTML5
- CSS3
- JavaScript
- Bootstrap

### Backend
- PHP
- MVC Architecture

### Database
- MySQL

### Server
- Apache (XAMPP/Laragon/WAMP)

### Version Control
- Git
- GitHub

---

## 📂 Project Structure

```text
hotel.php/
│
├── assets/           # CSS, JavaScript, Images
├── config/           # Configuration files
├── controllers/      # Controller classes
├── database/         # Database scripts
├── includes/         # Common PHP files
├── models/           # Database models
├── views/            # UI pages
├── .htaccess         # URL Rewrite
└── index.php         # Application entry point
```

---

## ⚙️ Installation

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/hotel.php.git
cd hotel.php
```

### 2. Move Project

Copy the project folder into your web server directory.

Example (XAMPP):

```
htdocs/hotel.php
```

### 3. Create Database

Create a MySQL database named:

```
hotel_booking
```

Import the SQL file from the `database/` folder.

### 4. Configure Database

Edit the configuration file inside:

```
config/
```

Update your database credentials.

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "hotel_booking";
```

### 5. Start Server

Start:

- Apache
- MySQL

Open your browser:

```
http://localhost/hotel.php
```

---

## 👤 User Features

- Register an account
- Login securely
- Browse hotel rooms
- View room details
- Book a room
- View booking history
- Cancel bookings
- Update profile

---

## 👨‍💼 Admin Features

- Login as administrator
- Manage hotel rooms
- Add new rooms
- Update room information
- Delete rooms
- View all bookings
- Manage users
- Manage reservations

---

## 🔒 Security

- Password Authentication
- Input Validation
- SQL Injection Protection
- Session Management
- Secure Routing using `.htaccess`

---

## 📸 Screenshots

Add screenshots here after deployment.

Example:

- Home Page
- Login Page
- Booking Page
- Dashboard
- Admin Panel

---

## 🚀 Future Improvements

- Online Payment Integration
- Email Notifications
- Room Reviews
- Image Gallery
- Search & Filters
- Multi-language Support
- Booking Reports
- QR Code Reservation
- Mobile-Friendly Dashboard

---

## 🤝 Contributing

Contributions are welcome.

1. Fork the repository
2. Create a feature branch

```bash
git checkout -b feature-name
```

3. Commit your changes

```bash
git commit -m "Add new feature"
```

4. Push to GitHub

```bash
git push origin feature-name
```

5. Create a Pull Request

---

## 📄 License

This project is licensed under the MIT License.

---

## 👨‍💻 Author

**Yimer**

Hotel Booking System Project

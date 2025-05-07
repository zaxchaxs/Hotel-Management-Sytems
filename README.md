# Hotel Management System

A complete web-based hotel management system built with PHP, MySQL, Express.js API, and Tailwind CSS.

## Features

- **Room Management**
  - Display available rooms with filtering options
  - Room details with images and amenities
  - Admin panel for adding, editing, and deleting rooms

- **Booking System**
  - Room availability checking
  - Booking process with date selection
  - Dynamic price calculation

- **User Management**
  - User registration and authentication
  - User profiles with booking history
  - Role-based access control (admin, staff, customer)

- **Payment Processing**
  - Simulated payment system
  - Multiple payment methods
  - Payment confirmation and receipt

- **Admin Dashboard**
  - Overview of bookings and occupancy
  - Revenue reports
  - User management

## Tech Stack

- **Frontend**: HTML, Tailwind CSS, JavaScript
- **Database**: MySQL
- **Authentication**: Custom PHP authentication system

## Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Node.js 18.x or higher
- npm 6.x or higher
- Web server (Apache/Nginx)

### Database Setup

1. Create a new MySQL database:
   ```sql
   CREATE DATABASE hotel_management;
   ```

### PHP Application Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/zaxchaxs/Hotel-Management-Sytems.git
   cd hotel-management-system
   ```

2. Configure database connection:
   - Copy `includes/config.sample.php` to `includes/config.php`
   - Edit `config.php` with your database credentials

3. Set up your web server to point to the project directory

### API Setup

1. Navigate to the API directory:
   ```bash
   cd api
   ```

2. Install dependencies:
   ```bash
   npm install
   ```

3. Configure environment variables:
   - Copy `.env.sample` to `.env`
   - Edit `.env` with your database credentials and other settings

4. Start the API server:
   ```bash
   npm start
   ```

## Database Schema

### Users Table
```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'staff', 'customer') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Rooms Table
```sql
CREATE TABLE rooms (
    room_id INT PRIMARY KEY AUTO_INCREMENT,
    room_number VARCHAR(10) UNIQUE NOT NULL,
    room_type VARCHAR(50) NOT NULL,
    capacity INT NOT NULL,
    price_per_night DECIMAL(10, 2) NOT NULL,
    description TEXT,
    amenities TEXT,
    image_url VARCHAR(255),
    status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available'
);
```

### Bookings Table
```sql
CREATE TABLE bookings (
    booking_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    adults INT NOT NULL DEFAULT 1,
    children INT NOT NULL DEFAULT 0,
    total_price DECIMAL(10, 2) NOT NULL,
    booking_status ENUM('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (room_id) REFERENCES rooms(room_id)
);
```

### Payments Table
```sql
CREATE TABLE payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100),
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id)
);
```

## User Roles

### Admin
- Full access to all features
- Manage rooms, bookings, and users
- Access to reports and statistics

### Staff
- View and manage bookings
- Check-in and check-out guests
- Limited room management

### Customer
- Browse available rooms
- Make and manage own bookings
- Update personal profile

## Security Features

- Password hashing with PHP's `password_hash()`
- Protection against SQL injection using prepared statements
- CSRF protection for forms
- Input validation on both client and server sides
- Role-based access control

## Future Enhancements

- Implement real payment gateway integration
- Add email notifications for bookings
- Develop a mobile app version
- Implement a more advanced reporting system
- Add multi-language support

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Credits

Developed by Irzi, Satria

## Support

For support, email [irzirahmatullah@example.com] or open an issue in the GitHub repository.
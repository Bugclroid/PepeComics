# PepeComics E-commerce Platform

PepeComics is a dynamic e-commerce platform dedicated to selling comics, featuring user authentication, a comprehensive product catalog, shopping cart functionality, and engaging Pepe-themed animations to enhance user interaction.

## Features

- User Authentication (Login/Register)
- Product Catalog with Search and Filtering
- Shopping Cart Management
- Secure Checkout Process
- Order Tracking
- User Reviews
- Responsive Design
- Pepe-themed Animations

## Technology Stack

- Frontend: HTML5, CSS3, JavaScript (ES6)
- Backend: PHP 8.x
- Database: MySQL 8.x
- Development Environment: XAMPP
- Database Management: phpMyAdmin

## Prerequisites

- XAMPP (with PHP 8.x and MySQL 8.x)
- Web Browser (Chrome, Firefox, Safari, or Edge)
- Git (optional, for version control)

## Installation

1. Clone the repository or download the source code:
   ```bash
   git clone https://github.com/yourusername/pepecomics.git
   ```

2. Move the project to your XAMPP htdocs directory:
   ```bash
   mv pepecomics C:/xampp/htdocs/
   ```

3. Start XAMPP and ensure Apache and MySQL services are running.

4. Create the database:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named "PepeComics"
   - Import the database schema from `databaseschema` file
   - Import sample data from `seed.sql` (optional)

5. Configure database connection:
   - Open `php/db.php`
   - Update database credentials if necessary

6. Access the website:
   ```
   http://localhost/pepecomics
   ```

## Project Structure

```
pepecomics/
├── css/                      # Stylesheets
│   ├── main.css              # Global styles
│   ├── animations.css        # Custom animations
│   └── components/           # Component styles
├── js/                       # JavaScript files
│   ├── main.js              # Main JS logic
│   ├── animations.js        # Animation handlers
│   ├── cart.js             # Cart functionality
│   └── utils.js            # Utility functions
├── images/                   # Image assets
├── php/                      # Backend logic
│   ├── controllers/         # Request handlers
│   ├── models/             # Database operations
│   └── views/              # HTML templates
├── index.php                # Entry point
└── README.md                # Documentation
```

## Usage

### User Registration
1. Click on "Login/Register" in the navigation bar
2. Fill out the registration form
3. Submit the form to create your account

### Browsing Comics
1. Use the search bar to find specific comics
2. Apply filters to narrow down results
3. Click on a comic to view details

### Shopping Cart
1. Add comics to your cart
2. Adjust quantities as needed
3. Proceed to checkout when ready

### Checkout Process
1. Review your cart
2. Enter shipping information
3. Select payment method
4. Confirm your order

### Order Tracking
1. Log in to your account
2. View your order history
3. Track the status of your orders

## Security Features

- Password Hashing
- Input Sanitization
- Prepared Statements
- CSRF Protection
- Secure Session Management

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## Testing

1. Import sample data using `seed.sql`
2. Test user authentication with sample accounts:
   - Regular User: john@example.com (password: password123)
   - Admin User: jane@example.com (password: admin123)
3. Test all features and functionality
4. Report any issues through the issue tracker

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- XAMPP development team
- PHP development team
- MySQL development team
- All contributors and testers

## Support

For support, please email support@pepecomics.com or create an issue in the repository. 
# PepeComics Admin Panel

## Overview

The PepeComics Admin Panel is a comprehensive management interface for the PepeComics e-commerce platform. It provides administrators with tools to manage comics, orders, users, and categories efficiently.

## Features

### Dashboard
- Overview of key metrics
- Recent orders
- Low stock alerts
- Monthly revenue chart
- Order statistics

### Comics Management
- List all comics
- Add new comics
- Edit existing comics
- Delete comics
- Bulk actions (delete, update stock)
- Filter and sort comics
- Category assignment

### Order Management
- View all orders
- Order details
- Update order status
- Print order details
- Filter and sort orders
- View order statistics

### User Management
- List all users
- View user details
- Manage user roles
- Delete users
- View user statistics
- Filter and sort users

### Category Management
- List all categories
- Add new categories
- Edit categories
- Delete categories
- View comics in each category

## Directory Structure

```
admin/
├── css/
│   ├── admin.css              # Admin panel styles
│   └── components/            # Reusable CSS components
├── js/
│   └── admin.js              # Admin panel JavaScript
├── php/
│   ├── controllers/          # Admin controllers
│   ├── models/              # Admin models (if needed)
│   ├── views/               # Admin view files
│   └── helpers/             # Admin helper functions
├── index.php                # Admin panel entry point
└── README.md               # This documentation
```

## Setup

1. Ensure you have the required permissions to access the admin panel
2. Access the admin panel at `/admin/index.php`
3. Log in with your admin credentials

## Usage

### Comics Management

1. **Adding a Comic:**
   - Click "Add Comic" button
   - Fill in the required information
   - Upload a comic image
   - Select categories
   - Click "Save"

2. **Editing a Comic:**
   - Click the edit icon next to the comic
   - Modify the information
   - Click "Update"

3. **Deleting Comics:**
   - Individual: Click the delete icon
   - Bulk: Select multiple comics and use bulk actions

### Order Management

1. **Viewing Orders:**
   - Access the Orders page
   - Use filters to find specific orders
   - Click on an order to view details

2. **Updating Order Status:**
   - Open order details
   - Select new status
   - Click "Update Status"

3. **Printing Orders:**
   - Open order details
   - Click "Print Order"

### User Management

1. **Managing Users:**
   - View user list
   - Click on a user to view details
   - Toggle admin status
   - Delete users if necessary

2. **Viewing User Statistics:**
   - Access user details
   - View order history
   - Check total spent
   - Monitor user activity

### Category Management

1. **Adding Categories:**
   - Click "Add Category"
   - Enter category name
   - Click "Save"

2. **Managing Categories:**
   - Edit category names
   - Delete unused categories
   - View comics in each category

## Security

- Admin access is restricted to users with admin privileges
- Session management ensures secure access
- Input validation prevents malicious data
- CSRF protection implemented
- Secure password handling

## Best Practices

1. **Regular Maintenance:**
   - Monitor low stock alerts
   - Process orders promptly
   - Keep comic information updated
   - Review user activities

2. **Order Processing:**
   - Check order details carefully
   - Update order status promptly
   - Communicate with customers when needed
   - Keep shipping information accurate

3. **User Management:**
   - Grant admin access carefully
   - Monitor user activities
   - Address user issues promptly
   - Maintain user privacy

4. **Content Management:**
   - Keep comic information accurate
   - Update stock levels regularly
   - Maintain organized categories
   - Archive old or unavailable comics

## Troubleshooting

### Common Issues

1. **Access Denied:**
   - Verify admin credentials
   - Check session status
   - Ensure proper permissions

2. **Image Upload Fails:**
   - Check file size
   - Verify file format
   - Ensure directory permissions

3. **Order Processing Issues:**
   - Verify payment status
   - Check shipping information
   - Ensure stock availability

4. **User Management Issues:**
   - Verify user data
   - Check role assignments
   - Ensure proper access levels

### Support

For technical support or questions:
1. Check the documentation
2. Contact the development team
3. Report bugs through the issue tracker

## Updates and Maintenance

- Regular updates will be provided
- Security patches will be applied
- New features will be added
- Performance improvements will be made

## Contributing

1. Follow coding standards
2. Test changes thoroughly
3. Document new features
4. Submit pull requests

## License

This admin panel is part of the PepeComics project and is subject to the same license terms. 
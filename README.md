# Task API with User Auth and Activity Logs

A PHP-based REST API for Task Management with user authentication, role-based access control, and activity tracking.

## Features

- User authentication with JWT tokens
- CRUD operations for tasks
- Role-based access control (admin and user roles)
- Activity logging for all task actions
- Secure password hashing
- RESTful API design

## Setup Instructions

1. Create a MySQL database named `task_api`
2. Update database configuration in `config/database.php` if needed
3. Run the setup script to create tables: `php config/setup.php`
4. Configure your web server to point to the project root

## API Endpoints

### Authentication

- `POST /api/register` - Register a new user
- `POST /api/login` - Login and get JWT token

### Tasks (Protected)

- `GET /api/tasks` - Get all tasks (admin: all tasks, user: own tasks)
- `POST /api/tasks` - Create a new task
- `GET /api/tasks/{id}` - Get a specific task
- `PUT /api/tasks/{id}` - Update a specific task
- `DELETE /api/tasks/{id}` - Soft delete a task

### Activity Logs (Protected)

- `GET /api/activity-logs` - Get activity logs (admin: all logs, user: own logs)

## Default Admin User

- Username: admin
- Password: admin123

## Authentication

All protected endpoints require Bearer token authentication:

```
Authorization: Bearer {jwt_token}
```

## Sample Requests

### Register User

```
POST /api/register
Content-Type: application/json

{
  "username": "johndoe",
  "email": "john@example.com",
  "password": "password123"
}
```

### Login

```
POST /api/login
Content-Type: application/json

{
  "username": "johndoe",
  "password": "password123"
}
```

### Create Task

```
POST /api/tasks
Content-Type: application/json
Authorization: Bearer {jwt_token}

{
  "title": "Complete project",
  "description": "Finish the task API project",
  "status": "pending"
}
```

# API Documentation

## Base URL

All API endpoints are relative to the base URL of your API:

```
https://your-domain.com/api
```

## Authentication

### Register User

Register a new user account.

- **URL**: `/register`
- **Method**: `POST`
- **Auth required**: No

**Request Body**:

```json
{
  "username": "johndoe",
  "email": "john@example.com",
  "password": "password123"
}
```

**Success Response**:

- **Code**: 201 Created
- **Content**:

```json
{
  "message": "User registered successfully"
}
```

**Error Responses**:

- **Code**: 400 Bad Request
- **Content**:

```json
{
  "error": "Username already exists"
}
```

Or

```json
{
  "error": "Email already exists"
}
```

Or

```json
{
  "error": "Username, email and password are required"
}
```

### Login

Authenticate a user and get a JWT token.

- **URL**: `/login`
- **Method**: `POST`
- **Auth required**: No

**Request Body**:

```json
{
  "username": "johndoe",
  "password": "password123"
}
```

**Success Response**:

- **Code**: 200 OK
- **Content**:

```json
{
  "message": "Login successful",
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 1,
    "username": "johndoe",
    "email": "john@example.com",
    "role": "user"
  }
}
```

**Error Response**:

- **Code**: 401 Unauthorized
- **Content**:

```json
{
  "error": "Invalid credentials"
}
```

## Tasks

All task endpoints require authentication with a valid JWT token.

### Get All Tasks

Get all tasks for the authenticated user (or all tasks for admin).

- **URL**: `/tasks`
- **Method**: `GET`
- **Auth required**: Yes (Bearer Token)

**Success Response**:

- **Code**: 200 OK
- **Content**:

```json
{
  "tasks": [
    {
      "id": 1,
      "title": "Complete project",
      "description": "Finish the task API project",
      "status": "pending",
      "user_id": 1,
      "created_at": "2025-06-01 14:30:00",
      "updated_at": "2025-06-01 14:30:00",
      "is_deleted": 0
    },
    ...
  ]
}
```

### Get Single Task

Get a specific task by ID.

- **URL**: `/tasks/{id}`
- **Method**: `GET`
- **Auth required**: Yes (Bearer Token)

**Success Response**:

- **Code**: 200 OK
- **Content**:

```json
{
  "task": {
    "id": 1,
    "title": "Complete project",
    "description": "Finish the task API project",
    "status": "pending",
    "user_id": 1,
    "created_at": "2025-06-01 14:30:00",
    "updated_at": "2025-06-01 14:30:00",
    "is_deleted": 0
  }
}
```

**Error Responses**:

- **Code**: 404 Not Found
- **Content**:

```json
{
  "error": "Task not found"
}
```

Or

- **Code**: 403 Forbidden
- **Content**:

```json
{
  "error": "Access denied"
}
```

### Create Task

Create a new task.

- **URL**: `/tasks`
- **Method**: `POST`
- **Auth required**: Yes (Bearer Token)

**Request Body**:

```json
{
  "title": "New task",
  "description": "Description of the task",
  "status": "pending"
}
```

**Success Response**:

- **Code**: 201 Created
- **Content**:

```json
{
  "message": "Task created successfully",
  "task": {
    "id": 3,
    "title": "New task",
    "description": "Description of the task",
    "status": "pending",
    "user_id": 1,
    "created_at": "2025-06-01 15:45:00",
    "updated_at": "2025-06-01 15:45:00",
    "is_deleted": 0
  }
}
```

### Update Task

Update an existing task.

- **URL**: `/tasks/{id}`
- **Method**: `PUT`
- **Auth required**: Yes (Bearer Token)

**Request Body**:

```json
{
  "title": "Updated task title",
  "status": "in_progress"
}
```

**Success Response**:

- **Code**: 200 OK
- **Content**:

```json
{
  "message": "Task updated successfully",
  "task": {
    "id": 1,
    "title": "Updated task title",
    "description": "Finish the task API project",
    "status": "in_progress",
    "user_id": 1,
    "created_at": "2025-06-01 14:30:00",
    "updated_at": "2025-06-01 16:10:00",
    "is_deleted": 0
  }
}
```

**Error Responses**:

- **Code**: 404 Not Found
- **Content**:

```json
{
  "error": "Task not found"
}
```

Or

- **Code**: 403 Forbidden
- **Content**:

```json
{
  "error": "Access denied"
}
```

### Delete Task

Soft delete a task.

- **URL**: `/tasks/{id}`
- **Method**: `DELETE`
- **Auth required**: Yes (Bearer Token)

**Success Response**:

- **Code**: 200 OK
- **Content**:

```json
{
  "message": "Task deleted successfully"
}
```

**Error Responses**:

- **Code**: 404 Not Found
- **Content**:

```json
{
  "error": "Task not found"
}
```

Or

- **Code**: 403 Forbidden
- **Content**:

```json
{
  "error": "Access denied"
}
```

## Activity Logs

### Get Activity Logs

Get activity logs for the authenticated user (or all logs for admin).

- **URL**: `/activity-logs`
- **Method**: `GET`
- **Auth required**: Yes (Bearer Token)

**Success Response**:

- **Code**: 200 OK
- **Content**:

```json
{
  "activity_logs": [
    {
      "id": 1,
      "user_id": 1,
      "task_id": 3,
      "action": "create",
      "timestamp": "2025-06-01 15:45:00",
      "username": "johndoe",
      "task_title": "New task"
    },
    {
      "id": 2,
      "user_id": 1,
      "task_id": 1,
      "action": "update",
      "timestamp": "2025-06-01 16:10:00",
      "username": "johndoe",
      "task_title": "Updated task title"
    },
    ...
  ]
}
```
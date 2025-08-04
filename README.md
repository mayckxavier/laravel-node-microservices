# Laravel-Node Microservices

This project consists of a Laravel application and a Node.js microservice that communicate with each other. The Laravel application provides a RESTful API for user management and also acts as a gateway to the Node.js microservice.

## Project Structure

- `laravel-app/`: Laravel application with RESTful API
- `node-app/`: Node.js microservice
- `docker-compose.yml`: Docker configuration for running the entire stack

## Requirements

- Docker and Docker Compose
- Git

## Setup and Installation

### 1. Clone the Repository

```bash
git clone https://github.com/mayckxavier/laravel-node-microservices.git
cd laravel-node-microservices
```

### 2. Environment Configuration

The project comes with pre-configured environment variables in the Docker Compose file. However, you can modify them if needed:

```bash
# Edit the .env file in the laravel-app directory if needed
nano laravel-app/.env
```

Key environment variables:
- `APP_KEY`: Laravel application key
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: Database configuration
- `EXTERNAL_SERVICE_BASE_URL`: URL for the Node.js microservice (default: http://node-app:3000)

### 3. Start the Docker Containers

```bash
docker-compose up -d
```

This will start:
- Laravel application (accessible at http://localhost:8000)
- Node.js microservice (accessible at http://localhost:3000)
- MySQL database

### 4. Run Migrations

To set up the database tables:

```bash
docker-compose exec laravel-app php artisan migrate
```

Alternatively, you can run migrations with seed data:

```bash
docker-compose exec laravel-app php artisan migrate --seed
```

## API Documentation

### Health Check

- **GET /api/health**
  - Description: Check if the API is running
  - Response: `ok` (200 OK)

### User Management

#### Get All Users

- **GET /api/users**
  - Description: Retrieve all users
  - Response: JSON array of user objects
  - Example Response:
    ```json
    {
      "data": [
        {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com"
        },
        {
          "id": 2,
          "name": "Jane Smith",
          "email": "jane@example.com"
        }
      ]
    }
    ```

#### Get User by ID

- **GET /api/users/{id}**
  - Description: Retrieve a specific user by ID
  - Parameters:
    - `id`: User ID (integer)
  - Response: JSON user object
  - Example Response:
    ```json
    {
      "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      }
    }
    ```
  - Error Response (404 Not Found):
    ```json
    {
      "message": "No query results for model [App\\Models\\User] 1"
    }
    ```

#### Register User

- **POST /api/users**
  - Description: Create a new user
  - Request Body:
    ```json
    {
      "name": "New User",
      "email": "newuser@example.com",
      "password": "password123",
      "password_confirmation": "password123"
    }
    ```
  - Response: JSON user object (201 Created)
  - Example Response:
    ```json
    {
      "data": {
        "id": 3,
        "name": "New User",
        "email": "newuser@example.com"
      }
    }
    ```
  - Validation Errors (422 Unprocessable Entity):
    ```json
    {
      "errors": {
        "name": ["The name field is required."],
        "email": ["The email field is required."],
        "password": ["The password field is required."]
      }
    }
    ```

#### Update User

- **PUT /api/users/{id}**
  - Description: Update an existing user
  - Parameters:
    - `id`: User ID (integer)
  - Request Body (all fields are optional):
    ```json
    {
      "name": "Updated Name",
      "email": "updated@example.com",
      "password": "newpassword123",
      "password_confirmation": "newpassword123"
    }
    ```
  - Response: JSON user object
  - Example Response:
    ```json
    {
      "data": {
        "id": 1,
        "name": "Updated Name",
        "email": "updated@example.com"
      }
    }
    ```
  - Error Response (404 Not Found):
    ```json
    {
      "message": "No query results for model [App\\Models\\User] 1"
    }
    ```
  - Validation Errors (422 Unprocessable Entity):
    ```json
    {
      "errors": {
        "email": ["The email must be a valid email address."],
        "password": ["The password must be at least 8 characters."]
      }
    }
    ```

#### Delete User

- **DELETE /api/users/{id}**
  - Description: Delete a user
  - Parameters:
    - `id`: User ID (integer)
  - Response: Empty response (204 No Content)
  - Error Response (404 Not Found):
    ```json
    {
      "message": "No query results for model [App\\Models\\User] 1"
    }
    ```

### External Service

- **GET /api/external**
  - Description: Fetch data from the Node.js microservice
  - Response: JSON data from the Node.js microservice
  - Example Response:
    ```json
    {
      "data": {
        "message": "Hello World!"
      }
    }
    ```
  - Error Response (503 Service Unavailable):
    ```json
    {
      "error": "Failed to connect to the microservice",
      "message": "Could not resolve host: node-app"
    }
    ```

## Testing

The project includes comprehensive tests for all API endpoints. To run the tests:

```bash
docker-compose exec laravel-app php artisan test
```

## Troubleshooting

### Connection Issues Between Services

If the Laravel app cannot connect to the Node.js microservice, check:

1. Both containers are running:
   ```bash
   docker-compose ps
   ```

2. The Node.js app is accessible from the Laravel container:
   ```bash
   docker-compose exec laravel-app curl http://node-app:3000
   ```

3. The `EXTERNAL_SERVICE_BASE_URL` environment variable is set correctly in the Laravel app.

### Database Connection Issues

If the Laravel app cannot connect to the database, check:

1. The MySQL container is running:
   ```bash
   docker-compose ps
   ```

2. The database credentials in the Laravel `.env` file match those in the Docker Compose file.

3. The database has been created:
   ```bash
   docker-compose exec mysql mysql -u root -p -e "SHOW DATABASES;"
   ```
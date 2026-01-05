# QuilhaSoft/JasperPHP-OpenServer

This project provides a web interface for managing and executing JasperReports, integrated with a Laravel backend and a Vue.js frontend. It allows you to upload JasperReports (`.jrxml`) files, manage data sources, and execute reports in various formats.

## Features

*   **Report Management**: Upload, view, and manage JasperReports (`.jrxml`) files.
*   **Data Source Management**: Configure and manage various database connections (MySQL, PostgreSQL, SQLite) and JSON/Array data sources.
*   **Report Execution**: Execute reports with selected data sources and parameters, generating output in formats like PDF, XLS, XLSX, DOCX, and TXT.
*   **User Authentication**: Secure access to the application.

## Prerequisites

Before you begin, ensure you have the following installed on your system:

*   [**Docker**](https://docs.docker.com/get-docker/): Used for containerizing the application.
*   [**Docker Compose**](https://docs.docker.com/compose/install/): Used for defining and running multi-container Docker applications.

## Installation

Follow these steps to get the project up and running using Docker:

1.  **Clone the Repository:**
    ```bash
    git clone <repository_url>
    cd jasperContainer # Or the name of your cloned directory
    ```

2.  **Build and Run Docker Containers:**
    Navigate to the project root directory (where `docker-compose.yml` is located) and run:
    ```bash
    docker-compose up --build -d
    ```
    This command will:
    *   Build the `jasperphp-app` (PHP-FPM) image based on `backend12/Dockerfile`.
    *   Start the `nginx` web server.
    *   Start the `app` (PHP-FPM) service.
    *   Create a shared network for the services.

3.  **Install Backend Dependencies (Composer):**
    Once the containers are running, execute Composer install within the `app` container:
    ```bash
    docker-compose exec app composer install --no-scripts --optimize-autoloader
    ```

4.  **Install Frontend Dependencies (NPM):**
    Navigate to the `frontend/package` directory and install NPM dependencies:
    ```bash
    cd frontend/package
    npm install
    cd ../.. # Go back to the project root
    ```

5.  **Build Frontend Assets:**
    Build the frontend assets for production:
    ```bash
    cd frontend/package
    npm run build
    cd ../.. # Go back to the project root
    ```

6.  **Configure Environment Variables:**
    Create a `.env` file for the backend by copying the example file:
    ```bash
    cp backend12/.env.example backend12/.env
    ```
    Open `backend12/.env` and configure your database connection and other settings.

7.  **Generate Application Key:**
    Generate a unique application key for Laravel:
    ```bash
    docker-compose exec app php artisan key:generate
    ```

8.  **Run Database Migrations and Seeders:**
    Apply database migrations and seed the database with initial data (e.g., a default user):
    ```bash
    docker-compose exec app php artisan migrate --seed
    ```
9.  **Default user end password:**
    'name' => 'Admin'
    'email' => 'admin@example.com'
    'password' => 'password'

## Configuration

### `.env` File

The `backend12/.env` file is crucial for configuring the application. Key variables include:

*   `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: Your database connection details.
*   Or use embeded default sqlite database
## Usage

After completing the installation and configuration steps, the application should be accessible via your web browser:

*   **Frontend**: Open your browser and go to `http://localhost`.
*   **Backend API**: The backend API runs on port `80` (via Nginx) and `9000` (PHP-FPM).

## Contributing

Contributions are welcome! Please feel free to submit issues or pull requests.

## License

This project is open-source and available under the [MIT License](LICENSE).

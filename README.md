# Laravel Blog API - Advanced Form Request Task

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

---

## 1. Introduction

### 1.1 Purpose

The purpose of this project is to build a robust API for managing posts using the Laravel framework. The project primarily focuses on the advanced usage of Laravel Form Requests to implement complex validation rules, customize error messages, and prepare data before validation, emphasizing the understanding and application of all available methods within Form Request classes.

### 1.2 Scope

The scope of this project includes:

*   Providing an API for CRUD (Create, Read, Update, Delete) operations for posts.
*   Implementing Soft Delete, Restore, and Force Delete operations.
*   Using Form Requests (`StorePostRequest`, `UpdatePostRequest`) for comprehensive input data validation.
*   Automatically generating a `slug` from the `title` if not provided during creation.
*   Implementing custom validation rules for `slug`, `publish_date`, and `keywords`.
*   Preparing and cleaning input data (like `tags`, `is_published`) before validation using `prepareForValidation`.
*   Customizing clear JSON responses for success and failure operations (especially 422 validation errors).
*   Structuring the code by separating business logic into a Service Layer (`PostService`).

### 1.3 Technology Stack

*   **Backend Framework:** Laravel (v.12)
*   **Programming Language:** PHP (v8.2 or later)
*   **Database:** MySQL (Configurable via `.env`, supports others like PostgreSQL)
*   **Dependency Manager:** Composer
*   **API Testing:** Postman

---

## 2. Overall Description

### 2.1 Product Perspective

This project is a standalone API that can serve as the backend for a Content Management System (CMS) or a blog application. It provides essential functionalities for managing posts with a focus on code quality and input data integrity.

### 2.2 Product Features

*   **Post Management:**
    *   List posts (with pagination).
    *   Create a new post with comprehensive validation.
    *   View details of a specific post.
    *   Update an existing post with comprehensive validation.
    *   Delete a post (Soft Delete).
    *   Restore a soft-deleted post.
    *   Permanently delete a post.
*   **Advanced Validation:**
    *   Comprehensive use of Form Requests.
    *   Customized validation rules and messages.
    *   Data preparation before validation.
    *   Automatic Slug generation.
    *   Custom validation rules.
*   **API Responses:**
    *   Consistent JSON responses for all operations.
    *   Usage of API Resources (`PostResource`) to standardize returned data format.
    *   Appropriate HTTP status codes (200, 201, 422, 404, 500).
*   **Code Structure:**
    *   Utilizes the Controller-Service-Model pattern.
    *   Separation of concerns for improved maintainability and testability.

---

## 3. Specific Requirements

### 3.1 Functional Requirements

#### 3.1.1 API Endpoints

| Verb        | URI                         | Description                         | Controller Method | Form Request        |
| :---------- | :-------------------------- | :---------------------------------- | :---------------- | :------------------ |
| `GET`       | `/api/posts`                | Fetch a list of posts (paginated)   | `index`           | -                   |
| `POST`      | `/api/posts`                | Create a new post                   | `store`           | `StorePostRequest`  |
| `GET`       | `/api/posts/{post}`         | Fetch a specific post               | `show`            | -                   |
| `PUT/PATCH` | `/api/posts/{post}`         | Update an existing post             | `update`          | `UpdatePostRequest` |
| `DELETE`    | `/api/posts/{post}`         | Delete a post (Soft Delete)         | `destroy`         | -                   |
| `POST`      | `/api/posts/{id}/restore`   | Restore a soft-deleted post         | `restore`         | -                   |
| `DELETE`    | `/api/posts/{id}/force`     | Permanently delete a post           | `forcedelete`     | -                   |
| `GET`       | `/api/posts/trashed`        | List soft-deleted (trashed) posts   | `trashed`         | `per_page` (query)  |

*   **Note:** `{post}` can be the post ID. `{id}` in custom routes is the post ID.
*   The `?per_page=N` query parameter can be used with `GET /api/posts` to specify the number of items per page.

#### 3.1.2 Validation & Data Preparation - Form Requests

`StorePostRequest` and `UpdatePostRequest` were used to meet the following requirements using all specified methods:

*   **`authorize(): bool`**:
    *   Determines if the current user is authorized to make this request.
    *   In this project, it defaults to `true`, allowing all requests (can be modified later for authorization logic).
    *   `Log::info()` is used to log the authorization check passage.
*   **`rules(): array`**:
    *   Defines validation rules for required fields (`title`, `body`) and optional fields (`slug`, `is_published`, `publish_date`, `meta_description`, `tags`, `keywords`).
    *   Uses built-in Laravel rules (`required`, `string`, `max`, `unique`, `boolean`, `date`, `nullable`, `sometimes`).
    *   Applies custom rules (`SlugFormatRule`, `FutureDateRule`, `MaxWordsRule`).
    *   Uses `Rule::unique()->ignore()` in `UpdatePostRequest` to allow the current post's slug during updates.
    *   Uses `Rule::requiredIf()` to make `publish_date` required only if `is_published` is true.
*   **`messages(): array`**:
    *   Used to customize error messages shown to the user when a specific validation rule fails, making them clearer and more helpful.
    *   Example: Customizing the error message for the `slug.unique` rule.
*   **`attributes(): array`**:
    *   Used to specify user-friendly field names for use in error messages, replacing default placeholders like `:attribute` (e.g., using "Title" instead of `title`).
*   **`prepareForValidation(): void`**:
    *   Used to modify or add data to the request *before* validation rules are applied.
    *   In `StorePostRequest` and `UpdatePostRequest`:
        *   Automatically generates `slug` from `title` using `Str::slug()` if `title` exists and `slug` is empty.
        *   Cleans the `tags` field by removing extra spaces and duplicate commas.
        *   Casts `is_published` to a proper boolean value.
        *   Sets `null` values for nullable fields if they are submitted as empty strings.
        *   Sets `publish_date` to `null` if `is_published` is false during an update.
*   **`passedValidation(): void`**:
    *   Executed after validation succeeds and before the Controller method is called.
    *   In this project, it logs an `Log::info()` message indicating successful validation along with the validated data. (Optional for actual use).
*   **`failedValidation(Validator $validator): void`**:
    *   Used to customize the response returned when validation fails.
    *   By default, Laravel redirects. Here, the default behavior is overridden to return an `HttpResponseException` containing a custom JSON response.
    *   The response includes: `status: error`, a general error message, a list of errors per field (`$validator->errors()`), and the `422 Unprocessable Entity` status code.
    *   `Log::warning()` is used to log the validation failure with error details.

#### 3.1.3 Custom Validation Rules

*   **`SlugFormatRule`**: Ensures the field value (slug) contains only lowercase English letters (`a-z`), numbers (`0-9`), and hyphens (`-`).
*   **`FutureDateRule`**: Ensures the provided date is a future date or today (not in the past). Ignores the value if it's `null` or an empty string.
*   **`MaxWordsRule`**: Ensures the provided text does not exceed a specified number of words (default is 15 words in this project for the `keywords` field).

### 3.2 Database Requirements

*   A `posts` table was created using Laravel Migrations (`database/migrations/YYYY_MM_DD_HHMMSS_create_posts_table.php`).
*   The table includes the following columns:
    *   `id` (Primary Key, BigInt, Auto Increment)
    *   `title` (String)
    *   `slug` (String, Unique)
    *   `body` (Text)
    *   `is_published` (Boolean, default: false)
    *   `publish_date` (Date, Nullable)
    *   `meta_description` (String, length 160, Nullable)
    *   `tags` (Text, Nullable) - To store comma-separated tags.
    *   `keywords` (Text, Nullable) - To store keywords.
    *   `deleted_at` (Timestamp, Nullable) - For Soft Deletes.
    *   `created_at` (Timestamp)
    *   `updated_at` (Timestamp)
*   An Eloquent model `App\Models\Post` represents this table, with `fillable` attributes, `casts`, and the `SoftDeletes` trait enabled.

### 3.3 External Interface Requirements

*   **API:** The primary interface is a RESTful API using JSON for communication.
*   **Postman:** A Postman collection is provided to facilitate API testing.

---

## 4. Installation and Setup

Follow these steps to run the project locally:

1.  **Clone the repository:**
    ```bash
    git clone <your-repository-url>
    cd <repository-folder-name>
    ```
    *(Replace `<your-repository-url>` and `<repository-folder-name>`)*

2.  **Install PHP dependencies:**
    ```bash
    composer install
    ```

3.  **Create environment file:**
    ```bash
    cp .env.example .env
    ```

4.  **Generate application key:**
    ```bash
    php artisan key:generate
    ```

5.  **Configure the database (MySQL):**
    *   Open the `.env` file.
    *   Set the database connection to MySQL:
        ```dotenv
        DB_CONNECTION=mysql
        ```
    *   Update the following lines with your MySQL database credentials:
        ```dotenv
        DB_HOST=127.0.0.1 # Or your MySQL host
        DB_PORT=3306      # Or your MySQL port
        DB_DATABASE=your_database_name # Replace with your database name
        DB_USERNAME=your_database_user # Replace with your MySQL username
        DB_PASSWORD=your_database_password # Replace with your MySQL password
        ```
    *   Ensure the database (`your_database_name`) exists on your MySQL server. If not, create it.

6.  **Run database migrations:**
    ```bash
    php artisan migrate
    ```
    *(Optional: If seeders exist, you can run `php artisan migrate --seed`)*

7.  **(Optional) Install & Build Frontend Assets:**
    *   If you plan to use tools requiring Node.js (like Vite for development):
    ```bash
    npm install
    npm run dev  # Or npm run build for production
    ```

8.  **Start the development server:**
    ```bash
    php artisan serve
    ```
    The API will typically be available at `http://localhost:8000/api/`.

---

## 5. API Testing with Postman

To facilitate testing the various API endpoints, a Postman collection has been prepared.

1.  **Collection Link:**
    [https://documenter.getpostman.com/view/39062755/2sB2j68pWX](https://documenter.getpostman.com/view/39062755/2sB2j68pWX)
    *(Alternatively, import the JSON file if provided)*

2.  **Import:**
    *   Open Postman.
    *   Click the `Import` button.
    *   Paste the link above into the `Link` tab or upload the JSON file.
    *   Click `Continue`, then `Import`.

3.  **Environment Setup:**
    *   You will likely need to set up an Environment Variable in Postman for the API base URL.
    *   Create a new Environment or edit an existing one.
    *   Add a variable named `baseUrl` with the value `http://localhost:8000/api` (or your local server's address).
    *   You might also need a `postId` variable to store the ID of a created/updated post for use in `show`, `update`, `destroy`, `restore`, `forcedelete` requests.

4.  **Making Requests:**
    *   Select the imported collection and the active environment.
    *   Browse the different requests (`Get All Posts`, `Create Post`, `Update Post`, etc.).
    *   Ensure you modify the request Body for `POST`, `PUT/PATCH` requests with appropriate data.
    *   Observe how validation errors are handled (422 response with error details).

---

## 6. License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

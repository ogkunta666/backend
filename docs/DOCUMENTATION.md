# Task Manager - Teljes Programdokumentáció

## Tartalomjegyzék

1. [Projekt Áttekintés](#projekt-áttekintés)
2. [Telepítés és Beállítás](#telepítés-és-beállítás)
3. [Adatbázis Szerkezet](#adatbázis-szerkezet)
4. [Authentikáció](#authentikáció)
5. [API Végpontok](#api-végpontok)
6. [Webes Felület](#webes-felület)
7. [Middleware-ek](#middleware-ek)
8. [Modellek és Kapcsolatok](#modellek-és-kapcsolatok)
9. [Tesztelés](#tesztelés)
10. [Postman Collection](#postman-collection)

---

## Projekt Áttekintés

### Technológiai Stack

- **Framework:** Laravel 11
- **PHP Verzió:** 8.2+
- **Adatbázis:** MySQL
- **Authentikáció:** Laravel Sanctum (API) + Session Auth (Web)
- **Frontend:** Blade Templates + Bootstrap 5
- **Tesztelés:** PHPUnit Feature Tests

### Fő Funkciók

- ✅ Felhasználó kezelés (admin jogosultsággal)
- ✅ Task (feladat) kezelés
- ✅ Task hozzárendelések (assignments)
- ✅ RESTful API Sanctum token authentikációval
- ✅ Webes admin felület session authentikációval
- ✅ Soft delete támogatás minden modellnél
- ✅ Átfogó feature tesztek (32 teszt)

---

## Telepítés és Beállítás

### 1. Előfeltételek

Ellenőrizd, hogy telepítve van:
- PHP 8.2 vagy újabb
- Composer
- MySQL szerver (XAMPP)
- Node.js és NPM (opcionális, frontend asset-ekhez)

### 2. Klónozás és Függőségek Telepítése

```bash
# Projekt mappa létrehozása
cd c:\xampp\htdocs
git clone <repository-url> todo_sanctum
cd todo_sanctum

# Composer függőségek telepítése
composer install

# NPM függőségek telepítése (opcionális)
npm install
```

### 3. Környezeti Változók Beállítása

Másold át a `.env.example` fájlt `.env` néven:

```bash
cp .env.example .env
```

Szerkeszd a `.env` fájlt:

```env
APP_NAME="Task Manager"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=todo_sanctum
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost:8000
SESSION_DRIVER=file
```

### 4. Alkalmazás Kulcs Generálása

```bash
php artisan key:generate
```

### 5. Adatbázis Létrehozása

Hozz létre egy új adatbázist MySQL-ben:

```sql
CREATE DATABASE todo_sanctum CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Migrációk és Seed Adatok

```bash
# Migrációk futtatása és adatbázis feltöltése
php artisan migrate:fresh --seed
```

Ez létrehozza az összes táblát és feltölti teszt adatokkal:
- **1 admin user:** admin@taskmanager.hu / admin123
- **9 normál user:** random generált adatokkal
- **50 task:** random prioritásokkal és státuszokkal
- **Task assignments:** random hozzárendelések

### 7. Development Szerver Indítása

```bash
# Laravel beépített szerver
php artisan serve

# Vagy XAMPP Apache-csel
# Nyisd meg: http://localhost/todo_sanctum/public
```

---

## Adatbázis Szerkezet

### Users Tábla

**Migráció:** `2026_01_01_000000_create_users_table.php`

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->string('department')->nullable();
    $table->string('phone')->nullable();
    $table->boolean('is_admin')->default(false);
    $table->rememberToken();
    $table->softDeletes();
    $table->timestamps();
});
```

**Oszlopok:**
- `id`: Elsődleges kulcs
- `name`: Felhasználó neve
- `email`: Egyedi email cím
- `password`: Hash-elt jelszó
- `department`: Részleg (IT, HR, Finance, stb.)
- `phone`: Telefonszám
- `is_admin`: Admin jogosultság (boolean)
- `deleted_at`: Soft delete időbélyeg

### Tasks Tábla

**Migráció:** `2026_02_09_082016_create_tasks_table.php`

```php
Schema::create('tasks', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description')->nullable();
    $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
    $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
    $table->date('due_date')->nullable();
    $table->softDeletes();
    $table->timestamps();
});
```

**Oszlopok:**
- `id`: Elsődleges kulcs
- `title`: Feladat címe
- `description`: Részletes leírás
- `priority`: Prioritás (low, medium, high)
- `status`: Állapot (pending, in_progress, completed)
- `due_date`: Határidő
- `deleted_at`: Soft delete időbélyeg

### Task Assignments Tábla

**Migráció:** `2026_02_09_082206_create_task_assignments_table.php`

```php
Schema::create('task_assignments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('task_id')->constrained()->onDelete('cascade');
    $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
    $table->timestamp('assigned_at')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->softDeletes();
    $table->timestamps();
});
```

**Oszlopok:**
- `id`: Elsődleges kulcs
- `user_id`: Foreign key a users táblához
- `task_id`: Foreign key a tasks táblához
- `status`: Hozzárendelés státusza
- `assigned_at`: Hozzárendelés időpontja
- `completed_at`: Befejezés időpontja
- `deleted_at`: Soft delete időbélyeg

### Personal Access Tokens Tábla (Sanctum)

**Migráció:** `2026_02_09_081721_create_personal_access_tokens_table.php`

Laravel Sanctum által használt tábla az API token-ek tárolására.

---

## Authentikáció

### API Authentikáció (Sanctum)

#### Token Generálás

A felhasználóknak először be kell jelentkezniük, hogy token-t kapjanak:

```http
POST /api/login
Content-Type: application/json

{
    "email": "admin@taskmanager.hu",
    "password": "admin123"
}
```

**Válasz:**

```json
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "Admin",
        "email": "admin@taskmanager.hu",
        "is_admin": true
    },
    "token": "1|laravel_sanctum_token_here"
}
```

#### Token Használata

A token-t a `Authorization` header-ben küldd minden védett API kéréshez:

```http
GET /api/profile
Authorization: Bearer 1|laravel_sanctum_token_here
```

#### Token Törlése (Logout)

```http
POST /api/logout
Authorization: Bearer 1|laravel_sanctum_token_here
```

### Web Authentikáció (Session)

#### Login Oldal

```
URL: http://localhost:8000/login
```

**HTML Form:**

```html
<form method="POST" action="/login">
    @csrf
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    <input type="checkbox" name="remember">
    <button type="submit">Login</button>
</form>
```

#### Logout

```html
<form method="POST" action="/logout">
    @csrf
    <button type="submit">Logout</button>
</form>
```

---

## API Végpontok

### Publikus Végpontok (Authentikáció Nélkül)

#### 1. API Status Check

```http
GET /api/ping
```

**Válasz:**
```json
{
    "message": "API is working",
    "timestamp": "2026-02-12 10:30:45"
}
```

#### 2. Regisztráció

```http
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "department": "IT",
    "phone": "+36301234567"
}
```

**Válasz:**
```json
{
    "message": "Registration successful",
    "user": {
        "id": 11,
        "name": "John Doe",
        "email": "john@example.com",
        "is_admin": false
    },
    "token": "2|token_string"
}
```

#### 3. Bejelentkezés

```http
POST /api/login
Content-Type: application/json

{
    "email": "admin@taskmanager.hu",
    "password": "admin123"
}
```

### Védett Végpontok (auth:sanctum middleware)

#### 4. Profil Lekérdezés

```http
GET /api/profile
Authorization: Bearer {token}
```

**Válasz:**
```json
{
    "id": 1,
    "name": "Admin",
    "email": "admin@taskmanager.hu",
    "department": "Management",
    "phone": "+36301234567",
    "is_admin": true,
    "created_at": "2026-02-12T10:00:00.000000Z"
}
```

#### 5. Saját Task-ok Lekérdezése

```http
GET /api/tasks/my-tasks
Authorization: Bearer {token}
```

**Válasz:**
```json
{
    "tasks": [
        {
            "id": 1,
            "title": "Complete documentation",
            "description": "Write full docs",
            "priority": "high",
            "status": "in_progress",
            "due_date": "2026-02-15",
            "assignment": {
                "id": 5,
                "status": "in_progress",
                "assigned_at": "2026-02-10 09:00:00"
            }
        }
    ]
}
```

#### 6. Task Státusz Frissítés

```http
PUT /api/tasks/my-tasks/{taskId}/status
Authorization: Bearer {token}
Content-Type: application/json

{
    "status": "completed"
}
```

**Válasz:**
```json
{
    "message": "Task status updated successfully",
    "task": {
        "id": 1,
        "status": "completed",
        "assignment": {
            "status": "completed",
            "completed_at": "2026-02-12 11:30:00"
        }
    }
}
```

#### 7. Kijelentkezés

```http
POST /api/logout
Authorization: Bearer {token}
```

### Admin Végpontok (auth:sanctum + admin middleware)

#### Users Management

**Lista összes userről:**
```http
GET /api/admin/users
Authorization: Bearer {admin_token}
```

**User létrehozás:**
```http
POST /api/admin/users
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "name": "New User",
    "email": "newuser@example.com",
    "password": "password123",
    "department": "Sales",
    "phone": "+36301234567",
    "is_admin": false
}
```

**User módosítás:**
```http
PUT /api/admin/users/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "name": "Updated Name",
    "department": "Marketing"
}
```

**User törlés (soft delete):**
```http
DELETE /api/admin/users/{id}
Authorization: Bearer {admin_token}
```

**User visszaállítás:**
```http
POST /api/admin/users/{id}/restore
Authorization: Bearer {admin_token}
```

#### Tasks Management

**Lista összes task-ról:**
```http
GET /api/admin/tasks
Authorization: Bearer {admin_token}
```

**Task létrehozás:**
```http
POST /api/admin/tasks
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "title": "New Task",
    "description": "Task details",
    "priority": "high",
    "status": "pending",
    "due_date": "2026-02-20"
}
```

**Task módosítás:**
```http
PUT /api/admin/tasks/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "title": "Updated Task Title",
    "priority": "medium",
    "status": "in_progress"
}
```

**Task törlés:**
```http
DELETE /api/admin/tasks/{id}
Authorization: Bearer {admin_token}
```

**Task visszaállítás:**
```http
POST /api/admin/tasks/{id}/restore
Authorization: Bearer {admin_token}
```

#### Task Assignments Management

**Lista minden hozzárendelésről:**
```http
GET /api/admin/assignments
Authorization: Bearer {admin_token}
```

**Assignment létrehozás:**
```http
POST /api/admin/assignments
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "user_id": 2,
    "task_id": 5,
    "status": "pending",
    "assigned_at": "2026-02-12 10:00:00"
}
```

**Assignment módosítás:**
```http
PUT /api/admin/assignments/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "status": "completed",
    "completed_at": "2026-02-12 15:30:00"
}
```

**Assignment törlés:**
```http
DELETE /api/admin/assignments/{id}
Authorization: Bearer {admin_token}
```

**Egy task hozzárendelései:**
```http
GET /api/admin/assignments/by-task/{taskId}
Authorization: Bearer {admin_token}
```

**Egy user hozzárendelései:**
```http
GET /api/admin/assignments/by-user/{userId}
Authorization: Bearer {admin_token}
```

---

## Webes Felület

### Login Oldal

**URL:** `http://localhost:8000/login`

Egyszerű bejelentkezési form admin felhasználók számára.

**Default credentials:**
- Email: `admin@taskmanager.hu`
- Jelszó: `admin123`

### Admin Dashboard

**URL:** `http://localhost:8000/admin/dashboard`

**Middleware:** `auth`, `admin`

Statisztikák megjelenítése:
- Összes felhasználó / aktív felhasználók
- Összes task / aktív task-ok
- Összes hozzárendelés / befejezett hozzárendelések

**Controller metódus:**

```php
public function dashboard()
{
    $stats = [
        'total_users' => User::withTrashed()->count(),
        'active_users' => User::count(),
        'total_tasks' => Task::withTrashed()->count(),
        'active_tasks' => Task::count(),
        'total_assignments' => Task_assignment::withTrashed()->count(),
        'completed_assignments' => Task_assignment::where('status', 'completed')->count(),
    ];

    return view('admin.dashboard', compact('stats'));
}
```

### Users Management (Web)

#### Users Lista

**URL:** `http://localhost:8000/admin/users`

**Controller:** `AdminWebController@usersIndex`

```php
public function usersIndex()
{
    $users = User::withTrashed()->orderBy('id', 'desc')->paginate(15);
    return view('admin.users.index', compact('users'));
}
```

#### User Létrehozás

**URL:** `http://localhost:8000/admin/users/create`

**Form adatok:**
- name (kötelező)
- email (kötelező, egyedi)
- password (kötelező, min 6 karakter)
- department (opcionális)
- phone (opcionális)
- is_admin (checkbox)

**Controller:**

```php
public function usersStore(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6',
        'department' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:20',
        'is_admin' => 'boolean',
    ]);

    $validated['password'] = Hash::make($validated['password']);
    $validated['is_admin'] = $request->has('is_admin');

    User::create($validated);

    return redirect()->route('admin.users.index')
        ->with('success', 'User created successfully');
}
```

#### User Szerkesztés

**URL:** `http://localhost:8000/admin/users/{id}/edit`

**Controller:**

```php
public function usersEdit($id)
{
    $user = User::withTrashed()->findOrFail($id);
    return view('admin.users.edit', compact('user'));
}

public function usersUpdate(Request $request, $id)
{
    $user = User::withTrashed()->findOrFail($id);

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
        'password' => 'nullable|string|min:6',
        'department' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:20',
        'is_admin' => 'boolean',
    ]);

    if (!empty($validated['password'])) {
        $validated['password'] = Hash::make($validated['password']);
    } else {
        unset($validated['password']);
    }

    $validated['is_admin'] = $request->has('is_admin');

    $user->update($validated);

    return redirect()->route('admin.users.index')
        ->with('success', 'User updated successfully');
}
```

#### User Törlés és Visszaállítás

```php
public function usersDestroy($id)
{
    $user = User::findOrFail($id);
    $user->delete(); // Soft delete

    return redirect()->route('admin.users.index')
        ->with('success', 'User deleted successfully');
}

public function usersRestore($id)
{
    $user = User::withTrashed()->findOrFail($id);
    $user->restore();

    return redirect()->route('admin.users.index')
        ->with('success', 'User restored successfully');
}
```

### Tasks Management (Web)

Hasonló struktúra, mint a Users:

- **Lista:** `/admin/tasks` → `tasksIndex()`
- **Létrehozás:** `/admin/tasks/create` → `tasksCreate()`, `tasksStore()`
- **Szerkesztés:** `/admin/tasks/{id}/edit` → `tasksEdit()`, `tasksUpdate()`
- **Törlés:** `/admin/tasks/{id}` → `tasksDestroy()`
- **Visszaállítás:** `/admin/tasks/{id}/restore` → `tasksRestore()`

### Assignments Management (Web)

- **Lista:** `/admin/assignments` → `assignmentsIndex()`
- **Létrehozás:** `/admin/assignments/create` → `assignmentsCreate()`, `assignmentsStore()`
- **Szerkesztés:** `/admin/assignments/{id}/edit` → `assignmentsEdit()`, `assignmentsUpdate()`
- **Törlés:** `/admin/assignments/{id}` → `assignmentsDestroy()`
- **Visszaállítás:** `/admin/assignments/{id}/restore` → `assignmentsRestore()`

---

## Middleware-ek

### IsAdmin Middleware

**Fájl:** `app/Http/Middleware/IsAdmin.php`

**Regisztráció:** `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => \App\Http\Middleware\IsAdmin::class,
    ]);
})
```

**Implementáció:**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Ellenőrizzük, hogy be van-e jelentkezve a felhasználó
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthorized. Please login first.'
                ], 401);
            }
            return redirect()->route('login');
        }

        // Ellenőrizzük, hogy admin-e a felhasználó
        if (!auth()->user()->is_admin) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Forbidden. Admin access required.'
                ], 403);
            }
            abort(403, 'Forbidden. Admin access required.');
        }

        return $next($request);
    }
}
```

**Használat route-okban:**

```php
// API route-ok
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    // ...
});

// Web route-ok
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminWebController::class, 'dashboard']);
    // ...
});
```

---

## Modellek és Kapcsolatok

### User Model

**Fájl:** `app/Models/User.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'department',
        'phone',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    // Kapcsolat: User-hez rendelt task-ok
    public function taskAssignments()
    {
        return $this->hasMany(Task_assignment::class);
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_assignments')
            ->withPivot('status', 'assigned_at', 'completed_at')
            ->withTimestamps();
    }
}
```

### Task Model

**Fájl:** `app/Models/Task.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'priority',
        'status',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    // Kapcsolat: Task-hoz rendelt userek
    public function assignments()
    {
        return $this->hasMany(Task_assignment::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'task_assignments')
            ->withPivot('status', 'assigned_at', 'completed_at')
            ->withTimestamps();
    }
}
```

### Task_assignment Model

**Fájl:** `app/Models/Task_assignment.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task_assignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'task_id',
        'status',
        'assigned_at',
        'completed_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Kapcsolatok
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
```

### Factory-k

#### UserFactory

```php
public function definition(): array
{
    return [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'email_verified_at' => now(),
        'password' => Hash::make('Jelszo12'),
        'department' => fake()->randomElement(['IT', 'HR', 'Finance', 'Marketing', 'Sales', 'Operations']),
        'phone' => fake()->phoneNumber(),
        'is_admin' => false,
        'remember_token' => Str::random(10),
    ];
}

public function admin(): static
{
    return $this->state(fn (array $attributes) => [
        'name' => 'Admin',
        'email' => 'admin@taskmanager.hu',
        'password' => Hash::make('admin123'),
        'department' => 'Management',
        'is_admin' => true,
    ]);
}
```

---

## Tesztelés

### Feature Tesztek

Összesen **32 teszt** található 4 fájlban.

#### Tesztek Futtatása

```bash
# Összes teszt futtatása
php artisan test

# Specifikus teszt fájl futtatása
php artisan test tests/Feature/AuthTest.php

# Specifikus teszt metódus
php artisan test --filter test_user_can_register

# Verbose kimenet
php artisan test -v
```

### 1. AuthTest (7 teszt)

**Fájl:** `tests/Feature/AuthTest.php`

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_ping_endpoint_works(): void
    {
        $response = $this->getJson('/api/ping');
        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'timestamp']);
    }

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'department' => 'IT',
            'phone' => '+36301234567',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'user' => ['id', 'name', 'email', 'is_admin'],
                     'token'
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
    }

    // További tesztek...
}
```

**Tesztelt esetek:**
1. ✅ API ping endpoint működik
2. ✅ User regisztráció sikeres
3. ✅ Regisztráció validáció (email formátum)
4. ✅ Duplikált email nem engedélyezett
5. ✅ User bejelentkezés sikeres
6. ✅ Helytelen jelszó elutasítása
7. ✅ User kijelentkezés (token törlés)

### 2. AdminAccessTest (11 teszt)

**Fájl:** `tests/Feature/AdminAccessTest.php`

**Tesztelt esetek:**
1. ✅ Admin hozzáfér admin végpontokhoz
2. ✅ Normál user nem fér hozzá admin végpontokhoz (403)
3. ✅ Authentikáció nélkül nincs hozzáférés (401)
4. ✅ Admin listázhatja az összes usert
5. ✅ Admin létrehozhat új usert
6. ✅ Admin törölhet usert (soft delete)
7. ✅ Admin visszaállíthat törölt usert
8. ✅ Admin listázhatja az összes task-ot
9. ✅ Admin létrehozhat task-ot
10. ✅ Admin törölhet task-ot
11. ✅ Admin visszaállíthat task-ot

### 3. UserTaskTest (7 teszt)

**Fájl:** `tests/Feature/UserTaskTest.php`

**Tesztelt esetek:**
1. ✅ User lekérheti a saját profilját
2. ✅ User lekérheti a hozzárendelt task-jait
3. ✅ User nem látja más userek task-jait
4. ✅ User frissítheti saját task státuszát
5. ✅ Completed státusz automatikusan beállítja completed_at-ot
6. ✅ User nem frissíthet más userek task-jait
7. ✅ User csak saját hozzárendelt task-jait látja

### 4. TaskAssignmentTest (9 teszt)

**Fájl:** `tests/Feature/TaskAssignmentTest.php`

**Tesztelt esetek:**
1. ✅ Admin listázhatja az összes assignmentet
2. ✅ Admin létrehozhat assignmentet
3. ✅ Duplikált assignment nem engedélyezett
4. ✅ Admin módosíthat assignmentet
5. ✅ Admin törölhet assignmentet
6. ✅ Admin visszaállíthat assignmentet
7. ✅ Assignmentek lekérdezése task szerint
8. ✅ Assignmentek lekérdezése user szerint
9. ✅ Normál user nem éri el az assignment végpontokat

### Teszt Lefedettség

```bash
# Test coverage report generálása (ha XDebug telepítve van)
php artisan test --coverage

# Vagy PHPUnit-tal
./vendor/bin/phpunit --coverage-html coverage
```

---

## Postman Collection

### Importálás

**Fájl:** `docs/postman_collection.json`

1. Nyisd meg a Postman-t
2. File → Import
3. Válaszd ki a `postman_collection.json` fájlt
4. Kattints az "Import" gombra

### Collection Változók

A collection két változót használ:

```json
{
    "variable": [
        {
            "key": "base_url",
            "value": "http://localhost:8000/api",
            "type": "string"
        },
        {
            "key": "auth_token",
            "value": "",
            "type": "string"
        }
    ]
}
```

### Automatikus Token Mentés

A **Login** request tartalmaz egy Test scriptet, ami automatikusan elmenti a token-t:

```javascript
// Test script a Login request-ben
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.collectionVariables.set("auth_token", jsonData.token);
    console.log("Token saved: " + jsonData.token);
}
```

Ezután minden védett endpoint automatikusan használja a `{{auth_token}}` változót:

```
Authorization: Bearer {{auth_token}}
```

### Request-ek Csoportosítása

1. **Auth**
   - Ping
   - Register
   - Login
   - Logout

2. **User Profile**
   - Get Profile

3. **My Tasks**
   - Get My Tasks
   - Update Task Status

4. **Admin - Users**
   - List All Users
   - Create User
   - Get User
   - Update User
   - Delete User
   - Restore User

5. **Admin - Tasks**
   - List All Tasks
   - Create Task
   - Get Task
   - Update Task
   - Delete Task
   - Restore Task

6. **Admin - Assignments**
   - List All Assignments
   - Create Assignment
   - Get Assignment
   - Update Assignment
   - Delete Assignment
   - Restore Assignment
   - Get Assignments by Task
   - Get Assignments by User

---

## Hasznos Parancsok

### Artisan Parancsok

```bash
# Route lista megjelenítése
php artisan route:list

# Specifikus route keresése
php artisan route:list --path=admin

# Migrációk futtatása
php artisan migrate

# Migrációk visszavonása
php artisan migrate:rollback

# Adatbázis újraépítése seed-ekkel
php artisan migrate:fresh --seed

# Cache törlése
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Storage link létrehozása
php artisan storage:link

# Új migration létrehozása
php artisan make:migration create_table_name

# Új model létrehozása
php artisan make:model ModelName -mf
# -m: migration, -f: factory, -c: controller

# Új controller létrehozása
php artisan make:controller ControllerName

# Új middleware létrehozása
php artisan make:middleware MiddlewareName

# Új seeder létrehozása
php artisan make:seeder SeederName

# Teszt futtatása
php artisan test

# Tinker (Laravel console)
php artisan tinker
```

### Tinker Példák

```php
# Tinker indítása
php artisan tinker

# User létrehozása
$user = User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => Hash::make('password'),
    'is_admin' => false
]);

# User keresése
$user = User::where('email', 'admin@taskmanager.hu')->first();

# Task létrehozása
$task = Task::create([
    'title' => 'New Task',
    'priority' => 'high',
    'status' => 'pending'
]);

# Assignment létrehozása
$assignment = Task_assignment::create([
    'user_id' => 1,
    'task_id' => 1,
    'status' => 'pending',
    'assigned_at' => now()
]);

# Kapcsolatok lekérdezése
$user->tasks;
$task->users;
$task->assignments;

# Token generálás
$token = $user->createToken('api-token')->plainTextToken;
```

### Composer Parancsok

```bash
# Függőségek telepítése
composer install

# Függőségek frissítése
composer update

# Autoload újragenerálása
composer dump-autoload

# Új package telepítése
composer require vendor/package

# Dev dependency telepítése
composer require --dev vendor/package

# Package eltávolítása
composer remove vendor/package
```

### Git Parancsok

```bash
# Repository inicializálás
git init

# Változások hozzáadása
git add .

# Commit
git commit -m "Initial commit"

# Remote hozzáadása
git remote add origin <repository-url>

# Push
git push -u origin main

# Ág létrehozása
git checkout -b feature/new-feature

# Változások megtekintése
git status
git diff

# Log megtekintése
git log --oneline
```

---

## Hibakeresés és Gyakori Problémák

### 1. "Token Mismatch" Hiba

**Probléma:** CSRF token hiba form submit-nál.

**Megoldás:**
```php
// Blade template-ben:
<form method="POST">
    @csrf
    <!-- form mezők -->
</form>

// Vagy meta tag:
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### 2. "Class not found" Hiba

**Megoldás:**
```bash
composer dump-autoload
```

### 3. "Unauthenticated" API Hiba

**Probléma:** Token nem lett elküldve vagy érvénytelen.

**Megoldás:**
- Ellenőrizd, hogy a token szerepel-e az Authorization header-ben
- Format: `Authorization: Bearer {token}`
- Login után generálj új token-t

### 4. "Permission Denied" Admin Végpontokon

**Probléma:** Normál user próbál admin endpoint-ot elérni.

**Megoldás:**
- Admin userrel jelentkezz be
- Ellenőrizd a `is_admin` mezőt az adatbázisban

### 5. Migration Hiba

**Probléma:** "Table already exists"

**Megoldás:**
```bash
php artisan migrate:fresh --seed
# FIGYELEM: Ez törli az összes adatot!
```

### 6. Port Foglalt

**Probléma:** "Address already in use"

**Megoldás:**
```bash
# Más port használata
php artisan serve --port=8001

# Vagy a foglalt folyamat leállítása (Windows)
netstat -ano | findstr :8000
taskkill /PID {process_id} /F
```

### 7. Database Connection Error

**Megoldás:**
- Ellenőrizd a `.env` fájlt
- XAMPP MySQL szerver fut-e
- Létezik-e az adatbázis

```bash
# MySQL ellenőrzése
mysql -u root -p
SHOW DATABASES;
```

---

## Biztonsági Megjegyzések

### 1. Környezeti Változók

Soha ne commitold a `.env` fájlt!

```bash
# .gitignore fájlban:
.env
.env.backup
```

### 2. Password Hash-elés

Mindig használj `Hash::make()` jelszavakhoz:

```php
'password' => Hash::make($request->password)
```

### 3. Mass Assignment Protection

Használj `$fillable` vagy `$guarded` a modellekben:

```php
protected $fillable = ['name', 'email', 'password'];
// vagy
protected $guarded = ['id', 'is_admin'];
```

### 4. SQL Injection Védelem

Laravel Eloquent automatikusan védett, de raw query-knél használj binding-ot:

```php
DB::select('SELECT * FROM users WHERE email = ?', [$email]);
```

### 5. CSRF Protection

Minden POST/PUT/DELETE form-ban használd a `@csrf` direktívát.

### 6. XSS Protection

Blade automatikusan escape-eli a változókat:

```php
{{ $user->name }}  // Biztonságos
{!! $html !!}      // NE használd user input-tal!
```

---

## Karbantartás és Frissítések

### Laravel Frissítés

```bash
# Composer frissítése
composer update

# Laravel verzió ellenőrzése
php artisan --version

# Major verzió frissítésnél kövesd a hivatalos guide-ot:
# https://laravel.com/docs/11.x/upgrade
```

### Log Fájlok Tisztítása

```bash
# Log mappa törlése
rm -rf storage/logs/*

# Vagy Laravel Telescope használata (opcionális)
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

### Database Backup

```bash
# MySQL dump
mysqldump -u root -p todo_sanctum > backup.sql

# Visszaállítás
mysql -u root -p todo_sanctum < backup.sql
```

### Performance Optimalizálás

```bash
# Config cache
php artisan config:cache

# Route cache
php artisan route:cache

# View cache
php artisan view:cache

# Cache törlése (development)
php artisan optimize:clear
```

---

## Kapcsolat és Támogatás

**Projekt Információk:**
- Laravel verzió: 11.x
- PHP verzió: 8.2+
- Sanctum verzió: 4.x

**Hasznos Linkek:**
- [Laravel Dokumentáció](https://laravel.com/docs/11.x)
- [Sanctum Dokumentáció](https://laravel.com/docs/11.x/sanctum)
- [Laravel API Resources](https://laravel.com/docs/11.x/eloquent-resources)

---

**Utolsó frissítés:** 2026. február 12.
**Dokumentáció verzió:** 1.0

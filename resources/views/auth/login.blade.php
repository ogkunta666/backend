<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7fafc;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h1 { font-size: 1.5rem; margin-bottom: 1.5rem; text-align: center; color: #2d3748; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #4a5568; }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #cbd5e0;
            border-radius: 4px;
            font-size: 1rem;
        }
        input:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        .checkbox-group input {
            margin-right: 0.5rem;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #4299e1;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
        }
        button:hover { background-color: #3182ce; }
        .alert {
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            background-color: #fed7d7;
            border: 1px solid #fc8181;
            color: #742a2a;
        }
        .info {
            margin-top: 1.5rem;
            padding: 1rem;
            background-color: #f7fafc;
            border-radius: 4px;
            text-align: center;
            font-size: 0.875rem;
            color: #718096;
        }
        .info strong { color: #2d3748; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Login</h1>

        @if ($errors->any())
            <div class="alert">
                @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember" style="margin-bottom: 0;">Remember me</label>
            </div>

            <button type="submit">Login</button>
        </form>

        <div class="info">
            Default credentials:<br>
            <strong>admin@taskmanager.hu</strong> / <strong>admin123</strong>
        </div>
    </div>
</body>
</html>

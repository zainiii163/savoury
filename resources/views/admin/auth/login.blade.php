<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin login — Savoury Spot</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="container py-5" style="max-width: 28rem;">
        <div class="info-panel">
            <h1 class="h3 mb-3">Admin login</h1>
            <p class="text-secondary">Create the first admin with <code>php artisan savoury:create-admin</code>. Passwords are never seeded.</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="post" action="{{ route('admin.login.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input id="password" type="password" name="password" class="form-control" required>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button class="btn btn-ss-primary w-100" type="submit">Sign in</button>
            </form>
        </div>
    </main>
</body>
</html>

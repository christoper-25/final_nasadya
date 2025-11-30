<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rider Login</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  @vite(['resources/css/login.css', 'resources/js/login.js'])
</head>

<body>
  <div class="overlay"></div>

  <div class="logo-container">
    <img src="{{ asset('videos/header.png') }}" alt="Logo">
  </div>

  <div class="login-card" id="loginCard">
    <div class="drag-indicator"></div>

    {{-- 🚨 Display error message if login fails --}}
    @if ($errors->has('login_error'))
      <div class="alert alert-danger text-center mt-2">
        {{ $errors->first('login_error') }}
      </div>
    @endif

    <form action="{{ route('rider.login.submit') }}" method="POST">
      @csrf
      <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}" required>
      <input type="password" name="password" class="form-control" placeholder="Password" required>
      <button type="submit" class="btn-login mt-2">Login</button>
    </form>

  </div>

</body>
</html>

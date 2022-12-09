<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel Uploads</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

    <!-- Filepond -->
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
</head>
<body>
    <header>
        <!-- Header -->
    </header>

    <main class="d-flex align-items-center justify-content-center min-vh-100 bg-dark text-white">
        {{ $slot }}
    </main>

    <footer>
        <!-- Footer -->
    </footer>

    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>

    @yield('scripts')
</body>
</html>
 
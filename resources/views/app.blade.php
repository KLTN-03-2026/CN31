<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    @routes @vite('resources/js/app.js')
    @inertiaHead

<link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
</head>
<body>
    @inertia
</body>
</html>

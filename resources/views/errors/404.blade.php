<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" sizes="16x16" href="../plugins/images/favicon.png">
    <title>404 - Page Not Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center">
    <section class="w-full max-w-md mx-auto px-4">
        <div class="bg-gray-800 rounded-lg shadow-xl p-8 text-center">
            <h1 class="text-9xl font-bold text-white mb-4">404</h1>
            <h3 class="text-2xl font-semibold text-white uppercase mb-4">Page Not Found!</h3>
            <p class="text-gray-400 mb-8">YOU SEEM TO BE TRYING TO FIND YOUR WAY HOME</p>
            <a href="{{ url('/login') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition-colors duration-200 mb-8">Back to home</a>
            <footer class="text-gray-500 text-sm mt-8">© {{ date('Y') }}</footer>
        </div>
    </section>
</body>
</html>
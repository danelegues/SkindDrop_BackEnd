<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verificado - SkinDrop</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-[#222]">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-[#333] p-8 rounded-xl shadow-2xl text-center">
            <div class="mb-4">
                <svg class="mx-auto h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-500 mb-2">¡Email Verificado!</h2>
            <p class="text-gray-500 mb-6">{{ $message }}</p>
            
            <script>
                // Redirigir después de 2 segundos
                setTimeout(function() {
                    window.location.href = "{{ $redirectUrl }}";
                }, 2000);
            </script>
        </div>
    </div>
</body>
</html>
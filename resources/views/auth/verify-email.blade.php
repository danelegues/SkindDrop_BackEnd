<!DOCTYPE html>
   <html lang="es">
   <head>
       <meta charset="UTF-8">
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <title>Verificación de Email</title>
       <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
   </head>
   <body class="bg-gray-100">
       <div class="min-h-screen flex items-center justify-center">
           <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-md">
               <h1 class="text-2xl font-bold text-center mb-4">Verifica tu dirección de correo electrónico</h1>
               <p class="text-center text-gray-600 mb-4">
                   Antes de continuar, por favor revisa tu correo electrónico para un enlace de verificación.
               </p>
               <p class="text-center text-gray-600">
                   Si no recibiste el correo electrónico, <a href="{{ route('verification.send') }}" class="text-blue-500 hover:underline">haz clic aquí para solicitar otro</a>.
               </p>
           </div>
       </div>
   </body>
   </html>
<!DOCTYPE html>
<html lang="en" style="scroll-behavior: smooth">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="apple-touch-icon" sizes="180x180" href="{{asset('images/favicons/apple-touch-icon.png')}}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{asset('images/favicons/favicon-32x32.png')}}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{asset('images/favicons/favicon-16x16.png')}}">
        <link rel="manifest" href="{{asset('images/favicons/site.webmanifest')}}">
        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
            integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g=="
            crossorigin="anonymous"
            referrerpolicy="no-referrer"
        />
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="//unpkg.com/alpinejs" defer></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            laravel: "#34a1eb",
                        },
                    },
                },
            };
        </script>
        <title>Kétkeréken</title>
        @stack('head')
    </head>
    <body class="min-h-screen flex flex-col">
        <nav class="flex justify-between items-center mb-4 pr-2 sm:pr-4">
            <!-- Logo -->
            <a href="/" class="inline-block flex-shrink-0">
                <img class="w-48 sm:w-56 md:w-64 lg:w-80 xl:w-96" src="{{asset('images/logo.png')}}" alt="logo"/>
            </a>

            <!-- Burger Menu (Mobile only) -->
            <div class="md:hidden" x-data="{ open: false }">
                <button @click="open = !open" class="text-3xl p-2">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <!-- Mobile Menu Links -->
                <ul x-show="open" @click.outside="open = false" 
                    x-transition
                    class="absolute right-2 mt-2 w-56 bg-white shadow-lg rounded-lg z-50">
                    @auth
                        <li>
                            <a href="/profile/{{auth()->id()}}" class="block px-4 py-3 hover:bg-gray-100 border-b border-gray-200">
                                <i class="fa-solid fa-user w-5"></i> Profil
                            </a>
                        </li>
                        <li>
                            <a href="/tour_planning" class="block px-4 py-3 hover:bg-gray-100 border-b border-gray-200">
                                <i class="fa-solid fa-route w-5"></i> Túratervezés
                            </a>
                        </li>
                        <li>
                            <a href="/map" class="block px-4 py-3 hover:bg-gray-100 border-b border-gray-200">
                                <i class="fa-solid fa-map w-5"></i> Interaktív térkép
                            </a>
                        </li>
                        <li>
                            <a href="/forum" class="block px-4 py-3 hover:bg-gray-100 border-b border-gray-200">
                                <i class="fa-solid fa-star w-5"></i> Fórum
                            </a>
                        </li>
                        <li>
                            <a href="/subscription" class="block px-4 py-3 hover:bg-gray-100 border-b border-gray-200">
                                <i class="fa-solid fa-gem w-5"></i> Előfizetés
                            </a>
                        </li>
                        <li>
                            <form class="inline w-full" method="POST" action="/logout">
                                @csrf
                                <button type="submit" class="block px-4 py-3 hover:bg-gray-100 w-full text-left">
                                    <i class="fa-solid fa-door-closed w-5"></i> Kijelentkezés
                                </button>
                            </form>
                        </li>
                    @else
                        <li>
                            <a href="/login" class="block px-4 py-3 hover:bg-gray-100 border-b border-gray-200">
                                <i class="fa-solid fa-arrow-right-to-bracket w-5"></i> Bejelentkezés
                            </a>
                        </li>
                        <li>
                            <a href="/register" class="block px-4 py-3 hover:bg-gray-100">
                                <i class="fa-solid fa-user-plus w-5"></i> Regisztráció
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>

            <!-- Regular Menu (Desktop) -->
            <ul class="hidden md:flex md:flex-wrap items-center gap-2 lg:gap-4 xl:gap-6 text-base lg:text-lg">
                @auth
                <li>
                    <a href="/profile/{{auth()->id()}}" class="flex items-center px-2 lg:px-3 py-2 hover:bg-gray-100 rounded transition-colors whitespace-nowrap">
                        <i class="fa-solid fa-user mr-1 lg:mr-2 text-xl lg:text-base"></i> 
                        <span class="hidden lg:inline">Profil</span>
                    </a>
                </li>
                <li>
                    <a href="/tour_planning" class="flex items-center px-2 lg:px-3 py-2 hover:bg-gray-100 rounded transition-colors whitespace-nowrap">
                        <i class="fa-solid fa-route mr-1 lg:mr-2 text-xl lg:text-base"></i> 
                        <span class="hidden xl:inline">Túratervezés</span>
                        <span class="xl:hidden hidden lg:inline">Túra</span>
                    </a>
                </li>
                <li>
                    <a href="/map" class="flex items-center px-2 lg:px-3 py-2 hover:bg-gray-100 rounded transition-colors whitespace-nowrap">
                        <i class="fa-solid fa-map mr-1 lg:mr-2 text-xl lg:text-base"></i> 
                        <span class="hidden xl:inline">Interaktív térkép</span>
                        <span class="xl:hidden hidden lg:inline">Térkép</span>
                    </a>
                </li>
                <li>
                    <a href="/forum" class="flex items-center px-2 lg:px-3 py-2 hover:bg-gray-100 rounded transition-colors whitespace-nowrap">
                        <i class="fa-solid fa-star mr-1 lg:mr-2 text-xl lg:text-base"></i> 
                        <span class="hidden lg:inline">Fórum</span>
                    </a>
                </li>
                <li>
                    <a href="/subscription" class="flex items-center px-2 lg:px-3 py-2 hover:bg-gray-100 rounded transition-colors whitespace-nowrap">
                        <i class="fa-solid fa-gem mr-1 lg:mr-2 text-xl lg:text-base"></i> 
                        <span class="hidden lg:inline">Előfizetés</span>
                    </a>
                </li>
                <li>
                    <form class="inline" method="POST" action="/logout">
                        @csrf
                        <button type="submit" class="flex items-center px-2 lg:px-3 py-2 hover:bg-gray-100 rounded transition-colors whitespace-nowrap">
                            <i class="fa-solid fa-door-closed mr-1 lg:mr-2 text-xl lg:text-base"></i> 
                            <span class="hidden lg:inline">Kijelentkezés</span>
                        </button>
                    </form>
                </li>
                @else
                <li>
                    <a href="/register" class="flex items-center px-2 lg:px-3 py-2 hover:text-laravel transition-colors whitespace-nowrap">
                        <i class="fa-solid fa-user-plus mr-1 lg:mr-2 text-xl lg:text-base"></i> 
                        <span class="hidden lg:inline">Regisztráció</span>
                    </a>
                </li>
                <li>
                    <a href="/login" class="flex items-center px-2 lg:px-3 py-2 hover:text-laravel transition-colors whitespace-nowrap">
                        <i class="fa-solid fa-arrow-right-to-bracket mr-1 lg:mr-2 text-xl lg:text-base"></i> 
                        <span class="hidden lg:inline">Bejelentkezés</span>
                    </a>
                </li>
                @endauth
            </ul>
        </nav>

        <div class="border-b-4 border-laravel mb-4"></div>
        <main class="flex-1">
            {{$slot}}
        </main>
        <footer class="mt-auto w-full flex items-center justify-center font-bold bg-laravel text-white h-14 opacity-90 text-center">
            <p class="ml-2">Copyright &copy; 2025, All rights reserved</p>
        </footer>
        @stack('scripts')
    </body>
</html>
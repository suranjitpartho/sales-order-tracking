<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Laravel App')</title>
    
    <link rel="stylesheet" href="{{ asset('css/style.css') }}"> <!-- CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Barlow:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet"> <!-- Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- <script src="https://kit.fontawesome.com/e2ee203db1.js" crossorigin="anonymous"></script> FontAwesome Icons -->
</head>
<body>


    <!-- NAVIGATION BAR -->
    
    <nav class="navbar">
        <div class="container">
            <a href="{{ route('tasks.index') }}" class="logo">SALES ORDER TRACKING</a>
            <ul class="nav-links">
                <li><a href="{{ route('dashboard') }}" class="btn">Dashboard</a></li>
                <li><a href="{{ route('tasks.index') }}" class="btn">Orders</a></li>
                <li><a href="{{ route('tasks.create') }}" class="btn">New Order</a></li>
                @auth
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn">Logout</button>
                    </form>
                </li>
                @endauth
            </ul>
        </div>
    </nav>



    <!-- MAIN CONTENT DIV -->
    <main class="container">
        <div class="main-card">
            @yield('content')
        </div>
    </main>



    <!-- Footer -->
    <footer>
        <p>&copy; {{ date('Y') }} | Developed by Suranjit Das | All Rights Reserved.</p>
    </footer>

    @stack('scripts')

</body>
</html>
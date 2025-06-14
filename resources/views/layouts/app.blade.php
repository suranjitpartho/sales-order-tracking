<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Laravel App')</title>
    
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/agent.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Barlow:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet"> <!-- Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>

    <!-- <script src="https://kit.fontawesome.com/e2ee203db1.js" crossorigin="anonymous"></script> FontAwesome Icons -->
</head>
<body>


    <!-- NAVIGATION BAR -->
    
    <nav class="navbar">
        <a href="{{ route('tasks.index') }}" class="logo">SALES ORDER TRACKING</a>
        <div class="navigation-buttons">
            <a href="{{ route('dashboard') }}" class="btn-icon" title="Dashboard"><i class="fa-solid fa-chart-simple"></i></a>
            <a href="{{ route('ai-agent.index') }}" class="btn-icon" title="Ai Agent"><i class="fa-solid fa-robot"></i></a>
            <a href="{{ route('tasks.index') }}" class="btn-icon" title="Orders"><i class="fa-regular fa-file-lines"></i></a>
            <a href="{{ route('tasks.create') }}" class="btn-icon" title="Create"><i class="fa-solid fa-square-plus"></i></a>
            @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-icon" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></button>
            </form>
            @endauth
        </div>
    </nav>



    <!-- MAIN CONTENT -->
    <div class="content-wrapper">
        @yield('content')
    </div>



    <!-- Footer -->
    <footer>
        <p>&copy; {{ date('Y') }} | Suranjit Das</p>
    </footer>

    @stack('scripts')

</body>
</html>
<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.partials.admin.head')
    <title>@yield('title', 'Dashboard') | SMKN 1 BENDO MAGETAN</title>
</head>

<body class="bg-light">
    <div id="db-wrapper">
            @include('layouts.partials.admin.navbar-vertical-guru')
        <div id="page-content">
            @include('layouts.partials.admin.header')
            @yield('content')
        </div>
    </div>

    @include('layouts.partials.admin.scripts')
    @stack('scripts')

</body>

</html>
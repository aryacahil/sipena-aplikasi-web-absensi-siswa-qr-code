<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.partials.admin.head')
    <title>Home | SMKN 1 BENDO MAGETAN</title>
</head>

<body class="bg-light">
    <div id="db-wrapper">
        <!-- navbar vertical -->
            @include('layouts.partials.admin.navbar-vertical-admin')
        <!-- Page content -->
        <div id="page-content">
            @include('layouts.partials.admin.header')
            <!-- Container fluid -->
            @yield('content')
        </div>
    </div>



    <!-- Scripts -->
    @include('layouts.partials.admin.scripts')
    @stack('scripts')



</body>

</html>

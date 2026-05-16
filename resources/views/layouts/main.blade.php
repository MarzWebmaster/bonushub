<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RewardHub Management System</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
    <style>
        body {
            display: flex;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .sidebar {
            width: 250px;
            background-color: #f8f9fa;
            padding: 20px;
            position: fixed;
            height: 100%;
        }
        .sidebar h2 {
            text-align: center;
        }
        .sidebar a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
            margin: 5px 0;
        }
        .sidebar a:hover {
            background-color: #e2e6ea;
        }
        .main-content {
            margin-left: 260px; /* Sidebar width + padding */
            padding: 20px;
            flex-grow: 1;
        }
        .header {
            background-color: #343a40;
            color: white;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header .logo {
            font-size: 24px;
        }
        .header .profile {
            display: flex;
            align-items: center;
        }
        .header .profile img {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            margin-left: 10px;
        }
        .footer {
            text-align: center;
            padding: 10px;
            background-color: #f8f9fa;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">RewardHub</div>
        <div class="profile">
            <span class="notification-icon">🔔</span>
            <img src="path/to/avatar.jpg" alt="Profile Avatar">
        </div>
    </div>
    <div class="sidebar">
        <h2>Navigation</h2>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <a href="{{ route('manage.shop') }}">Manage Shop</a>
        <a href="{{ route('manage.shop.package') }}">Manage Package</a>
        <a href="{{ route('finance') }}">Finance</a>
        <a href="{{ route('settings') }}">Settings</a>
        <a href="{{ route('logout') }}">Logout</a>
    </div>
    <div class="main-content">
        @yield('content')
    </div>
    <div class="footer">
        <p>dev by <a href="https://marztechnology.com.my" target="_blank">marztechnology.com.my</a></p>
    </div>
</body>
</html>

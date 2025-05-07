<?php
require_once '../includes/config.php';
require_once '../includes/authentication.php';

// Ensure user is logged in and is an admin
ensureAdmin();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Admin - <?= $pageTitle ?? 'Dashboard' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 text-white">
            <div class="p-4">
                <h1 class="text-xl font-bold">Hotel Admin</h1>
            </div>
            <nav class="mt-6">
                <a href="index.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white <?= $currentPage === 'dashboard' ? 'bg-gray-700' : '' ?>">
                    Dashboard
                </a>
                <a href="rooms.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white <?= $currentPage === 'rooms' ? 'bg-gray-700' : '' ?>">
                    Rooms
                </a>
                <a href="bookings.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white <?= $currentPage === 'bookings' ? 'bg-gray-700' : '' ?>">
                    Bookings
                </a>
                <a href="users.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white <?= $currentPage === 'users' ? 'bg-gray-700' : '' ?>">
                    Users
                </a>
                <a href="../index.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white mt-6">
                    View Website
                </a>
                <a href="../auth/logout.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white">
                    Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex flex-col gap-4 w-full p-4">
            <!-- Top Bar -->
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800"><?= $pageTitle ?? 'Dashboard' ?></h2>
                    <div class="flex items-center">
                        <span class="text-sm text-gray-600 mr-4">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
                    </div>
                </div>
            </header>
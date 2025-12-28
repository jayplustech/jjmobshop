<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - JJ.MOBISHOP</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="admin-layout">
        <div class="sidebar">
            <h3>JJ.MOBISHOP Admin</h3>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="categories.php">Categories</a>
                <a href="products.php">Products</a>
                <a href="orders.php">Orders</a>
                <a href="users.php">Users</a>
                <a href="profile.php">Profile</a>
                <a href="../logout.php" style="margin-top: 20px; color: #ff6b6b;">Logout</a>
            </nav>
        </div>
        <div class="main-content">

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-800 flex">

<?php include('./sidebar.php'); ?>

<!-- Main Content -->
<div class="main-content  pl-64  flex-1">

<?php
$page_title = "Dashboard";
include('./navbar.php');
?>
    <div class="p-6 ">
        <h1 class="text-2xl font-bold mb-4">Welcome to Faculty Dashboard</h1>
        <div class="bg-white shadow-md rounded-lg p-6">
            <p><strong>Faculty ID:</strong> <?php echo htmlspecialchars($userdata['faculty_id']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user["email"]); ?></p>
        </div>
    </div>
</div>

</body>
</html>

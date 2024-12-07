    <?php
    session_start();

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
        header("Location: ../login.php");
        exit();
    }

    $userdata = $_SESSION['userdata'];
    $user = $_SESSION['user'];

    ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .sidebar {
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
        }
        .sidebar.open {
            transform: translateX(0);
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

<!-- Navbar -->
<nav class="bg-stone-800 p-5">
    <div class="flex justify-end items-center space-x-4">
        <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
            <img 
                class="w-full h-full object-cover" 
                src="https://marwadieducation.edu.in/MEFOnline/handler/getImage.ashx?Id=<?php echo htmlspecialchars($user['username']); ?>" 
                alt="Faculty Image"
                onerror="this.onerror=null; this.src='../assets/images/favicon.png';">
        </div>
        <div class="text-white flex flex-col items-start">
            <span class="font-medium">
                <?php echo($userdata['first_name'] . " " . $userdata['last_name']); ?>
            </span>
            <span class="font-small text-xs">
                <?php echo($userdata['designation']=="hod"?"HOD-ICT":"NO"); ?>
            </span>
        </div>
        <div class='w-2'></div>
        <!-- Icon to open sidebar -->
        <div id="sidebarToggle" class="cursor-pointer text-white text-2xl">
            &#9776; <!-- Hamburger Icon -->
        </div>
    </div>
</nav>

 <!-- Include Sidebar -->
 <?php include('./sidebar.php'); ?>
 
<!-- Body -->
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Faculty Dashboard</h1>
    <div class="bg-white shadow-md rounded-lg p-6">
        <br>
        <p><strong>Faculty:</strong> <?php echo htmlspecialchars($userdata['faculty_id']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user["email"]); ?></p>
    </div>
</div>

<script>
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const closeSidebar = document.getElementById('closeSidebar');

    // Open sidebar
    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.add('open');
        closeSidebar.classList.remove('hidden'); // Show close icon
    });

    // Close sidebar
    closeSidebar.addEventListener('click', closeSidebarHandler);

    // Close sidebar when clicking outside
    document.addEventListener('click', (event) => {
        if (!sidebar.contains(event.target) && event.target !== sidebarToggle) {
            closeSidebarHandler();
        }
    });

    function closeSidebarHandler() {
        sidebar.classList.remove('open');
        closeSidebar.classList.add('hidden'); // Hide close icon
        sidebarToggle.classList.remove('hidden'); // Show hamburger icon
    }
</script>

</body>
</html>

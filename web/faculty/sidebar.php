<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../login.php");
    exit();
}

$userdata = $_SESSION['userdata'];
$user = $_SESSION['user'];

// Check if the image URL is already stored in the session
if (!isset($_SESSION['image_url'])) {
    // Generate and store the image URL in session
    $imageUrl = "https://marwadieducation.edu.in/MEFOnline/handler/getImage.ashx?Id=" . htmlspecialchars($user['username']);
    $_SESSION['image_url'] = $imageUrl;
} else {
    // Use the stored image URL
    $imageUrl = $_SESSION['image_url'];
}
?>
<style>
    .common-radius {
        border-radius: 10px; /* Adjust the radius as needed */
    }
</style>

<div id="sidebar" class="sidebar fixed h-screen w-64 bg-zinc-800 shadow-xl shadow-gray-400 text-white flex flex-col">
    <div class="flex flex-col justify-between h-full">
        <div>
            <div class="p-6 bg-zinc-950 flex flex-row">
                <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center common-radius">
                    <img
                        class="w-full h-full object-cover"
                        src="<?php echo $imageUrl; ?>"
                        alt="Faculty Image"
                        onerror="this.onerror=null; this.src='../assets/images/favicon.png';">
                </div>
                <div class="pl-3">
                    <span class="block text-md font-medium">
                        <?php echo ($userdata['first_name'] . " " . $userdata['last_name']); ?>
                    </span>
                    <span class="block text-sm text-gray-400">
                        <?php
                        echo match ($userdata['designation']) {
                            'hod' => 'HOD-ICT',
                            'ap' => 'Assistant Professor',
                            'tp' => 'Trainee Professor',
                            default => 'Null',
                        };
                        ?>
                    </span>
                </div>
            </div>
            <ul>
                <div class="text-sm px-2">
                    <!-- Existing Menu Items -->
                <?php
                $radious = "rounded-lg";  // Apply common-radius class here
                ?>
                <li>
                    <a href="dashboard.php">
                        <div class="w-full h-10 mt-2 flex <?php echo $radious; ?> items-center px-5 text-white transition bg-transparent hover:bg-red-600 active:bg-red-900">
                            Dashboard
                        </div>
                    </a>
                </li>

                <?php if ($userdata['designation'] === 'hod'): ?>
                <li>
                    <a href="holiday.php">
                        <div class="w-full h-10 flex <?php echo $radious; ?> items-center px-5 text-white transition bg-transparent hover:bg-red-600 active:bg-red-900">
                            Holidays
                        </div>
                    </a>
                </li>
                <?php endif; ?>

                <li>
                    <a href="student_search_page.php">
                        <div class="w-full h-10 flex items-center px-5 text-white transition bg-transparent hover:bg-red-600 active:bg-red-900 <?php echo $radious; ?>">
                            Student Search
                        </div>
                    </a>
                </li>

                <li>
                    <a href="add_zoom_meeting.php">
                        <div class="w-full h-10 flex items-center px-5 text-white transition bg-transparent hover:bg-red-600 active:bg-red-900 <?php echo $radious; ?>">
                            Parents Meeting
                        </div>
                    </a>
                </li>

                <li>
                    <a href="total_attendance_sheet.php">
                        <div class="w-full h-10 flex items-center px-5 text-white transition bg-transparent hover:bg-red-600 active:bg-red-900 <?php echo $radious; ?>">
                            Students Total Attendance
                        </div>
                    </a>
                </li>

                <?php if ($userdata['designation'] === 'hod'): ?>
                <li>
                    <a href="manage_class.php">
                        <div class="w-full h-10 flex items-center px-5 text-white transition bg-transparent hover:bg-red-600 active:bg-red-900 <?php echo $radious; ?>">
                            Manage Class
                        </div>
                    </a>
                </li>
                <?php endif; ?>

                <!-- New Placement Dropdown -->
                <?php if ($userdata['designation'] === 'hod'): ?>
                    <li class="relative">
                        <div 
                            id="placement-box"
                            class="transition-all w-full h-10 flex items-center justify-between px-5 text-white transition bg-transparent hover:bg-red-600 active:bg-red-900 cursor-pointer <?php echo $radious; ?>" 
                            onclick="toggleDropdown('placement-dropdown', 'placement-box', 'arrow-icon')">
                            <span>Placement</span>
                            <span id="arrow-icon" class="transition-transform text-xs ">&#9660;</span> <!-- Smaller down arrow -->
                        </div>
                        <ul id="placement-dropdown" class="pl-5 hidden opacity-0 transform translate-y-5 transition-all duration-300 bg-zinc-800 text-white shadow-lg z-10 w-full">
                            <li>
                                <a href="companies.php" class="rounded-full block px-5 py-2 my-1 hover:bg-red-600 hover:ml-5 transition-all <?php echo $radious; ?>">Companies</a>
                            </li>
                            <li>
                                <a href="campus_drive.php" class="rounded-full block px-5 py-2 my-1 hover:bg-red-600 hover:ml-5 transition-all <?php echo $radious; ?>">Campus Drive</a>
                            </li>   
                            <li>
                                <a href="placed_students.php" class="rounded-full block px-5 py-2 my-1 hover:bg-red-600 hover:ml-5 transition-all <?php echo $radious; ?>">Placed Students</a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
                </div>

            <script>
                function toggleDropdown(dropdownId, boxId, arrowId) {
                    const dropdown = document.getElementById(dropdownId);
                    const box = document.getElementById(boxId);
                    const arrow = document.getElementById(arrowId);

                    // Toggle dropdown visibility and animation
                    if (dropdown.classList.contains('hidden')) {
                        dropdown.classList.remove('hidden');
                        setTimeout(() => {
                            dropdown.classList.remove('opacity-0', 'translate-y-5');
                            dropdown.classList.add('opacity-100', 'translate-y-0');
                        }, 10); // Allow DOM update

                        // Rotate arrow up
                        arrow.style.transform = "rotate(180deg)";
                    } else {
                        dropdown.classList.add('opacity-0', 'translate-y-5');
                        dropdown.classList.remove('opacity-100', 'translate-y-0');
                        setTimeout(() => {
                            dropdown.classList.add('hidden');
                        }, 300); // Matches transition duration

                        // Rotate arrow down
                        arrow.style.transform = "rotate(0deg)";
                    }

                    // Add or remove border for the clicked box
                    box.classList.toggle('bg');
                    box.classList.toggle('bg-red-600');
                }
            </script>
        </div>

        <!-- Logout Button -->
        <form action="../logout.php" method="post">
            <button type="submit" class="w-full h-10 bg-red-600 text-white text-center hover:bg-red-700 transition">
                Logout
            </button>
        </form>
    </div>
</div>

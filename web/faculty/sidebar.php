<?php
include('../../api/db/db_connection.php'); 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../login.php");
    exit();
}

$userdata = $_SESSION['userdata'];
$user = $_SESSION['user'];

if (!isset($_SESSION['image_url'])) {
    $imageUrl = "https://marwadieducation.edu.in/MEFOnline/handler/getImage.ashx?Id=" . htmlspecialchars($user['username']);
    $_SESSION['image_url'] = $imageUrl;
} else {
    $imageUrl = $_SESSION['image_url'];
}

$pending_leave = 0;
$sql = "SELECT COUNT(*) AS pending_leave FROM leave_info WHERE leave_status='pending'";
$leaveresult = $conn->query($sql);
if ($leaveresult->num_rows > 0) {
    $row = $leaveresult->fetch_assoc();
    $pending_leave = $row['pending_leave'];
}

// Define color variable (just the color name)
$color = "cyan";
?>

<style>
    .common-radius {
        border-radius: 10px;
    }
</style>

<div id="sidebar" class="sidebar fixed h-screen bg-white w-64 border-r-2 text-grey-500 flex flex-col">
    <div class="flex flex-col justify-between h-full">
        <div>
        <a href="dashboard.php">
           <img src="../assets/images/ict_logo.png" class="mt-3 w-[200px] mx-auto block">
        </a>
           <br>
            <ul>
                <div class="text-sm bg-white px-2">
                    <?php
                    $radious = "rounded-lg"; // common-radius 
                    ?>
                    <li>
                        <a href="dashboard.php">
                            <div class="w-full h-10 mt-2 flex <?php echo $radious; ?> items-center px-5 text-grey-500 transition bg-transparent hover:bg-<?php echo $color; ?>-600 hover:text-white active:bg-<?php echo $color; ?>-900">
                                Dashboard
                            </div>
                        </a>
                    </li>

                    <?php if ($userdata['designation'] === 'hod'): ?>
                    <li>
                        <a href="holiday.php">
                            <div class="w-full h-10 flex <?php echo $radious; ?> items-center px-5 text-grey-500 transition bg-transparent hover:bg-<?php echo $color; ?>-600 hover:text-white active:bg-<?php echo $color; ?>-900">
                                Holidays
                            </div>
                        </a>
                    </li>
                    <?php endif; ?>

                    <li>
                        <a href="add_zoom_meeting.php">
                            <div class="w-full h-10 flex items-center px-5 text-grey-500 transition bg-transparent hover:bg-<?php echo $color; ?>-600 hover:text-white active:bg-<?php echo $color; ?>-900 <?php echo $radious; ?>">
                                Parents Meeting
                            </div>
                        </a>
                    </li>

                    <li>
                        <a href="total_attendance_sheet.php">
                            <div class="w-full h-10 flex items-center px-5 text-grey-500 transition bg-transparent hover:bg-<?php echo $color; ?>-600 hover:text-white active:bg-<?php echo $color; ?>-900 <?php echo $radious; ?>">
                                Students Total Attendance
                            </div>
                        </a>
                    </li>

                    <?php if ($userdata['designation'] === 'hod'): ?>
                    <li>
                        <a href="students_leave.php">
                            <div class="w-full h-10 flex items-center px-5 text-grey-500 transition bg-transparent hover:bg-<?php echo $color; ?>-600 hover:text-white active:bg-<?php echo $color; ?>-900 <?php echo $radious; ?> justify-between group">
                                Students Leave Request
                                <?php if ($pending_leave > 0): ?>
                                    <div class="bg-<?php echo $color; ?>-600 text-grey-500 px-3 py-1 rounded-md transition-colors duration-300 group-hover ">
                                        <?php echo $pending_leave; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($userdata['designation'] === 'hod'): ?>
                    <li>
                        <a href="manage_class.php">
                            <div class="w-full h-10 flex items-center px-5 text-grey-500 transition bg-transparent hover:bg-<?php echo $color; ?>-600 hover:text-white active:bg-<?php echo $color; ?>-900 <?php echo $radious; ?>">
                                Manage Class
                            </div>
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Placement Dropdown -->
                    <?php if ($userdata['designation'] === 'hod'): ?>
                    <li class="relative">
                        <div 
                            id="placement-box"
                            class="transition-all w-full h-10 flex items-center justify-between px-5 text-grey-500 transition bg-transparent hover:bg-<?php echo $color; ?>-600 hover:text-white active:bg-<?php echo $color; ?>-900 cursor-pointer <?php echo $radious; ?>" 
                            onclick="toggleDropdown('placement-dropdown', 'placement-box', 'arrow-icon')">
                            <span>Placement</span>
                            <span id="arrow-icon" class="transition-transform text-xs">▼</span>
                        </div>
                        <ul id="placement-dropdown" class="pl-5 hidden opacity-0 transform translate-y-5 transition-all duration-300 text-grey-500 z-10 w-full">
                            <li>
                                <a href="companies.php" class="rounded-full block px-5 py-2 my-1 hover:bg-<?php echo $color; ?>-600 hover:text-white hover:ml-5 transition-all <?php echo $radious; ?>">Companies</a>
                            </li>
                            <li>
                                <a href="campus_drive.php" class="rounded-full block px-5 py-2 my-1 hover:bg-<?php echo $color; ?>-600 hover:text-white hover:ml-5 transition-all <?php echo $radious; ?>">Campus Drive</a>
                            </li>   
                            <li>
                                <a href="placed_students.php" class="rounded-full block px-5 py-2 my-1 hover:bg-<?php echo $color; ?>-600 hover:text-white hover:ml-5 transition-all <?php echo $radious; ?>">Placed Students</a>
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
                            // Highlight box with red background and white text
                            box.classList.add('bg-<?php echo $color; ?>-600', 'text-white');
                        } else {
                            dropdown.classList.add('opacity-0', 'translate-y-5');
                            dropdown.classList.remove('opacity-100', 'translate-y-0');
                            setTimeout(() => {
                                dropdown.classList.add('hidden');
                            }, 300); // Matches transition duration

                            // Rotate arrow down
                            arrow.style.transform = "rotate(0deg)";
                            // Remove highlight
                            box.classList.remove('bg-<?php echo $color; ?>-600', 'text-white');
                        }
                    }
                </script>
            </ul>
        </div>
    </div>
</div>
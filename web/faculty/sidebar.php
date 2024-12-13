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
<div id="sidebar" class="sidebar fixed h-screen w-64 bg-zinc-800 shadow-xl shadow-gray-400 text-white flex flex-col">
    <div class="flex flex-col justify-between h-full">
        <div>
            <div class="p-6 bg-zinc-950 flex flex-row">
                <div class="w-12 h-12 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
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

                <li>
                    <a href="dashboard.php">
                        <div class="w-full h-12 flex items-center px-5 text-white transition bg-transparent hover:bg-red-600 active:bg-red-900">
                            Dashboard
                        </div>
                    </a>
                </li>

                <?php if ($userdata['designation'] === 'hod'): ?>
                    <li>
                        <a href="holiday.php">
                            <div class="w-full h-12 flex items-center px-5 text-white transition bg-transparent hover:bg-red-600 active:bg-red-900">
                                Holidays
                            </div>
                        </a>
                    </li>
                <?php endif; ?>

                <li>
                    <a href="student_search_page.php">
                        <div class="w-full h-12 flex items-center px-5 text-white transition bg-transparent hover:bg-red-600 active:bg-red-900">
                            Student Search
                        </div>
                    </a>
                </li>

                <li>
                    <a href="add_zoom_meeting.php">
                        <div class="w-full h-12 flex items-center px-5 text-white transition bg-transparent hover:bg-red-600 active:bg-red-900">
                            Parents Meeting
                        </div>
                    </a>
                </li>

                <li>
                    <a href="total_attendance_sheet.php">
                        <div class="w-full h-12 flex items-center px-5 text-white transition bg-transparent hover:bg-red-600 active:bg-red-900">
                            Students Total Attendance
                        </div>
                    </a>
                </li>


            </ul>
        </div>

        <!-- Logout Button -->
        <form action="../logout.php" method="post">
            <button type="submit" class="w-full h-12 bg-red-600 text-white text-center hover:bg-red-700 transition">
                Logout
            </button>
        </form>
    </div>
</div>
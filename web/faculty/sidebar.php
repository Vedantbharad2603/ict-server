<div id="sidebar" class="fixed top-0 right-0 w-72 h-full bg-white shadow-2xl sidebar">
    <!-- Close Icon -->
    <div 
        id="closeSidebar" 
        class="absolute -left-10 top-4 bg-red-600 h-10 w-10 text-center cursor-pointer text-white rounded-l-xl text-2xl hidden transition">
        &times; <!-- Close (X) Icon -->
    </div>
    <div class="bg-zinc-700 h-full flex flex-col justify-between">
        <div>
            <div class="p-6 bg-zinc-800">
                <img src="../assets/images/mu_logo_white.png" class="w-56 top-4 right-4">
            </div>
            <ul>
                <li>
                    <a href="dashboard.php">
                        <div class="w-full h-12 flex items-center px-5 text-white transition bg-transparent hover:bg-red-600 active:bg-red-900">
                            Dashboard
                        </div>
                    </a>
                </li>
                <li>
                    <a href="total_attendance_sheet.php">
                        <div class="w-full h-12 flex items-center px-5 text-white transition bg-transparent hover:bg-red-600 active:bg-red-900">
                            Student Total Attendance
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="w-full h-12 flex items-center px-5 text-white transition bg-transparent hover:bg-red-600 active:bg-red-900">
                            Option 3
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

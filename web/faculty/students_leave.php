<?php
include('../../api/db/db_connection.php');

$leaves_query = "SELECT li.*, CONCAT(si.first_name, ' ', si.last_name) AS student_name  
                FROM leave_info li  
                JOIN student_info si ON li.student_info_id = si.id  
                ORDER BY FIELD(li.leave_status, 'pending', 'approved', 'declined')";
$leaves_result = mysqli_query($conn, $leaves_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students Leave</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 flex h-screen overflow-hidden">
            <?php include('./sidebar.php'); ?>
            <div class="main-content pl-64 flex-1 ml-1/6 overflow-y-auto">
            <?php
            $page_title = "Students Leave";
            include('./navbar.php');
            ?>

            <div class="container mx-auto p-5">
                <!-- Search Bar -->
                <input type="text" id="search" class=" shadow border-2 pl-4 p-2 rounded-full w-1/2" placeholder="Search Students..." onkeyup="searchLeaves()">
            </div>

            <div id="leaves-grid" class="grid grid-cols-1 gap-3 p-5">
                <?php while ($leave = mysqli_fetch_assoc($leaves_result)): ?>
                    <div class="leave-item bg-white border <?php echo ($leave['leave_status'] == 'pending') ? 'border-2 border-gray-400' : 'border-gray-300'; ?>  rounded-lg pl-5 p-2 hover:bg-cyan-600 hover:pl-10 hover:text-white hover:shadow-2xl transition-all" onclick="window.location.href='student_leave_details.php?leave_id=<?php echo $leave['id']; ?>'">
                    <div class="flex justify-between items-center cursor-pointer toggle-rounds" data-leave-id="<?php echo $leave['id']; ?>">
                        <div class="flex items-center space-x-2 <?php echo ($leave['leave_status'] == 'pending') ? 'font-bold' : ''; ?>">
                            <h2 class="text-sm mr-2"><?php echo $leave['student_name']; ?></h2>
                            <span>- - - - -</span>
                            <span><?php echo date("d/m/Y", strtotime($leave['created_at'])); ?></span>
                            <span>-</span>
                            <span><?php echo date("g:i A", strtotime($leave['created_at'])); ?></span>
                            <?php  
                                $bgColor = ($leave['leave_status'] == "pending") ? "bg-yellow-500" : 
                                        (($leave['leave_status'] == "approved") ? "bg-green-500" : "bg-red-500");
                            ?>
                            <span class="p-1 px-2 <?php echo $bgColor; ?> w-auto font-bold text-sm rounded-full text-white">
                                <?php echo strtoupper($leave['leave_status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <script>
                 // Real-time search function for leaves
            function searchLeaves() {
                const searchInput = document.getElementById('search').value.toLowerCase();
                const leaves = document.querySelectorAll('.leave-item');
                
                leaves.forEach(leave => {
                    const companyName = leave.querySelector('h2').textContent.toLowerCase();
                    
                    if (companyName.includes(searchInput)) {
                        leave.style.display = '';
                    } else {
                        leave.style.display = 'none';
                    }
                });
            }
        </script>
</body>
</html>

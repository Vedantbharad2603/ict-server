<?php
include('../../api/db/db_connection.php');

// Get campus_drive_info_id and company name from the URL
$drive_id = isset($_GET['drive_id']) ? intval($_GET['drive_id']) : null;
$company = isset($_GET['company']) ? mysqli_real_escape_string($conn, $_GET['company']) : null;

if (!$drive_id || !$company) {
    die("<script>
            alert('Invalid drive ID or company name.');
            window.history.back();
        </script>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Students List</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800 flex h-screen overflow-hidden">
    <?php include('./sidebar.php'); ?>
    <div class="main-content pl-64 flex-1 ml-1/6 overflow-y-auto">
        <?php
        $page_title = "$company registered students list";
        include('./navbar.php');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['student_id'], $_POST['round_id'], $_POST['status'], $_POST['drive_id'])) {
                $student_id = intval($_POST['student_id']);
                $round_id = intval($_POST['round_id']);
                $status = mysqli_real_escape_string($conn, $_POST['status']);
                $drive_id = intval($_POST['drive_id']);
        
                if (!empty($student_id) && !empty($round_id) && !empty($status) && !empty($drive_id)) {
                    // Check if the record exists
                    $check_query = "SELECT id FROM student_round_info 
                                    WHERE student_info_id = $student_id 
                                    AND campus_placement_info_id = $drive_id";
                    $check_result = mysqli_query($conn, $check_query);
        
                    if (mysqli_num_rows($check_result) > 0) {
                        // Update existing record
                        $update_query = "UPDATE student_round_info 
                                         SET status = '$status', company_round_info_id = $round_id
                                         WHERE student_info_id = $student_id  
                                         AND campus_placement_info_id = $drive_id";
        
                        if (mysqli_query($conn, $update_query)) {
                            echo "<script>
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: 'Round status updated successfully!'
                                    }).then(() => {
                                        window.location.href = 'company_students_list.php?drive_id=$drive_id&company=$company';
                                    });
                                  </script>";
                        } else {
                            echo "<script>
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Failed to update round status. Please try again.'
                                    });
                                  </script>";
                        }
                    } else {
                        // Insert new record
                        $insert_query = "INSERT INTO student_round_info (student_info_id, campus_placement_info_id, company_round_info_id, status) 
                                         VALUES ($student_id, $drive_id, $round_id, '$status')";
        
                        if (mysqli_query($conn, $insert_query)) {
                            echo "<script>
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: 'Round status added successfully!'
                                    }).then(() => {
                                        window.location.href = 'company_students_list.php?drive_id=$drive_id&company=$company';
                                    });
                                  </script>";
                        } else {
                            echo "<script>
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Failed to add round status. Please try again.'
                                    });
                                  </script>";
                        }
                    }
                } else {
                    echo "<script>
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'All fields are required!'
                            });
                          </script>";
                }
            } else {
                echo "<script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Invalid request!'
                        });
                      </script>";
            }
        }
        
        ?>

        <div class="p-6">
           <div class="mb-4">
                <a href="javascript:history.back()" class="text-white bg-gray-700 p-2 px-5 rounded-full mb-4 hover:px-7 inline-block transition-all">
                    <i class="fa-solid fa-angle-left"></i> Back
                </a>
               <input type="text" id="search" class="drop-shadow-md border-2 ml-10 pl-4 p-2 rounded-full w-1/2" placeholder="Search Students..." onkeyup="searchTable()">
           </div>

           <form id="student-table-form" class="rounded-lg" method="POST">
               <table id="student-table" class="min-w-full bg-white border drop-shadow-md border-gray-300 rounded-lg">
                   <thead>
                       <tr>
                           <th class="border px-2 py-2">No</th>
                           <th class="border px-4 py-2">Enrollment no.</th>
                           <th class="border px-4 py-2">Student Name</th>
                           <th class="border px-4 py-2">Registered nn</th>
                           <th class="border px-4 py-2">Round Status</th>
                           <th class="border px-4 py-2">Round Action</th>
                       </tr>
                   </thead>
                   <tbody>
                       <?php
                       $total_rounds_query = "SELECT COUNT(*) AS total_rounds FROM company_rounds_info WHERE campus_placement_info_id = $drive_id";
                       $total_rounds_result = mysqli_query($conn, $total_rounds_query);

                       if ($total_rounds_result) {
                           $total_rounds = mysqli_fetch_assoc($total_rounds_result)['total_rounds'];

                           $query = "SELECT si.id AS student_id, CONCAT(si.first_name, ' ', si.last_name) AS student_name,enrollment_no, pse.datetime 
                                     FROM placement_student_enroll pse
                                     JOIN student_info si ON pse.student_info_id = si.id
                                     WHERE pse.campus_drive_info_id = $drive_id";
                           $result = mysqli_query($conn, $query);

                           if (mysqli_num_rows($result) > 0) {
                               $counter = 1;
                               while ($row = mysqli_fetch_assoc($result)) {
                                   $datetime = new DateTime($row['datetime']);
                                   $formatted_datetime = $datetime->format('d/m/Y - g:i A');

                                   $student_id = $row['student_id'];
                                   $round_query = "CALL studentRounds($student_id, $drive_id);";

                                   if (mysqli_multi_query($conn, $round_query)) {
                                       do {
                                           if ($result_set = mysqli_store_result($conn)) {
                                               $progress_bar = '<div class="flex gap-2 items-center">';
                                               while ($round = mysqli_fetch_assoc($result_set)) {
                                                   $color = ($round['status'] === 'pass') ? 'bg-green-500 text-white' : 
                                                            (($round['status'] === 'reject') ? 'bg-red-500 text-white' : 'bg-gray-300 text-black');
                                                   $progress_bar .= "<div class='relative flex items-center'>
                                                        <div class='w-20 h-6 hover:scale-105 rounded-full cursor-pointer $color transition-all' 
                                                            title='{$round['round_name']}'>
                                                            <div class='text-sm text-center'>
                                                                Round {$round['round_index']}
                                                            </div>
                                                        </div>
                                                    </div>";
                                               }
                                               $progress_bar .= '</div>';

                                               echo "<tr>
                                                       <td class='border px-1 py-2 text-center'>{$counter}</td>
                                                       <td class='border px-4 py-2 text-center'>{$row['enrollment_no']}</td>
                                                       <td class='border px-4 py-2'>{$row['student_name']}</td>
                                                       <td class='border px-4 py-2 text-center'>{$formatted_datetime}</td>
                                                       <td class='border px-4 py-2'>{$progress_bar}</td>
                                                       <td class='border px-4 py-2 text-center'>
                                                            <button type='button' onclick='openEditPopup({$row['student_id']}, \"{$row['student_name']}\", $drive_id)' class='text-blue-500 mr-2'>Edit</button>
                                                       </td>
                                                     </tr>";
                                               mysqli_free_result($result_set);
                                           }
                                       } while (mysqli_next_result($conn));
                                   }
                                   $counter++;
                               }
                           } else {
                               echo "<tr><td colspan='5' class='border px-4 py-2 text-center'>No students registered</td></tr>";
                           }
                       } else {
                           echo "<tr><td colspan='5' class='border px-4 py-2 text-center'>Error fetching rounds data</td></tr>";
                       }
                       ?>
                   </tbody>
               </table>
           </form>

           <div id="popup-modal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden flex justify-center items-center z-50">
               <div class="bg-white rounded-lg p-6 w-96">
                   <h2 id="popup-title" class="text-xl font-bold mb-4">Update Rounds</h2>
                   <form id="popup-form" method="POST">
                       <input type="hidden" name="student_id" id="student_id">
                       <input type="hidden" name="drive_id" id="drive_id">

                       <div class="mb-4">
                           <input type="text" disabled id="student_name" name="student_name" class="border rounded p-2 w-full" required>
                       </div>

                       <div class="mb-4">
                           <label for="round_id" class="block text-sm font-medium mb-1">Select Round</label>
                           <select id="round_id" name="round_id" class="border rounded p-2 w-full" required>
                               <?php
                               $round_query = "SELECT * FROM company_rounds_info WHERE campus_placement_info_id = $drive_id ORDER BY round_index";
                               $round_result = mysqli_query($conn, $round_query);

                               if (mysqli_num_rows($round_result) > 0) {
                                   while ($round_row = mysqli_fetch_assoc($round_result)) {
                                       echo "<option value='" . $round_row['id'] . "'>" . $round_row['round_index'] . " - " . $round_row['round_name'] . "</option>";
                                   }
                               } else {
                                   echo "<option value=''>No rounds available</option>";
                               }
                               ?>
                           </select>
                       </div>

                       <div class="mb-4">
                           <label for="status" class="block text-sm font-medium mb-1">Select Status</label>
                           <select id="status" name="status" class="border rounded p-2 w-full" required>
                               <option value="pass">Pass</option>
                               <option value="reject">Reject</option>
                           </select>
                       </div>

                       <div class="flex justify-end gap-4">
                           <button type="button" onclick="closePopup()" class="pl-5 pr-5 bg-gray-500 text-white p-2 rounded-full">Cancel</button>
                           <button type="submit" id="popup-submit" class="pl-6 pr-6 bg-cyan-500 text-white p-2 rounded-full">Save</button>
                       </div>
                   </form>
               </div>
           </div>
       </div>
    </div>
    <script>
        function openEditPopup(studentId, studentName, driveId) {
            document.getElementById('student_id').value = studentId;
            document.getElementById('student_name').value = studentName;
            document.getElementById('drive_id').value = driveId;
            document.getElementById('popup-modal').classList.remove('hidden');
        }

        function closePopup() {
            document.getElementById('popup-modal').classList.add('hidden');
        }

        function searchTable() {
            const searchInput = document.getElementById('search').value.toLowerCase();
            const rows = document.querySelectorAll('#student-table tbody tr');
            rows.forEach(row => {
                const studentName = row.cells[1].textContent.toLowerCase();
                if (studentName.includes(searchInput)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>

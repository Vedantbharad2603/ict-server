<?php
include('../../api/db/db_connection.php');

// Fetch all batches
$batches_query = "SELECT * FROM batch_info";
$batches_result = mysqli_query($conn, $batches_query);

// Fetch the current year
$current_year = date("Y");  

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placed Students</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 flex h-screen overflow-hidden">
    <?php include('./sidebar.php'); ?>
    <div class="main-content pl-64 flex-1 ml-1/6 overflow-y-auto">
    <?php
        $page_title = "Placed Students";
        include('./navbar.php');

        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['placement_id'])) {
            $placement_id = intval($_POST['placement_id']);
        
            $delete_query = "DELETE FROM placed_student_info WHERE id = ?";
            $stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt, "i", $placement_id);
        
            if (mysqli_stmt_execute($stmt)) {
                echo "<script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Student record deleted successfully.',
                            confirmButtonColor: '#3085d6'
                        }).then(() => {
                            window.location.href = 'placed_students.php';
                        });
                      </script>";
            } else {
                echo "<script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to delete student record.',
                            confirmButtonColor: '#d33'
                        }).then(() => {
                            window.history.back();
                        });
                      </script>";
            }
        
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            exit;
        }
        ?>
        <div class="p-6">
            <div class="mb-4">
                <button onclick="openAddEditPopup()" class="drop-shadow-md bg-cyan-500 px-6 hover:px-8 text-white p-2 hover:bg-cyan-600 rounded-full mb-6 transition-all">Add Student</button>
                <input type="text" id="search" class="drop-shadow-md border-2 ml-10 pl-4 p-2 rounded-full w-1/2" placeholder="Search Students/Companies..." onkeyup="searchTable()">
                <!-- Batch Dropdown -->
                 <select id="batchDropdown" class="ml-10 drop-shadow-md border-2 px-5 p-2 rounded-xl mb-4" onchange="fetchCompaniesByBatch()">
                    <?php 
                    // Loop through batches and set the default option if the batch_end_year matches current year
                    while ($batch = mysqli_fetch_assoc($batches_result)):
                        $selected = ($batch['batch_end_year'] == $current_year) ? "selected" : "";
                    ?>
                        <option value="<?php echo $batch['id']; ?>" <?php echo $selected; ?>>
                            <?php echo $batch['batch_start_year'] . '-' . $batch['batch_end_year']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <table id="company-table" class="min-w-full bg-white border-2 border-gray-300">
                <thead>
                    <tr>
                        <th class="border-2 border-gray-300 bg-gray-700 text-white px-4 py-2">No</th>
                        <th class="border-2 border-gray-300 bg-gray-700 text-white px-4 py-2">Student Name</th>
                        <th class="border-2 border-gray-300 bg-gray-700 text-white px-4 py-2">Company Name</th>
                        <th class="border-2 border-gray-300 bg-gray-700 text-white px-4 py-2">Package (LPA)</th>
                        <th class="border-2 border-gray-300 bg-gray-700 text-white px-4 py-2">Date</th>
                        <th class="border-2 border-gray-300 bg-gray-700 text-white px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT psi.id AS placement_id, psi.*, CONCAT(si.first_name, ' ', si.last_name) AS student_name, ci.company_name, si.batch_info_id
                              FROM placed_student_info psi 
                              JOIN student_info si ON psi.student_info_id = si.id
                              JOIN company_info ci ON psi.company_info_id = ci.id
                              ORDER BY student_name, placement_id";
                    $result = mysqli_query($conn, $query);

                    $studentCounts = [];  // Track occurrences of student names

                    // Count occurrences of each student
                    while ($row = mysqli_fetch_assoc($result)) {
                        $student_name = $row['student_name'];
                        if (!isset($studentCounts[$student_name])) {
                            $studentCounts[$student_name] = 1;
                        } else {
                            $studentCounts[$student_name]++;
                        }
                        $rows[] = $row;
                    }

                    $counter = 1;
                    $renderedStudents = []; // Track already rendered students

                    foreach ($rows as $row) {
                        echo "<tr>";

                        // Merge "No." and "Student Name" columns
                        if (!isset($renderedStudents[$row['student_name']])) {
                            echo "<td class='border-b-2 border border-gray-300 px-4 py-2 text-center' rowspan='{$studentCounts[$row['student_name']]}'>{$counter}</td>";
                            echo "<td class='border-b-2 border border-gray-300 px-4 py-2' rowspan='{$studentCounts[$row['student_name']]}'>{$row['student_name']}</td>";
                            $renderedStudents[$row['student_name']] = true;
                            $counter++;
                        }

                        echo "<td class='border-b-2 border border-gray-300 px-4 py-2'>{$row['company_name']}</td>";

                        if ($row['package_start'] != $row['package_end']) {
                            echo "<td class='border-b-2 border border-gray-300 px-4 py-2 text-center'>{$row['package_start']} - {$row['package_end']}</td>";
                        } else {
                            echo "<td class='border-b-2 border border-gray-300 px-4 py-2 text-center'>{$row['package_start']}</td>";
                        }

                        echo "<td class='border-b-2 border border-gray-300 px-4 py-2 text-center'>" . ($row['date'] ? date("d-m-Y", strtotime($row['date'])) : " - ") . "</td>";

                        echo "<td class='border-b-2 border border-gray-300 px-4 py-2 text-center'>
                                <button type='button' class='text-red-500 mr-2' onclick='confirmDelete({$row['placement_id']})'>Delete</button>
                              </td>";

                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function confirmDelete(placementId) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = document.createElement("form");
                    form.method = "POST";
                    form.action = "";
                    let input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "placement_id";
                    input.value = placementId;
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function searchTable() {
            const searchInput = document.getElementById('search').value.toLowerCase();
            const rows = document.querySelectorAll('#company-table tbody tr');
            rows.forEach(row => {
                const studentName = row.cells[1]?.textContent.toLowerCase() || "";
                const companyName = row.cells[2]?.textContent.toLowerCase() || "";
                const packageLPA = row.cells[3]?.textContent.toLowerCase() || "";
                const date = row.cells[4]?.textContent.toLowerCase() || "";
                if (studentName.includes(searchInput) || companyName.includes(searchInput)||packageLPA.includes(searchInput) || date.includes(searchInput)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>





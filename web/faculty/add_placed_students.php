<?php
session_start();
include('../../api/db/db_connection.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch company names for the dropdown
function getCompanies() {
    global $conn;
    $query = "SELECT id, company_name FROM company_info ORDER BY company_name ASC";
    $result = mysqli_query($conn, $query);
    $companies = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $companies[] = $row;
    }
    return $companies;
}

$companies = getCompanies();

// Fetch the current year
$current_year = date("Y");

// Fetch all batches for the dropdown
function getBatches() {
    global $conn;
    $query = "SELECT id, batch_start_year, batch_end_year FROM batch_info ORDER BY batch_start_year ASC";
    $result = mysqli_query($conn, $query);
    $batches = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $batches[] = $row;
    }
    return $batches;
}

$batches = getBatches();

// Fetch students for the selected batch
function getStudentsByBatch($batch_id) {
    global $conn;
    $query = "SELECT CONCAT(si.first_name, ' ', si.last_name) AS student_name, si.id AS student_id 
              FROM placement_support_enroll pse
              JOIN student_info si ON pse.student_info_id = si.id
              WHERE si.batch_info_id = $batch_id";
    $result = mysqli_query($conn, $query);
    $students = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $students[] = $row;
    }
    echo json_encode($students); // Ensure this data is returned
    exit();
}

// Handle AJAX request for students when batch is selected
if (isset($_POST['batch_id'])) {
    $batch_id = $_POST['batch_id'];
    getStudentsByBatch($batch_id);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_id = $_POST['company_id'];
    $date = $_POST['date'];
    $package_start = $_POST['package_start'];
    $package_end = !empty($_POST['package_end']) ? $_POST['package_end'] : $package_start; // Use package_start if package_end is empty

    // Get the student IDs from the hidden input
    $student_ids = explode(',', $_POST['student_ids']); // Convert the comma-separated string to an array

    // Prepare the SQL statement
    $query = "INSERT INTO placed_student_info (student_info_id, company_info_id, package_start, package_end, date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    foreach ($student_ids as $student_id) {
        // Bind parameters and execute the statement
        if (!$stmt->bind_param("iidds", $student_id, $company_id, $package_start, $package_end, $date)) {
            die("Binding parameters failed: " . $stmt->error);
        }
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
    }
    // Redirect after successful insertion
    echo "<script>
            Swal.fire({
                title: 'Saved!',
                text: 'Placed students added successfully.',
                icon: 'success'
            }).then(function() {
                window.location.href = 'placed_students.php';
            });
        </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Placed Students</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            const selectedBatchId = $('select[name="batch_id"]').val();
            console.log("Selected Batch ID on page load: " + selectedBatchId); // Print the selected batch ID in the console

            // Global array to store selected student IDs
            let studentIds = [];

            // Function to fetch students for the selected batch
            function fetchStudents(batchId, excludeIds = []) {
                if (batchId) {
                    $.ajax({
                        url: '', // The current page
                        method: 'POST',
                        data: {
                            batch_id: batchId
                        },
                        success: function(response) {
                            const students = JSON.parse(response);
                            const studentDropdown = $('#student-dropdowns');
                            studentDropdown.empty();
                            studentDropdown.append('<div class="student-dropdown-group"><label class="block text-gray-700 font-bold mb-2">Select Student*</label><select name="student_id[]" class="w-full p-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:outline-none" required><option value="" disabled selected>Select a student</option></select></div>');

                            students.forEach(function(student) {
                                // Skip adding the already selected student IDs to the dropdown
                                if (!excludeIds.includes(student.student_id)) {
                                    const option = $('<option>').val(student.student_id).text(student.student_name);
                                    studentDropdown.find('select').append(option);
                                }
                            });
                        }
                    });
                }
            }

            // Fetch students for the initially selected batch when the page loads
            fetchStudents(selectedBatchId);

            // When batch is selected, fetch students for that batch
            $('select[name="batch_id"]').change(function() {
                const batchId = $(this).val();
                console.log("Selected Batch ID on change: " + batchId); // Print the selected batch ID when changed
                fetchStudents(batchId);
            });

            $('#add-student').click(function(e) {
                e.preventDefault(); // Prevent form submission
                const studentId = $('#student-dropdowns select').val();
                const studentName = $('#student-dropdowns select option:selected').text();

                if (studentId) {
                    // Check if the student is already in the placed list
                    if (studentIds.includes(studentId)) {
                        alert('This student is already added to the placed list.');
                        return;
                    }

                    // Add the student ID to the global array
                    studentIds.push(studentId);

                    // Append the student to the placed students list
                    $('#placed-students').append(
                        `<div data-id="${studentId}" class="w-1/3 border border-gray-300 mb-2 p-2 px-4 hover:bg-gray-100 rounded-xl shadow-md flex justify-between items-center transition-all">
                        <span>${studentName}</span>
                        <button class="remove-student text-red-500 text-sm bg-red-100 h-10 w-10 ml-4 rounded-xl hover:scale-110 transition-all cursor-pointer"> 
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>`
                    );

                    // Show the placed students card if it's hidden
                    $('#placed-students-card').removeClass('hidden');

                    // Remove the selected student from the dropdown
                    $('#student-dropdowns select option:selected').remove();

                    // Update the hidden input with the selected student IDs
                    $('#student_ids').val(studentIds.join(','));
                }
            });

            $(document).on('click', '.remove-student', function() {
                // Get the parent div of the button that was clicked
                const studentCard = $(this).closest('div[data-id]');
                const studentId = studentCard.data('id');
                const studentName = studentCard.find('span').text(); // Get the student name from the span

                // Remove the specific student card
                studentCard.remove();

                // Remove the student ID from the global array
                studentIds = studentIds.filter(id => id != studentId);
                console.log("for delete = " + studentId);
                console.log(studentIds);

                // Re-add the student back to the dropdown
                $('#student-dropdowns select').append(`<option value="${studentId}">${studentName}</option>`);

                // Hide the placed students card if there are no students left
                if ($('#placed-students div[data-id]').length === 0) {
                    $('#placed-students-card').addClass('hidden');
                }

                // Update the hidden input with the selected student IDs
                $('#student_ids').val(studentIds.join(','));
            });
        });
    </script>
</head>

<body class="bg-gray-100 text-gray-800 flex h-screen overflow-hidden">
    <?php include('./sidebar.php'); ?>
    <div class="main-content pl-64 flex-1 ml-1/6 overflow-y-auto">
        <?php
        $page_title = "Add Placed Students";
        include('./navbar.php');
        ?>

        <div class="container mx-auto p-6">
            <!-- Back Button -->
            <a href="placed_students.php" class="text-white bg-gray-700 p-2 px-5 rounded-full mb-4 hover:px-7 inline-block transition-all">
                <i class="fa-solid fa-angle-left"></i> Back
            </a>

            <form id="addPlaceStudentForm" action="" method="POST" class="bg-white p-6 rounded-xl shadow-md">
                <div class="flex flex-wrap items-end gap-4">
                    <!-- Select Company -->
                    <div class="w-full md:w-1/3">
                        <label class="block text-gray-700 font-bold mb-2">Select Company*</label>
                        <select name="company_id" class="w-full p-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:outline-none" required>
                            <option value="" disabled selected>Select a company</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?php echo $company['id']; ?>">
                                    <?php echo $company['company_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Placed Date -->
                    <div class="w-full md:w-1/6 ">
                        <label class="block text-gray-700 font-bold mb-2">Placed Date*</label>
                        <input type="date" name="date" class="w-full p-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:outline-none" required>
                    </div>

                    <!-- Batch Dropdown -->
                    <div class="w-full md:w-1/6">
                        <label class="block text-gray-700 font-bold mb-2">Batch*</label>
                        <select name="batch_id" class="w-full p-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:outline-none" required>
                            <?php foreach ($batches as $batch): ?>
                                <option value="<?php echo $batch['id']; ?>"
                                    <?php echo ($batch['batch_end_year'] == $current_year) ? 'selected' : ''; ?>>
                                    <?php echo $batch['batch_start_year'] . ' - ' . $batch['batch_end_year']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Package Start -->
                    <div class="w-40">
                        <label class="block text-gray-700 font-bold mb-2">Package Start*</label>
                        <input type="number" name="package_start" placeholder="LPA" class="w-full p-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:outline-none" required>
                    </div>

                    <!-- Package End -->
                    <div class="w-40">
                        <label class="block text-gray-700 font-bold mb-2">Package End</label>
                        <input type="number" name="package_end" placeholder="LPA" class="w-full p-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    </div>
                </div>
                <br>
                <!-- Student Dropdowns Container -->
                <div id="student-dropdowns">
                    <!-- Initial Student Dropdown -->
                    <div class="student-dropdown-group">
                        <label class="block text-gray-700 font-bold mb-2">Select Student*</label>
                        <select name="student_id[]" class="w-full p-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:outline-none" required>
                            <option value="" disabled selected>Select a student</option>
                        </select>
                    </div>
                </div>

                <button id="add-student" type="button" class="mt-4 bg-cyan-500 text-white px-4 py-1 hover:bg-cyan-600 rounded-full transition-all cursor-pointer">Add to Placed List</button>

                <div id="placed-students-card" class="hidden">
                    <h2 class="font-bold text-gray-700 mb-2 mt-5">Placed Students</h2>
                    <ul id="placed-students" class="list-disc pl-5 text-gray-600"></ul>
                </div>

                <!-- Hidden input to store student IDs -->
                <input type="hidden" name="student_ids" id="student_ids" value="">

                <!-- Submit Button -->
                <div class="w-full text-left mt-10">
                    <button type="submit" name="saveBtn" class="bg-cyan-500 text-white px-6 py-2 rounded-2xl hover:scale-105 text-md font-bold hover:bg-cyan-600 cursor-pointer transition-all">
                        Save Data
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('saveBtn').addEventListener('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to save the changes?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, save it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('addPlaceStudentForm').submit();
                }
            });
        });
    </script>
</body>
</html>
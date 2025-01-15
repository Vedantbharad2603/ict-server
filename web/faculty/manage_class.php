<?php
include('../../api/db/db_connection.php');

// Function to fetch class information based on selected semId
function fetchClassInfo($sem_id) {
    global $conn;
    $class_query = "
        SELECT 
            cl.id AS class_id,
            cl.classname,
            cl.batch,
            fi.id AS faculty_id,
            CONCAT(fi.first_name, ' ', fi.last_name) AS faculty_name
        FROM 
            class_info cl
        JOIN 
            faculty_info fi ON cl.faculty_info_id = fi.id
        WHERE 
            cl.sem_info_id = $sem_id
    ";

    $result = mysqli_query($conn, $class_query);
    $classes = [];

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $classes[] = $row;
        }
    }

    return $classes;
}

// Function to fetch faculty names
function fetchFacultyNames() {
    global $conn;
    $faculty_query = "SELECT id, CONCAT(first_name, ' ', last_name) AS faculty_name FROM faculty_info";
    $result = mysqli_query($conn, $faculty_query);
    $faculty = [];

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $faculty[] = $row;
        }
    }

    return $faculty;
}

// Insert or update class
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['classname'])) {
    $classname = strtoupper($_POST['classname']);
    $sem_id = $_POST['sem_id'];
    $batch = strtolower($_POST['batch']);
    $faculty_id = $_POST['faculty_id'];
    $class_id = isset($_POST['class_id']) ? $_POST['class_id'] : null;

    if ($class_id) {
        // Update class info
        $update_query = "UPDATE class_info SET classname='$classname', sem_info_id='$sem_id', batch='$batch', faculty_info_id='$faculty_id' WHERE id='$class_id'";
        if (mysqli_query($conn, $update_query)) {
            echo json_encode(['status' => 'success', 'message' => 'Class updated successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error updating class. Please try again.']);
        }
    } else {
        // Insert new class
        $insert_query = "INSERT INTO class_info (classname, sem_info_id, batch, faculty_info_id) 
                         VALUES ('$classname', '$sem_id', '$batch', '$faculty_id')";

        if (mysqli_query($conn, $insert_query)) {
            echo json_encode(['status' => 'success', 'message' => 'Class created successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error creating class. Please try again.']);
        }
    }
    exit;
}

// Check if AJAX request is made for deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $class_id = $_POST['class_id'];

    // Delete class from class_info table
    $delete_query = "DELETE FROM class_info WHERE id = $class_id";

    if (mysqli_query($conn, $delete_query)) {
        echo json_encode(['status' => 'success', 'message' => 'Class deleted successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error deleting class. Please try again.']);
    }
    exit;
}

// Check if AJAX request is made for class info
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sem_id'])) {
    $sem_id = $_POST['sem_id'];
    $classes = fetchClassInfo($sem_id);
    echo json_encode(['classes' => $classes]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Class</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>

<body class="bg-gray-100 text-gray-800 flex h-screen overflow-hidden">
    <?php include('./sidebar.php'); ?>

    <div class="main-content pl-64 flex-1 ml-1/6 overflow-y-auto">
        <?php
        $page_title = "Manage Class";
        include('./navbar.php');
        ?>

        <div class="p-5 grid grid-cols-1 gap-4">
            <?php
            $sem_query = "SELECT id, sem, edu_type FROM sem_info";
            $sem_result = mysqli_query($conn, $sem_query);

            if (mysqli_num_rows($sem_result) > 0) {
                while ($sem_row = mysqli_fetch_assoc($sem_result)) {
                    echo "
                    <div class='bg-gray-300 rounded transition-all text-gray-800 p-2 pl-4 hover:pl-8 hover:bg-cyan-600/75 hover:text-white cursor-pointer' onclick='fetchClassInfo({$sem_row['id']}, this)'>
                        <div class='flex justify-between items-center'>
                            <h3 class='text-md font-bold'>Sem : {$sem_row['sem']} - " . strtoupper($sem_row['edu_type']) . "</h3>
                            <button 
                                class='transition-all bg-gray-200 text-green-600 mr-4 font-bold text-sm px-4 py-1 rounded hover:scale-110' 
                                onclick='event.stopPropagation(); openCreateClassPopup({$sem_row['id']}, \"{$sem_row['sem']}\", \"" . strtoupper($sem_row['edu_type']) . "\")'>
                                Create Class
                            </button>
                        </div>
                        <div class='class-list hidden mt-4'></div>
                    </div>";
                }
            } else {
                echo "<p>No semesters available</p>";
            }
            ?>
        </div>
    </div>

    <div id="create-class-popup" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white p-8 rounded-lg w-4/12">
            <h3 class="text-xl font-bold mb-4">Create New Class</h3>
            <form id="create-class-form" action="manage_class.php" method="POST">
                <input type="hidden" name="sem_id" id="popup-sem-id">
                <input type="hidden" name="class_id" id="popup-class-id">
                <div class="mb-4">
                    <label for="classname" class="block font-semibold">Class Name</label>
                    <input type="text" name="classname" id="classname" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label for="batch" class="block font-semibold">Batch:</label>
                    <select name="batch" id="batch" class="w-full p-2 border rounded" required>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="faculty_id" class="block font-semibold">Select Faculty:</label>
                    <select name="faculty_id" id="faculty_id" class="w-full p-2 border rounded" required>
                        <?php
                        $faculty = fetchFacultyNames();
                        foreach ($faculty as $faculty_member) {
                            echo "<option value='{$faculty_member['id']}'>{$faculty_member['faculty_name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="flex justify-end gap-4">
                    <button type="button" onclick="closePopup()" class="pl-5 pr-5 bg-gray-500 text-white p-2 rounded-full">Cancel</button>
                    <button type="submit" id="popup-submit" class="pl-6 pr-6 bg-cyan-500 text-white p-2 rounded-full">Create</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function fetchClassInfo(semId, semCard) {
        $.ajax({
            url: '',
            type: 'POST',
            data: { sem_id: semId },
            success: function (response) {
                const responseData = JSON.parse(response);
                const classListDiv = $(semCard).find('.class-list');

                classListDiv.empty();

                if (responseData.classes.length > 0) {
                    let classInfoHtml = '<ul>';
                    responseData.classes.forEach(classItem => {
                        classInfoHtml += `
                            <li class="bg-gray-200 transition-all text-gray-600 p-2 mb-2 pl-4 mr-8 hover:pl-8 rounded-xl">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-md text-gray-600 font-semibold">
                                        ${classItem.classname} - ${classItem.batch.toUpperCase()} - ${classItem.faculty_name}
                                    </h3>
                                    <div>
                                        <button onclick="editClass(${classItem.class_id}, '${classItem.classname}', '${classItem.batch}', ${classItem.faculty_id})" 
                                            class="border-blue-500 text-sm font-bold border-2 border-blue-600 text-blue-600 px-4 py-1 rounded-full hover:bg-blue-600 hover:text-white mr-2">
                                            Edit
                                        </button>
                                        <button onclick="deleteClass(${classItem.class_id})" 
                                            class="border-red-500 text-sm font-bold border-2 border-red-600 text-red-600 px-4 py-1 rounded-full hover:bg-red-600 hover:text-white">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </li>`;
                    });
                    classInfoHtml += '</ul>';
                    classListDiv.html(classInfoHtml);
                } else {
                    classListDiv.html('<p class="text-sm text-gray-500">No classes found for this semester.</p>');
                }

                classListDiv.stop().slideToggle(200);
            },
            error: function () {
                Swal.fire('Error', 'Unable to fetch class info. Please try again.', 'error');
            }
        });
    }

    function deleteClass(classId) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'This will permanently delete the class.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '',
                    type: 'POST',
                    data: { action: 'delete', class_id: classId },
                    success: function (response) {
                        const result = JSON.parse(response);
                        Swal.fire(result.status === 'success' ? 'Deleted!' : 'Error', result.message, result.status);
                        if (result.status === 'success') {
                            fetchClassInfo(classId); // Optionally reload class list
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Unable to delete class. Please try again.', 'error');
                    }
                });
            }
        });
    }

    function editClass(classId, classname, batch, facultyId) {
        document.getElementById('popup-class-id').value = classId;
        document.getElementById('classname').value = classname;
        document.getElementById('batch').value = batch.toUpperCase;
        document.getElementById('faculty_id').value = facultyId;
        document.getElementById('popup-sem-id').disabled = true; // Keep semester ID hidden for editing
        openCreateClassPopup(classId); // Open the popup for editing
    }

    function openCreateClassPopup(semId) {
        document.getElementById('popup-sem-id').value = semId;
        document.getElementById('create-class-popup').classList.remove('hidden');
    }

    function closePopup() {
        document.getElementById('create-class-popup').classList.add('hidden');
        document.getElementById('popup-sem-id').disabled = false; // Re-enable semester field for new class creation
    }

    $('#create-class-form').submit(function (e) {
        e.preventDefault();

        $.ajax({
            url: '',
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                const result = JSON.parse(response);
                Swal.fire(result.status === 'success' ? 'Success' : 'Error', result.message, result.status);
                if (result.status === 'success') {
                    closePopup();
                    fetchClassInfo($('#popup-sem-id').val()); // Reload class info
                }
            },
            error: function () {
                Swal.fire('Error', 'Unable to create class. Please try again.', 'error');
            }
        });
    });
    </script>
</body>
</html>

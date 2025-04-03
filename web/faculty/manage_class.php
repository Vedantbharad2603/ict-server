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
        $update_query = "UPDATE class_info SET classname='$classname', sem_info_id='$sem_id', batch='$batch', faculty_info_id='$faculty_id' WHERE id='$class_id'";
        if (mysqli_query($conn, $update_query)) {
            echo json_encode(['status' => 'success', 'message' => 'Class updated successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error updating class. Please try again.']);
        }
    } else {
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

        <div class="p-5">
            <!-- Degree Semester Tabs -->
            <div id="degree-tabs" class="bg-white shadow-xl rounded-xl p-3 mb-4">
                <h3 class="text-md font-bold pl-5 pt-2 mb-2">Degree Semesters</h3>
                <div class="flex border-b">
                    <?php
                    $sem_query = "SELECT id, sem, edu_type FROM sem_info WHERE edu_type = 'Degree'";
                    $sem_result = mysqli_query($conn, $sem_query);
                    $first_degree = true;

                    if (mysqli_num_rows($sem_result) > 0) {
                        while ($sem_row = mysqli_fetch_assoc($sem_result)) {
                            echo "
                            <button class='sem-tab-button px-4 py-2 -mb-px border-b-2 " . ($first_degree ? 'border-cyan-500 text-cyan-500' : 'border-transparent text-gray-600') . " hover:text-cyan-500 hover:border-cyan-500' 
                                data-sem-id='{$sem_row['id']}' data-tab='sem-tab-{$sem_row['id']}'>
                                Sem {$sem_row['sem']}
                            </button>";
                            $first_degree = false;
                        }
                    } else {
                        echo "<p class='pl-5 text-gray-600'>No Degree semesters available</p>";
                    }
                    ?>
                </div>
            </div>

            <!-- Diploma Semester Tabs -->
            <div id="diploma-tabs" class="bg-white shadow-xl rounded-xl p-3 mb-4">
                <h3 class="text-md font-bold pl-5 pt-2 mb-2">Diploma Semesters</h3>
                <div class="flex border-b">
                    <?php
                    $sem_query = "SELECT id, sem, edu_type FROM sem_info WHERE edu_type = 'Diploma'";
                    $sem_result = mysqli_query($conn, $sem_query);
                    $first_diploma = true;

                    if (mysqli_num_rows($sem_result) > 0) {
                        while ($sem_row = mysqli_fetch_assoc($sem_result)) {
                            echo "
                            <button class='sem-tab-button px-4 py-2 -mb-px border-b-2 " . ($first_diploma && !$first_degree ? 'border-cyan-500 text-cyan-500' : 'border-transparent text-gray-600') . " hover:text-cyan-500 hover:border-cyan-500' 
                                data-sem-id='{$sem_row['id']}' data-tab='sem-tab-{$sem_row['id']}'>
                                Sem {$sem_row['sem']}
                            </button>";
                            $first_diploma = false;
                        }
                    } else {
                        echo "<p class='pl-5 text-gray-600'>No Diploma semesters available</p>";
                    }
                    ?>
                </div>
            </div>

            <!-- Class Tabs Container -->
            <div id="class-tabs-container" class="bg-white shadow-xl rounded-xl p-3">
                <div id="class-tabs"></div>
            </div>
        </div>
    </div>

    <div id="create-class-popup" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white p-8 rounded-lg w-4/12">
            <h3 class="text-xl font-bold mb-4" id="popup-title">Create New Class</h3>
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
    $(document).ready(function() {
        // Load classes for the first active semester on page load
        const firstSemTab = $('.sem-tab-button.border-cyan-500');
        if (firstSemTab.length) {
            const semId = firstSemTab.data('sem-id');
            fetchClassInfo(semId, $('#class-tabs')[0]);
        }

        // Semester tab switching for both Degree and Diploma
        $('.sem-tab-button').on('click', function() {
            const semId = $(this).data('sem-id');
            $('.sem-tab-button').removeClass('border-cyan-500 text-cyan-500').addClass('border-transparent text-gray-600');
            $(this).removeClass('border-transparent text-gray-600').addClass('border-cyan-500 text-cyan-500');
            fetchClassInfo(semId, $('#class-tabs')[0]);
        });
    });

    function fetchClassInfo(semId, tabContainer) {
        $.ajax({
            url: '',
            type: 'POST',
            data: { sem_id: semId },
            success: function(response) {
                const responseData = JSON.parse(response);
                const $tabContainer = $(tabContainer);

                $tabContainer.empty();

                let tabsHtml = '<div class="flex border-b">';
                if (responseData.classes.length > 0) {
                    responseData.classes.forEach((classItem, index) => {
                        tabsHtml += `
                            <button class="class-tab-button px-4 py-2 -mb-px border-b-2 ${index === 0 ? 'border-cyan-500 text-cyan-500' : 'border-transparent text-gray-600'} hover:text-cyan-500 hover:border-cyan-500" data-tab="tab-${classItem.class_id}">
                                ${classItem.classname} - ${classItem.batch.toUpperCase()}
                            </button>`;
                    });
                }
                // Add Create Class tab
                tabsHtml += `
                    <button class="class-tab-button px-4 py-2 -mb-px border-b-2 border-transparent text-gray-600 hover:text-cyan-500 hover:border-cyan-500" data-tab="tab-create-${semId}">
                        + Create Class
                    </button>
                </div>`;

                // Tab content
                let contentHtml = '<div class="tab-content mt-4">';
                if (responseData.classes.length > 0) {
                    responseData.classes.forEach((classItem, index) => {
                        contentHtml += `
                            <div id="tab-${classItem.class_id}" class="tab-pane ${index === 0 ? '' : 'hidden'} p-4 bg-gray-50 rounded-b-xl">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-md text-gray-600 font-semibold">
                                        ${classItem.classname} - ${classItem.batch.toUpperCase()} - ${classItem.faculty_name}
                                    </h3>
                                    <div>
                                        <button onclick="editClass(${classItem.class_id}, '${classItem.classname}', '${classItem.batch}', ${classItem.faculty_id}, ${semId})" 
                                            class="border-blue-500 text-sm font-bold border-2 border-blue-600 text-blue-600 px-4 py-1 rounded-full hover:bg-blue-600 hover:text-white mr-2">
                                            Edit
                                        </button>
                                        <button onclick="deleteClass(${classItem.class_id}, ${semId})" 
                                            class="border-red-500 text-sm font-bold border-2 border-red-600 text-red-600 px-4 py-1 rounded-full hover:bg-red-600 hover:text-white">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>`;
                    });
                } else {
                    contentHtml += `<div id="tab-no-classes-${semId}" class="tab-pane hidden p-4 bg-gray-50 rounded-b-xl">
                        <p class="text-sm text-gray-500">No classes found for this semester.</p>
                    </div>`;
                }
                contentHtml += `
                    <div id="tab-create-${semId}" class="tab-pane ${responseData.classes.length === 0 ? '' : 'hidden'} p-4 bg-gray-50 rounded-b-xl">
                        <button class="transition-all bg-gray-100 drop-shadow-lg text-green-600 font-bold text-sm px-4 py-1 rounded-lg hover:scale-110" 
                            onclick="openCreateClassPopup(${semId})">
                            Create New Class
                        </button>
                    </div>
                </div>`;

                $tabContainer.html(tabsHtml + contentHtml);

                // Class tab switching logic
                $tabContainer.find('.class-tab-button').on('click', function() {
                    const tabId = $(this).data('tab');
                    $tabContainer.find('.class-tab-button').removeClass('border-cyan-500 text-cyan-500').addClass('border-transparent text-gray-600');
                    $(this).removeClass('border-transparent text-gray-600').addClass('border-cyan-500 text-cyan-500');
                    $tabContainer.find('.tab-pane').addClass('hidden');
                    $tabContainer.find(`#${tabId}`).removeClass('hidden');
                });
            },
            error: function() {
                Swal.fire('Error', 'Unable to fetch class info. Please try again.', 'error');
            }
        });
    }

    function deleteClass(classId, semId) {
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
                    success: function(response) {
                        const result = JSON.parse(response);
                        Swal.fire(result.status === 'success' ? 'Deleted!' : 'Error', result.message, result.status);
                        if (result.status === 'success') {
                            fetchClassInfo(semId, $('#class-tabs')[0]);
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Unable to delete class. Please try again.', 'error');
                    }
                });
            }
        });
    }

    function editClass(classId, classname, batch, facultyId, semId) {
        document.getElementById('popup-class-id').value = classId;
        document.getElementById('classname').value = classname;
        document.getElementById('batch').value = batch.toUpperCase();
        document.getElementById('faculty_id').value = facultyId;
        document.getElementById('popup-sem-id').value = semId;
        document.getElementById('popup-title').textContent = 'Edit Class';
        document.getElementById('popup-submit').textContent = 'Update';
        document.getElementById('create-class-popup').classList.remove('hidden');
    }

    function openCreateClassPopup(semId) {
        document.getElementById('popup-sem-id').value = semId;
        document.getElementById('popup-class-id').value = '';
        document.getElementById('classname').value = '';
        document.getElementById('batch').value = 'A';
        document.getElementById('faculty_id').value = '';
        document.getElementById('popup-title').textContent = 'Create New Class';
        document.getElementById('popup-submit').textContent = 'Create';
        document.getElementById('create-class-popup').classList.remove('hidden');
    }

    function closePopup() {
        document.getElementById('create-class-popup').classList.add('hidden');
    }

    $('#create-class-form').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: '',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const result = JSON.parse(response);
                Swal.fire(result.status === 'success' ? 'Success' : 'Error', result.message, result.status);
                if (result.status === 'success') {
                    closePopup();
                    fetchClassInfo($('#popup-sem-id').val(), $('#class-tabs')[0]);
                }
            },
            error: function() {
                Swal.fire('Error', 'Unable to process request. Please try again.', 'error');
            }
        });
    });
    </script>
</body>
</html>
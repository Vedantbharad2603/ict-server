<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meetings</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>

<body class="bg-gray-100 text-gray-800 flex h-screen overflow-hidden">

    <?php include('./sidebar.php'); ?>

    <!-- Main Content -->
    <div class="main-content pl-64 flex-1 ml-1/6 overflow-y-auto">

        <?php
        $page_title = "Meetings";
        include('./navbar.php');
        include('../db/db_connection.php'); // Ensure this connects to your database

        // Assume $userdata is already populated with the logged-in user data
        $faculty_info_id = $userdata['id'];

        // Handle delete request
        if (isset($_GET['id'])) {
            $meeting_id = $_GET['id'];
            $delete_query = "DELETE FROM zoom_link_info WHERE id = $meeting_id AND faculty_info_id = $faculty_info_id";
            if (mysqli_query($conn, $delete_query)) {
                echo "<script>
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Meeting has been deleted.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => { window.location.href = 'add_zoom_meeting.php'; });
                </script>";
            } else {
                echo "<script>
                    Swal.fire({
                        title: 'Error!',
                        text: 'There was an issue deleting the meeting.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    }).then(() => { window.location.href = 'add_zoom_meeting.php'; });
                </script>";
            }
        }

        // Handle add/edit request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $meeting_id = $_POST['meeting_id'] ?? null;
            $meeting_title = $_POST['meeting_title'];
            $meeting_link = $_POST['meeting_link'];
            $meeting_date = $_POST['meeting_date'];
            $meeting_time = $_POST['meeting_time'];

            if ($meeting_id) {
                // Update existing meeting
                $update_query = "UPDATE zoom_link_info 
                        SET zoom_link_title = '$meeting_title', zoom_date = '$meeting_date', zoom_link_time = '$meeting_time', zoom_link = '$meeting_link' 
                        WHERE id = $meeting_id AND faculty_info_id = $faculty_info_id";
                if (mysqli_query($conn, $update_query)) {
                    echo "<script>
                        Swal.fire({
                            title: 'Success!',
                            text: 'Meeting updated successfully.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => { window.location.href = 'add_zoom_meeting.php'; });
                    </script>";
                }
            } else {
                // Add new meeting
                $insert_query = "INSERT INTO zoom_link_info (zoom_link_title, zoom_date, zoom_link_time, zoom_link, faculty_info_id) 
                        VALUES ('$meeting_title', '$meeting_date', '$meeting_time', '$meeting_link', $faculty_info_id)";
                if (mysqli_query($conn, $insert_query)) {
                    echo "<script>
                        Swal.fire({
                            title: 'Success!',
                            text: 'Meeting added successfully.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => { window.location.href = 'add_zoom_meeting.php'; });
                    </script>";
                }
            }
        }
        ?>

        <div class="p-6">
            <!-- Add/Edit Meeting Popup Trigger -->
            <button onclick="openAddEditPopup()" class="bg-cyan-500 px-6 hover:px-8 text-white p-2 hover:bg-cyan-600 rounded-full mb-6 transition-all">Add Meeting</button>

            <!-- Meetings Table -->
            <table id="meetings-table" class="min-w-full bg-white border border-gray-300 rounded">
                <thead>
                    <tr>
                        <th class="border px-4 py-2"> <!-- Add this class -->
                            <input type="checkbox" id="select-all" class="cursor-pointer w-5 h-5">
                        </th>
                        <th class="border px-4 py-2">No</th>
                        <th class="border px-4 py-2">Meeting Title</th>
                        <th class="border px-4 py-2">Date</th>
                        <th class="border px-4 py-2">Time</th>
                        <th class="border px-4 py-2">Faculty</th>
                        <th class="border px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    date_default_timezone_set('Asia/Kolkata');
                    $query = "SELECT zli.id, zli.zoom_link_title, zli.zoom_link, zli.faculty_info_id, zli.zoom_date, zli.zoom_link_time, 
                              CONCAT(fi.first_name, ' ', fi.last_name) AS faculty_name
                              FROM zoom_link_info zli
                              JOIN faculty_info fi ON zli.faculty_info_id = fi.id
                              ORDER BY zli.zoom_date";
                    $result = mysqli_query($conn, $query);

                    // Debugging the query result
                    if (!$result) {
                        echo "<tr><td colspan='6' class='border px-4 py-2 text-center'>Error: " . mysqli_error($conn) . "</td></tr>";
                    } else if (mysqli_num_rows($result) > 0) {
                        $counter = 1;
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Combine zoom_date and zoom_link_time into a full datetime
                            $formatted_date = date("d/m/Y", strtotime($row['zoom_date']));
                            $meeting_time = date("H:i", strtotime($row['zoom_link_time']));
                            $meeting_datetime = date("Y-m-d H:i", strtotime($row['zoom_date'] . ' ' . $meeting_time));
                            $current_datetime = date("Y-m-d H:i"); // Get the current date and time for comparison

                            echo "<tr>
                                <td class='border px-4 py-2 text-center'>
                                    <input type='checkbox' name='selected_ids[]' value='{$row['id']}' class='select-checkbox h-4 w-4 cursor-pointer'>
                                </td>
                                <td class='border px-4 py-2 text-center'>{$counter}</td>
                                <td class='border px-4 py-2'>{$row['zoom_link_title']}</td>
                                <td class='border px-4 py-2 text-center'>{$formatted_date}</td>
                                <td class='border px-4 py-2 text-center'>{$row['zoom_link_time']}</td>
                                <td class='border px-4 py-2'>{$row['faculty_name']}</td>
                                <td class='border px-4 py-2 text-center'>";

                            // Only show Edit button if faculty_info_id matches the logged-in user's faculty_info_id
                            if ($row['faculty_info_id'] == $faculty_info_id) {
                                echo "<button onclick=\"openAddEditPopup({$row['id']}, '{$row['zoom_link_title']}','{$row['zoom_date']}', '{$row['zoom_link_time']}','{$row['zoom_link']}')\" class='text-blue-500 mr-2'>Edit</button>";
                            }

                            // Show Delete button
                            echo "<button onclick='confirmDelete({$row['id']})' class='text-red-500 mr-3'>Delete</button>";

                            // Show Join button if the meeting time has passed or is currently ongoing
                            if ($current_datetime >= $meeting_datetime) {
                                echo "<button onclick=\"window.open('{$row['zoom_link']}', '_blank')\" class='text-green-500 mt-2'>Join</button>";
                            }

                            echo "</td>
                            </tr>";
                            $counter++;
                        }
                    } else {
                        echo "<tr><td colspan='6' class='border px-4 py-2 text-center'>No meetings found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Add/Edit Popup -->
        <div id="popup-modal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden flex justify-center items-center">
            <div class="bg-white rounded-lg p-6 w-96">
                <h2 id="popup-title" class="text-xl font-bold mb-4">Add/Edit Meeting</h2>
                <form id="popup-form" action="add_zoom_meeting.php" method="POST">
                    <input type="hidden" name="meeting_id" id="meeting_id">
                    <div class="mb-4">
                        <label for="meeting_title" class="block text-sm font-medium mb-1">Meeting Title</label>
                        <input type="text" id="meeting_title" name="meeting_title" class="border rounded p-2 w-full" required>
                    </div>
                    <div class="mb-4">
                        <label for="meeting_link" class="block text-sm font-medium mb-1">Meeting Link</label>
                        <input type="url" id="meeting_link" name="meeting_link" class="border rounded p-2 w-full" required>
                    </div>
                    <div class="mb-4">
                        <label for="meeting_date" class="block text-sm font-medium mb-1">Meeting Date</label>
                        <input type="date" id="meeting_date" name="meeting_date" class="border rounded p-2 w-full" required>
                    </div>
                    <div class="mb-4">
                        <label for="meeting_time" class="block text-sm font-medium mb-1">Meeting Time</label>
                        <input type="time" id="meeting_time" name="meeting_time" class="border rounded p-2 w-full" required>
                    </div>
                    <div class="flex justify-end gap-4">
                        <button type="button" onclick="closePopup()" class="pl-5 pr-5 bg-gray-500 hover:bg-gray-600 text-white p-2 rounded-full">Cancel</button>
                        <button type="submit" id="popup-submit" class="pl-6 pr-6 bg-cyan-500 hover:bg-cyan-600 text-white p-2 rounded-full">Save</button>
                    </div>
                </form>
            </div>
        </div>

    
        <script>

$(document).ready(function () {
    $('#meetings-table').DataTable({
        paging: false,  // Disable pagination
        info: false,    // Disable info
        searching: true, // Enable search
        columnDefs: [
            { orderable: false, targets: 0 } // Disable sorting for the first column
        ]
    });

    $('#select-all').on('click', function () {
        $('.select-checkbox').prop('checked', this.checked);
        toggleDeleteButton();
    });

    $('.select-checkbox').on('change', function () {
        toggleDeleteButton();
    });

    function toggleDeleteButton() {
        const selected = $('.select-checkbox:checked').length > 0;
        const deleteButton = document.querySelector("button[onclick='deleteSelectedHolidays()']");
        if (selected) {
            deleteButton.disabled = false;
            deleteButton.classList.remove('opacity-25', 'cursor-not-allowed');
        } else {
            deleteButton.disabled = true;
            deleteButton.classList.add('opacity-25', 'cursor-not-allowed');
        }
    }

    // Initialize button state
    toggleDeleteButton();   
});

            function openAddEditPopup(id = null, title = '', date = '', time = '', link = '') {
                document.getElementById('popup-title').innerText = id ? 'Edit Meeting' : 'Add Meeting';
                document.getElementById('meeting_id').value = id || '';
                document.getElementById('meeting_title').value = title || '';
                document.getElementById('meeting_date').value = date || '';
                document.getElementById('meeting_time').value = time || '';
                document.getElementById('meeting_link').value = link || '';
                document.getElementById('popup-modal').classList.remove('hidden');
            }

            function closePopup() {
                document.getElementById('popup-modal').classList.add('hidden');
            }

            function confirmDelete(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `add_zoom_meeting.php?id=${id}`;
                    }
                });
            }
        </script>
    </div>
</body>

</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Holidays</title>
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
$page_title = "Holidays";
include('./navbar.php');

include('../db/db_connection.php');

// Handle delete request for multiple holidays
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_selected'])) {
    $ids = $_POST['selected_ids'];
    if (!empty($ids)) {
        $id_list = implode(',', $ids);
        $delete_query = "DELETE FROM holiday_info WHERE id IN ($id_list)";
        if (mysqli_query($conn, $delete_query)) {
            echo "<script>
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Selected holidays have been deleted.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => { window.location.href = 'holiday.php'; });
            </script>";
        }
    }
}

// Handle single delete request
if (isset($_GET['id'])) {
    $holiday_id = $_GET['id'];
    $delete_query = "DELETE FROM holiday_info WHERE id = $holiday_id";
    if (mysqli_query($conn, $delete_query)) {
        echo "<script>
            Swal.fire({
                title: 'Deleted!',
                text: 'Holiday has been deleted.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => { window.location.href = 'holiday.php'; });
        </script>";
    }
}
?>

<div class="p-6">
    <!-- Add/Edit Holiday Popup Trigger -->
    <button onclick="openAddEditPopup()" class="bg-cyan-500 px-6 hover:px-8 text-white p-2 hover:bg-cyan-600 rounded-full mb-6 transition-all">Add Holiday</button>
    <button onclick="deleteSelectedHolidays()" class="bg-red-500 px-6 hover:px-8 text-white p-2 hover:bg-red-600 rounded-full mb-6 ml-4  transition-all">Delete Selected</button>

    <!-- Holidays Table -->
    <form id="holiday-table-form" class="rounded-lg" method="POST">
        <input type="hidden" name="delete_selected" value="1">
        <table id="holiday-table" class="min-w-full bg-white border border-gray-300 rounded-lg">
            <thead>
                <tr>
                    <th class="border px-4 py-2"> <!-- Add this class -->
                        <input type="checkbox" id="select-all" class="cursor-pointer w-5 h-5">
                    </th>
                    <th class="border px-4 py-2">No</th>
                    <th class="border px-4 py-2">Holiday Name</th>
                    <th class="border px-4 py-2">Date of holiday</th>
                    <th class="border px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM holiday_info ORDER BY holiday_date";
                $result = mysqli_query($conn, $query);

                if (mysqli_num_rows($result) > 0) {
                    $counter = 1;
                    while ($row = mysqli_fetch_assoc($result)) {
                        $formatted_date = date("d/m/Y", strtotime($row['holiday_date']));
                        echo "<tr>
                                <td class='border px-4 py-2 text-center'>
                                    <input type='checkbox' name='selected_ids[]' value='{$row['id']}' class='select-checkbox h-4 w-4 cursor-pointer'>
                                </td>
                                <td class='border px-4 py-2 text-center'>{$counter}</td>
                                <td class='border px-4 py-2'>{$row['holiday_name']}</td>
                                <td class='border px-4 py-2 text-center'>{$formatted_date}</td>
                                <td class='border px-4 py-2 text-center'>
                                    <button type='button' onclick='openAddEditPopup({$row['id']}, \"{$row['holiday_name']}\", \"{$row['holiday_date']}\")' class='text-blue-500 mr-2'>Edit</button>
                                    <button type='button' onclick='confirmDelete({$row['id']})' class='text-red-500'>Delete</button>
                                </td>
                              </tr>";
                        $counter++;
                    }
                } else {
                    echo "<tr><td colspan='5' class='border px-4 py-2 text-center'>No holidays found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </form>
</div>

<!-- Add/Edit Popup -->
<div id="popup-modal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden flex justify-center items-center">
    <div class="bg-white rounded-lg p-6 w-96">
        <h2 id="popup-title" class="text-xl font-bold mb-4">Add/Edit Holiday</h2>
        <form id="popup-form" action="holiday.php" method="POST">
            <input type="hidden" name="holiday_id" id="holiday_id">
            <div class="mb-4">
                <label for="holiday_name" class="block text-sm font-medium mb-1">Holiday Name</label>
                <input type="text" id="holiday_name" name="holiday_name" class="border-2 rounded p-2 w-full" required>
            </div>
            <div class="mb-4">
                <label for="holiday_date" class="block text-sm font-medium mb-1">Holiday Date</label>
                <input type="date" id="holiday_date" name="holiday_date" class="border-2 rounded p-2 w-full" required>
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
    $('#holiday-table').DataTable({
        paging: false,  // Disable pagination
        info: false,    // Disable info
        searching: false, // Disable search
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


function openAddEditPopup(id = null, name = '', date = '') {
    document.getElementById('popup-title').innerText = id ? 'Edit Holiday' : 'Add Holiday';
    document.getElementById('holiday_id').value = id || '';
    document.getElementById('holiday_name').value = name || '';
    document.getElementById('holiday_date').value = date || '';
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
            window.location.href = `holiday.php?id=${id}`;
        }
    });
}

function deleteSelectedHolidays() {
    const selected = $('.select-checkbox:checked').length;
    if (selected === 0) {
        Swal.fire({
            title: 'No Selection!',
            text: 'Please select at least one holiday to delete.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }

    Swal.fire({
        title: 'Are you sure?',
        text: "Selected holidays will be permanently deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete them!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('holiday-table-form').submit();
        }
    });
}
</script>


</div>

</body>
</html>

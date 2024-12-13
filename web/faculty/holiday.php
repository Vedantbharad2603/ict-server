<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Holidays</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 text-gray-800 flex h-screen overflow-hidden">

<?php include('./sidebar.php'); ?>

<!-- Main Content -->
<div class="main-content pl-64 flex-1 ml-1/6 overflow-y-auto">

<?php
$page_title = "Holidays";
include('./navbar.php');

include('../db/db_connection.php'); // Ensure this connects to your database

// Handle delete request
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
    } else {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: 'There was an issue deleting the holiday.',
                icon: 'error',
                confirmButtonText: 'OK'
            }).then(() => { window.location.href = 'holiday.php'; });
        </script>";
    }
}

// Handle add/edit request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $holiday_id = $_POST['holiday_id'] ?? null;
    $holiday_name = $_POST['holiday_name'];
    $holiday_date = $_POST['holiday_date'];

    if ($holiday_id) {
        // Update existing holiday
        $update_query = "UPDATE holiday_info SET holiday_name = '$holiday_name', holiday_date = '$holiday_date' WHERE id = $holiday_id";
        if (mysqli_query($conn, $update_query)) {
            echo "<script>
                Swal.fire({
                    title: 'Success!',
                    text: 'Holiday updated successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => { window.location.href = 'holiday.php'; });
            </script>";
        }
    } else {
        // Add new holiday
        $insert_query = "INSERT INTO holiday_info (holiday_name, holiday_date) VALUES ('$holiday_name', '$holiday_date')";
        if (mysqli_query($conn, $insert_query)) {
            echo "<script>
                Swal.fire({
                    title: 'Success!',
                    text: 'Holiday added successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => { window.location.href = 'holiday.php'; });
            </script>";
        }
    }
}
?>

<div class="p-6">
    <!-- Add/Edit Holiday Popup Trigger -->
    <button onclick="openAddEditPopup()" class="bg-blue-500 text-white p-2 rounded mb-6">Add Holiday</button>

    <!-- Holidays Table -->
    <table class="min-w-full bg-white border border-gray-300 rounded">
        <thead>
            <tr>
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
                            <td class='border px-4 py-2 text-center'>{$counter}</td>
                            <td class='border px-4 py-2'>{$row['holiday_name']}</td>
                            <td class='border px-4 py-2 text-center'>{$formatted_date}</td>
                            <td class='border px-4 py-2 text-center'>
                                <button onclick='openAddEditPopup({$row['id']}, \"{$row['holiday_name']}\", \"{$row['holiday_date']}\")' class='text-blue-500 mr-2'>Edit</button>
                                <button onclick='confirmDelete({$row['id']})' class='text-red-500'>Delete</button>
                            </td>
                          </tr>";
                    $counter++;
                }
            } else {
                echo "<tr><td colspan='4' class='border px-4 py-2 text-center'>No holidays found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Add/Edit Popup -->
<div id="popup-modal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden flex justify-center items-center">
    <div class="bg-white rounded-lg p-6 w-96">
        <h2 id="popup-title" class="text-xl font-bold mb-4">Add/Edit Holiday</h2>
        <form id="popup-form" action="holiday.php" method="POST">
            <input type="hidden" name="holiday_id" id="holiday_id">
            <div class="mb-4">
                <label for="holiday_name" class="block text-sm font-medium mb-1">Holiday Name</label>
                <input type="text" id="holiday_name" name="holiday_name" class="border rounded p-2 w-full" required>
            </div>
            <div class="mb-4">
                <label for="holiday_date" class="block text-sm font-medium mb-1">Holiday Date</label>
                <input type="date" id="holiday_date" name="holiday_date" class="border rounded p-2 w-full" required>
            </div>
            <div class="flex justify-end gap-4">
                <button type="button" onclick="closePopup()" class="bg-gray-500 text-white p-2 rounded">Cancel</button>
                <button type="submit" id="popup-submit" class="bg-blue-500 text-white p-2 rounded">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
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
</script>

</div>

</body>
</html>

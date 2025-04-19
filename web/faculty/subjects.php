<?php
include('../../api/db/db_connection.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        #subject-table {
            border-collapse: collapse; /* Ensure borders don't double up */
        }
        #subject-table th, #subject-table td {
            text-align: center;
            border: 1px solid #d1d5db; /* Tailwind's gray-300 */
        }
        #subject-table th {
            background-color: #374151; /* Tailwind's gray-700 */
            color: #ffffff; /* Tailwind's white */
        }
        #subject-table tbody tr:hover {
            background-color: #f9fafb; /* Tailwind's gray-50 for hover effect */
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-800 flex h-screen overflow-hidden">
    <?php include('./sidebar.php'); ?>
    <div class="main-content pl-64 flex-1 ml-1/6 overflow-y-auto">
        <?php
        $page_title = "Subjects";
        include('./navbar.php');
        ?>
        <div class="container mx-auto p-6">
            <div class="bg-white p-6 rounded-xl shadow-md mb-6">
                <div class="w-full md:w-1/2 mb-4">
                    <label for="semester" class="block text-gray-700 font-bold mb-2">Semester & Program</label>
                    <select id="semester" name="semester" class="w-full p-3 border-2 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                        <option value="" disabled selected>Select Semester & Program</option>
                        <?php
                        $sem_query = "SELECT id, sem, edu_type FROM sem_info ORDER BY edu_type, sem";
                        $sem_result = mysqli_query($conn, $sem_query);
                        while ($row = mysqli_fetch_assoc($sem_result)) {
                            echo "<option value='{$row['id']}'>SEM {$row['sem']} - " . strtoupper($row['edu_type']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="p-6 bg-white rounded-xl shadow-md">
                <button onclick="openAddEditPopup()" class="bg-cyan-500 shadow-md hover:shadow-xl px-6 text-white p-2 rounded-full hover:bg-cyan-600 transition-all mb-6">Add Subject</button>
                <table id="subject-table" class="min-w-full bg-white shadow-lg rounded-md border border-gray-300">
                    <thead>
                        <tr class="bg-gray-700 text-white">
                            <th class="border px-4 py-2 rounded-tl-md">No</th>
                            <th class="border px-4 py-2">Subject Name</th>
                            <th class="border px-4 py-2">Short Name</th>
                            <th class="border px-4 py-2">Subject Code</th>
                            <th class="border px-4 py-2">Subject Type</th>
                            <th class="border px-4 py-2">Lecture Type</th>
                            <th class="border px-4 py-2 rounded-tr-md">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div id="popup-modal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden flex justify-center items-center">
            <div class="bg-white rounded-lg p-6 w-96">
                <h2 id="popup-title" class="text-xl font-bold mb-4">Add Subject</h2>
                <form id="popup-form" action="add_subject.php" method="POST">
                    <input type="hidden" name="subject_id" id="subject_id">
                    <input type="hidden" name="sem_info_id" id="sem_info_id">
                    <div class="mb-4">
                        <label for="subject_name" class="block text-sm font-medium mb-1">Subject Name</label>
                        <input type="text" id="subject_name" name="subject_name" class="border-2 rounded p-2 w-full" required>
                    </div>
                    <div class="mb-4">
                        <label for="short_name" class="block text-sm font-medium mb-1">Short Name (Uppercase)</label>
                        <input type="text" id="short_name" name="short_name" class="border-2 rounded p-2 w-full" oninput="this.value = this.value.toUpperCase();" required>
                    </div>
                    <div class="mb-4">
                        <label for="subject_code" class="block text-sm font-medium mb-1">Subject Code</label>
                        <input type="text" id="subject_code" name="subject_code" class="border-2 rounded p-2 w-full" required>
                    </div>
                    <div class="mb-4">
                        <label for="subject_type" class="block text-sm font-medium mb-1">Subject Type</label>
                        <select id="subject_type" name="subject_type" class="border-2 rounded p-2 w-full" required>
                            <option value="" disabled selected>Select Subject Type</option>
                            <option value="mandatory">Mandatory</option>
                            <option value="elective">Elective</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="lec_type" class="block text-sm font-medium mb-1">Lecture Type</label>
                        <select id="lec_type" name="lec_type" class="border-2 rounded p-2 w-full" required>
                            <option value="" disabled selected>Select Lecture Type</option>
                            <option value="L">Lab only</option>
                            <option value="T">Theory only</option>
                            <option value="LT">Lab and Theory</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-4">
                        <button type="button" onclick="closePopup()" class="pl-5 pr-5 bg-gray-500 hover:bg-gray-600 text-white p-2 rounded-full">Cancel</button>
                        <button type="submit" id="popup-submit" class="pl-6 pr-6 bg-cyan-500 hover:bg-cyan-600 text-white p-2 rounded-full">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            let selectedSemId = '';
            const table = $('#subject-table').DataTable({
                paging: false,
                info: false,
                searching: false,
                ordering: false,
                language: {
                    emptyTable: 'Please select a semester to view subjects'
                },
                columns: [
                    { data: 'no' },
                    { data: 'subject_name' },
                    { data: 'short_name' },
                    { data: 'subject_code' },
                    { data: 'type' },
                    { data: 'lec_type' },
                    { data: 'actions' }
                ]
            });

            // Load Subjects when Semester is Selected
            $('#semester').change(function () {
                selectedSemId = $(this).val();
                console.log('Selected sem_id:', selectedSemId); // Debug: Log selected sem_id
                if (selectedSemId) {
                    $.ajax({
                        url: 'fetch_subjects_by_sem.php',
                        method: 'POST',
                        data: { sem_id: selectedSemId },
                        dataType: 'json',
                        success: function (data) {
                            console.log('fetch_subjects response:', data); // Debug: Log raw response
                            if (!Array.isArray(data)) {
                                console.error('Expected an array, got:', data);
                                Swal.fire('Error', data.error || 'Invalid response format from server.', 'error');
                                table.clear().draw();
                                return;
                            }

                            table.clear();
                            if (data.length === 0) {
                                table.draw();
                                return;
                            }

                            const rows = data.map((sub, index) => ({
                                no: index + 1,
                                subject_name: sub.subject_name || 'N/A',
                                short_name: sub.short_name || 'N/A',
                                subject_code: sub.subject_code || 'N/A',
                                type: sub.type ? sub.type.charAt(0).toUpperCase() + sub.type.slice(1) : 'N/A',
                                lec_type: sub.lec_type === 'L' ? 'Lab only' : sub.lec_type === 'T' ? 'Theory only' : 'Lab and Theory',
                                actions: `<button type="button" onclick="openAddEditPopup(${sub.id}, '${sub.subject_name}', '${sub.short_name}', '${sub.subject_code}', '${sub.type}', '${sub.lec_type}')" class="text-blue-500 mr-2">Edit</button>
                                          <button type="button" onclick="deleteSubject(${sub.id})" class="text-red-500">Delete</button>`
                            }));
                            table.rows.add(rows).draw();
                        },
                        error: function (xhr, status, error) {
                            console.error('fetch_subjects AJAX error:', status, error, 'Response:', xhr.responseText);
                            Swal.fire('Error', 'Failed to load subjects. Check the console for details.', 'error');
                            table.clear().draw();
                        }
                    });
                } else {
                    table.clear().draw();
                }
            });

            // Handle form submission via AJAX
            $('#popup-form').submit(function (e) {
                e.preventDefault();
                const subjectId = $('#subject_id').val();
                const url = subjectId ? 'update_subject.php' : 'add_subject.php';
                const formData = $(this).serialize();

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function (response) {
                        console.log('Form submission response:', response);
                        if (response.status === 'success') {
                            Swal.fire({
                                title: subjectId ? 'Updated!' : 'Added!',
                                text: `Subject has been ${subjectId ? 'updated' : 'added'} successfully.`,
                                icon: 'success'
                            }).then(() => {
                                closePopup();
                                $('#semester').trigger('change');
                            });
                        } else {
                            Swal.fire('Error', response.message || 'Failed to save subject.', 'error');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Form submission AJAX error:', status, error, 'Response:', xhr.responseText);
                        Swal.fire('Error', 'Failed to save subject. Check the console for details.', 'error');
                    }
                });
            });

            // Trigger change event on page load
            $('#semester').trigger('change');
        });

        function openAddEditPopup(id = null, name = '', shortName = '', code = '', type = '', lecType = '') {
            $('#popup-title').text(id ? 'Edit Subject' : 'Add Subject');
            $('#subject_id').val(id || '');
            $('#sem_info_id').val($('#semester').val() || '');
            $('#subject_name').val(name || '');
            $('#short_name').val(shortName || '');
            $('#subject_code').val(code || '');
            $('#subject_type').val(type || '');
            $('#lec_type').val(lecType || '');
            $('#popup-modal').removeClass('hidden');
        }

        function closePopup() {
            $('#popup-modal').addClass('hidden');
            $('#popup-form')[0].reset();
            $('#subject_id').val('');
            $('#sem_info_id').val('');
        }

        function deleteSubject(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This subject will be permanently deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'delete_subject.php',
                        method: 'POST',
                        data: { id: id },
                        dataType: 'json',
                        success: function (response) {
                            console.log('delete_subject response:', response);
                            if (response.status === 'success') {
                                Swal.fire('Deleted!', 'Subject has been deleted.', 'success').then(() => {
                                    $('#semester').trigger('change');
                                });
                            } else {
                                Swal.fire('Error', response.message || 'Failed to delete subject.', 'error');
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('delete_subject AJAX error:', status, error, 'Response:', xhr.responseText);
                            Swal.fire('Error', 'Failed to delete subject. Check the console for details.', 'error');
                        }
                    });
                }
            });
        }
    </script>
</body>

</html>
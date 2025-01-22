<?php
include('../../api/db/db_connection.php');

// Function to fetch rounds for a company
function getCompanyRounds($company_id) {
    global $conn;
    $query = "SELECT * FROM company_rounds_info WHERE company_info_id = $company_id ORDER BY round_index";
    $result = mysqli_query($conn, $query);
    $rounds = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rounds[] = $row;
    }
    return $rounds;
}

// Function to fetch mode options (Offline/Online)
function getModeOptions() {
    return [
        'offline' => 'Offline',
        'online' => 'Online'
    ];
}

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

// Check if company_id is passed in the URL

if (isset($_GET['company_id'])) {
    $company_id = intval($_GET['company_id']);

    // Fetch company information
    $company_query = "SELECT cmi.id as companyId, cmi.company_name, cpi.*, bi.id as batch_id, bi.batch_start_year, bi.batch_end_year
                      FROM campus_placement_info cpi
                      JOIN company_info cmi ON cpi.company_info_id = cmi.id
                      JOIN batch_info bi ON cpi.batch_info_id = bi.id
                      WHERE cmi.id = $company_id;";
    $company_result = mysqli_query($conn, $company_query);
    $company = mysqli_fetch_assoc($company_result);

    if (!$company) {
        die("Company not found.");
    }

    // Fetch rounds for the company
    $rounds = getCompanyRounds($company_id);
} else {
    die("Company ID is missing.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Campus Details</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 text-gray-800 flex h-screen overflow-hidden">
    <?php include('./sidebar.php'); ?>
    <div class="main-content pl-64 flex-1 ml-1/6 overflow-y-auto">
        <?php
        $page_title = "Edit Campus Details";
        include('./navbar.php');

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $date = !empty($_POST['date']) ? $_POST['date'] : null;
            $time = !empty($_POST['time']) ? $_POST['time'] : null;
            $location = $_POST['location'];
            $job_profile = $_POST['job_profile'];
            $package = $_POST['package'];
            $other_info = $_POST['other_info'];
            $batch_id = intval($_POST['batch_id']);
            $rounds_to_delete = isset($_POST['delete_rounds']) ? $_POST['delete_rounds'] : [];
            $round_names = $_POST['round_names']; // Names of rounds
            $round_modes = $_POST['round_modes']; // Modes for each round
            $round_indices = $_POST['round_index']; // Round indices
            $existing_round_ids = array_keys($round_indices); // Round IDs in the database
        
            // Update campus placement info
            $update_query = "UPDATE campus_placement_info 
                             SET date = " . ($date ? "'$date'" : "NULL") . ", 
                                 time = " . ($time ? "'$time'" : "NULL") . ", 
                                 location = '$location', 
                                 job_profile = '$job_profile', 
                                 package = '$package', 
                                 other_info = '$other_info',
                                 batch_info_id = $batch_id
                             WHERE company_info_id = $company_id";
        
            if (mysqli_query($conn, $update_query)) {
                // Delete selected rounds
                if (!empty($rounds_to_delete)) {
                    $rounds_to_delete_ids = implode(",", array_map('intval', $rounds_to_delete));
                    $delete_rounds_query = "DELETE FROM company_rounds_info WHERE id IN ($rounds_to_delete_ids) AND company_info_id = $company_id";
                    mysqli_query($conn, $delete_rounds_query);
                }
        
                // Update existing rounds
                foreach ($existing_round_ids as $index => $round_id) {
                    if (strpos($round_id, 'new-') === false) { // Ensure we don't update new rounds
                        $name = $round_names[$round_id];
                        $mode = $round_modes[$round_id];
                        $index = $round_indices[$round_id];
                        $update_round_query = "UPDATE company_rounds_info 
                                               SET round_name = '$name', mode = '$mode', round_index = $index+1 
                                               WHERE id = $round_id";
                        mysqli_query($conn, $update_round_query);
                    }
                }
        
                // Insert new rounds
                foreach ($_POST['round_names'] as $round_key => $round_name) {
                    if (strpos($round_key, 'new-') === 0) { // Check if it's a new round
                        $mode = $_POST['round_modes'][$round_key];
                        $index = $_POST['round_index'][$round_key];
                        $insert_round_query = "INSERT INTO company_rounds_info (company_info_id, round_name, mode, round_index) 
                                               VALUES ($company_id, '$round_name', '$mode', $index)";
                        mysqli_query($conn, $insert_round_query);
                    }
                }
        
                echo "<script>
                        Swal.fire({
                            title: 'Updated!',
                            text: 'Rounds have been updated successfully.',
                            icon: 'success'
                        }).then(function() {
                            window.location.href = 'campus_drive_company.php?company_id=" . $company_id . "';
                        });
                    </script>";
                exit;
            } else {
                echo "Error updating record: " . mysqli_error($conn);
            }
        }
        
        
        ?>

        <div class="container mx-auto p-6">

            <a href="javascript:history.back()" class="text-white bg-gray-700 p-2 px-5 rounded-full mb-4 hover:px-7 inline-block transition-all">
                <i class="fa-solid fa-angle-left"></i> Back
            </a>

            <!-- Company Name as Heading -->
            <h1 class="text-3xl font-bold ml-5 mb-6">
                <?php echo htmlspecialchars($company['company_name']); ?>
            </h1>

            <!-- Form for editing company details -->
            <form id="editForm" action="" method="POST" class="bg-white p-6 rounded-xl shadow-md">
                <div class="flex flex-wrap -mx-3">
                    <!-- Date Field -->
                    <div class="w-full md:w-1/6 px-3 mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Date</label>
                        <input type="date" name="date" value="<?php echo $company['date'] ? date('Y-m-d', strtotime($company['date'])) : ''; ?>" class="w-full p-3 border-2 rounded-xl">
                    </div>

                    <!-- Time Field -->
                    <div class="w-full md:w-1/6 px-3 mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Time</label>
                        <input type="time" name="time" value="<?php echo $company['time']; ?>" class="w-full p-3 border-2 rounded-xl">
                    </div>

                    <!-- Batch Dropdown -->
                    <div class="w-full md:w-1/6 px-3 mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Batch</label>
                        <select name="batch_id" class="w-full p-3 border-2 rounded-xl">
                            <?php foreach ($batches as $batch): ?>
                                <option value="<?php echo $batch['id']; ?>"
                                    <?php echo $company['batch_id'] == $batch['id'] ? 'selected' : ''; ?>>
                                    <?php echo $batch['batch_start_year'] . ' - ' . $batch['batch_end_year']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Work Location Field -->
                    <div class="w-full md:w-1/2 px-3 mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Work Location</label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($company['location']); ?>" class="w-full p-3 border-2 rounded-xl">
                    </div>
                </div>
                <div class="flex flex-wrap -mx-3">
                    <!-- Job Profile Field -->
                    <div class="w-full md:w-1/2 px-3 mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Job Profile</label>
                        <textarea name="job_profile" rows="3" class="w-full p-3 border-2 rounded-xl"><?php echo htmlspecialchars($company['job_profile']); ?></textarea>
                    </div>

                    <div class="w-full md:w-1/2 px-3 mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Package</label>
                        <textarea name="package" rows="3" class="w-full p-3 border-2 rounded-xl"><?php echo htmlspecialchars($company['package']); ?></textarea>
                    </div>
                </div>

                <!-- Rounds information -->
                <div class="w-full md:w-1/2 mb-4">
                <label class="block text-gray-700 font-bold mb-2">Selection Process Rounds</label>
                <div>
                    <?php foreach ($rounds as $index => $round): ?>
                        <div class="flex items-center ml-4 mb-2" id="round-<?php echo $round['id']; ?>">
                            <span class="mr-2 text-cyan-600 font-bold"><?php echo $index+1; ?>.</span>
                            <input type="text" name="round_names[<?php echo $round['id']; ?>]" value="<?php echo htmlspecialchars($round['round_name']); ?>" class="p-2 border-2 rounded-xl flex-1">
                            
                            <!-- Mode Dropdown -->
                            <select name="round_modes[<?php echo $round['id']; ?>]" class="p-2 border-2 rounded-xl ml-2">
                                <?php
                                    $mode_options = getModeOptions();
                                    $current_mode = $round['mode'] ? $round['mode'] : 'offline'; // Default to 'offline' if no mode exists
                                    foreach ($mode_options as $key => $value) {
                                        echo "<option value='$key' " . ($key == $current_mode ? "selected" : "") . ">$value</option>";
                                    }
                                ?>
                            </select>

                            <!-- Round Index -->
                            <input type="hidden" name="round_index[<?php echo $round['id']; ?>]" value="<?php echo $round['round_index']; ?>">

                            <!-- Delete Icon -->
                            <button type="button" class="text-red-500 text-sm bg-red-100 h-10 w-10 ml-4 rounded-xl hover:scale-110 transition-all" onclick="deleteRound(<?php echo $round['id']; ?>)">
                                <i class="fa fa-trash"></i>
                            </button>
                            <!-- Hidden input to mark for deletion -->
                            <input type="hidden" name="delete_rounds[]" value="" id="delete-<?php echo $round['id']; ?>">
                        </div>
                    
                        <?php endforeach; ?>
                        <div id="newRoundsContainer"></div>

                         <!-- Add New Round Button -->
                         <button type="button" id="addRoundBtn" class="bg-green-500 text-white px-5 p-3 rounded-full hover:px-7 font-bold hover:bg-green-600 transition-all mt-4">
                            Add New Round
                        </button>

                    </div>
                </div>


                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Other Info</label>
                    <textarea name="other_info" rows="10" class="w-full p-3 border-2 rounded-xl"><?php echo htmlspecialchars($company['other_info']); ?></textarea>
                </div>

                <!-- Submit Button -->
                <button type="button" id="saveBtn" class="bg-cyan-600 text-white px-5 p-3 rounded-full hover:px-7 font-bold hover:bg-cyan-700 transition-all">
                    Save Changes
                </button>
            </form>
        </div>
    </div>

    <script>
        // Function to mark a round for deletion and remove it from the UI
        function deleteRound(roundId) {
            // Mark the round for deletion
            document.getElementById('delete-' + roundId).value = roundId;

            // Remove the round div from the UI
            document.getElementById('round-' + roundId).style.display = "none";
        }

        document.getElementById('saveBtn').addEventListener('click', function () {
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
                    document.getElementById('editForm').submit();
                }
            });
        });

        let roundCount = <?php echo count($rounds); ?>; // Track the number of rounds (including existing ones)

        document.getElementById('addRoundBtn').addEventListener('click', function() {
            roundCount++;

            // Create new round input fields
            let newRoundHTML = `
                <div class="flex items-center ml-4 mb-2" id="round-new-${roundCount}">
                    <span class="mr-2 text-cyan-600 font-bold">${roundCount}.</span>
                    <input type="text" name="round_names[new-${roundCount}]" class="p-2 border-2 rounded-xl flex-1" placeholder="Enter round name" required>
                    
                    <select name="round_modes[new-${roundCount}]" class="p-2 border-2 rounded-xl ml-2" required>
                        <option value="offline">Offline</option>
                        <option value="online">Online</option>
                    </select>

                    <input type="hidden" name="round_index[new-${roundCount}]" value="${roundCount}"> <!-- Use dynamic index -->

                    <button type="button" class="text-red-500 text-sm bg-red-100 h-10 w-10 ml-4 rounded-xl hover:scale-110 transition-all" onclick="removeNewRound(${roundCount})">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            `;

            // Append the new round fields to the container
            document.getElementById('newRoundsContainer').insertAdjacentHTML('beforeend', newRoundHTML);
        });

        function removeNewRound(roundId) {
            // Remove the round div from the UI
            document.getElementById('round-new-' + roundId).remove();
        }

    </script>
</body>
</html>

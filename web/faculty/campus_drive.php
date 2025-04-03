<?php
include('../../api/db/db_connection.php');

// Fetch all companies
$companies_query = "SELECT cpi.id as driveId, cmi.id, cmi.company_name, cpi.date, cpi.time FROM campus_placement_info cpi JOIN company_info cmi ON cpi.company_info_id = cmi.id";
$companies_result = mysqli_query($conn, $companies_query);

// Fetch all batches
$batches_query = "SELECT * FROM batch_info";
$batches_result = mysqli_query($conn, $batches_query);

// Fetch the current year
$current_year = date("Y");

// Function to fetch campus placements by batch
function getPlacementsByBatch($batch_id) {
    global $conn;
    $query = "SELECT cmi.company_name, cpi.date, cpi.time 
              FROM campus_placement_info cpi 
              JOIN company_info cmi ON cpi.company_info_id = cmi.id
              WHERE cpi.batch_info_id = $batch_id";
    $result = mysqli_query($conn, $query);
    $placements = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $placements[] = $row;
    }
    return $placements;
}

// Handle AJAX request for companies by batch
if (isset($_GET['fetch_companies']) && isset($_GET['batch_id'])) {
    $batch_id = intval($_GET['batch_id']);
    $companies_query = "SELECT cpi.id as driveId, cmi.id, cmi.company_name, cpi.date, cpi.time 
                        FROM campus_placement_info cpi 
                        JOIN company_info cmi ON cpi.company_info_id = cmi.id
                        WHERE cpi.batch_info_id = $batch_id";
    $companies_result = mysqli_query($conn, $companies_query);
    
    if (mysqli_num_rows($companies_result) > 0) {
        echo '<table id="companies-table" class="min-w-full bg-white shadow-md rounded-md table-fixed">';
        echo '<thead>';
        echo '<tr class="bg-cyan-600 text-white rounded-t-md">';
        echo '<th class="border px-4 py-2 rounded-tl-md w-1/12">No</th>';
        echo '<th class="border px-4 py-2 w-5/12">Company Name</th>';
        echo '<th class="border px-4 py-2 w-3/12">Date</th>';
        echo '<th class="border px-4 py-2 rounded-tr-md w-3/12">Time</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        $counter = 1;
        while ($company = mysqli_fetch_assoc($companies_result)) {
            echo "<tr class='hover:bg-cyan-100 transition-all cursor-pointer' onclick=\"window.location.href='campus_drive_company.php?drive_id={$company['driveId']}'\">";
            echo "<td class='border px-4 py-2 text-center'>{$counter}</td>";
            echo "<td class='border px-4 py-2'>{$company['company_name']}</td>";
            echo "<td class='border px-4 py-2 text-center'>" . ($company['date'] ? date("d/m/Y", strtotime($company['date'])) : "Will be declared") . "</td>";
            echo "<td class='border px-4 py-2 text-center'>" . ($company['time'] ? date("g:i A", strtotime($company['time'])) : "Will be declared") . "</td>";
            echo "</tr>";
            $counter++;
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p class="text-gray-500 mb-5">No companies found for this batch.</p>';
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Drives</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 flex h-screen overflow-hidden">
    <?php include('./sidebar.php'); ?>
    <div class="main-content pl-64 flex-1 ml-1/6 overflow-y-auto">
        <?php
        $page_title = "Campus Drives";
        include('./navbar.php');
        ?>

        <div class="container mx-auto p-6">
            <!-- Add Campus Drive Button and Search Bar -->
            <div class="mb-6">
                <button onclick="window.location.href='add_campus_drive.php';" class="bg-cyan-500 shadow-md hover:shadow-xl px-6 text-white p-2 hover:bg-cyan-600 rounded-md transition-all">Add Campus Drive</button>
                <!-- Search Bar -->
                <input type="text" id="search" class="ml-5 shadow-lg pl-4 p-2 rounded-md w-1/2" placeholder="Search by companies" onkeyup="searchCompanies()">
                <!-- Batch Dropdown -->
                <select id="batchDropdown" class="ml-10 drop-shadow-md border-2 px-5 p-2 rounded-md" onchange="fetchCompaniesByBatch()">
                    <?php 
                    while ($batch = mysqli_fetch_assoc($batches_result)):
                        $selected = ($batch['batch_end_year'] == $current_year) ? "selected" : "";
                    ?>
                        <option value="<?php echo $batch['id']; ?>" <?php echo $selected; ?>>
                            <?php echo $batch['batch_start_year'] . '-' . $batch['batch_end_year']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Display Companies in Table -->
            <div id="companies-grid">
                <table id="companies-table" class="min-w-full bg-white shadow-md rounded-md table-fixed">
                    <thead>
                        <tr class="bg-gray-700 text-white">
                            <th class="border px-4 py-2 rounded-tl-md w-1/12">No</th>
                            <th class="border px-4 py-2 w-5/12">Company Name</th>
                            <th class="border px-4 py-2 w-3/12">Date</th>
                            <th class="border px-4 py-2 rounded-tr-md w-3/12">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        while ($company = mysqli_fetch_assoc($companies_result)): 
                        ?>
                            <tr class="hover:bg-gray-200 hover:font-bold cursor-pointer transition-all" onclick="window.location.href='campus_drive_company.php?drive_id=<?php echo $company['driveId']; ?>'">
                                <td class="border px-4 py-2 text-center"><?php echo $counter; ?></td>
                                <td class="border px-4 py-2"><?php echo $company['company_name']; ?></td>
                                <td class="border px-4 py-2 text-center"><?php echo $company['date'] ? date("d/m/Y", strtotime($company['date'])) : "Will be declared"; ?></td>
                                <td class="border px-4 py-2 text-center"><?php echo $company['time'] ? date("g:i A", strtotime($company['time'])) : "Will be declared"; ?></td>
                            </tr>
                        <?php 
                            $counter++;
                        endwhile; 
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Campus Placement Details by Batch -->
            <div id="placements-container" class="mt-6">
                <!-- The placements will be loaded here dynamically -->
            </div>
        </div>
    </div>

    <script>
        // Real-time search function for companies
        function searchCompanies() {
            const searchInput = document.getElementById('search').value.toLowerCase();
            const rows = document.querySelectorAll('#companies-table tbody tr');
            
            rows.forEach(row => {
                const companyName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                
                if (companyName.includes(searchInput)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Fetch companies based on selected batch
        function fetchCompaniesByBatch() {
            const batchId = document.getElementById('batchDropdown').value;
            
            if (batchId) {
                $.ajax({
                    url: 'campus_drive.php',
                    method: 'GET',
                    data: { fetch_companies: true, batch_id: batchId },
                    success: function(response) {
                        document.getElementById('companies-grid').innerHTML = response;
                    }
                });
            } else {
                document.getElementById('companies-grid').innerHTML = '';
            }
        }
    </script>
</body>
</html>
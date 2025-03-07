<?php
include('../../api/db/db_connection.php');

// Fetch all companies
$companies_query = "SELECT cpi.id as driveId,cmi.id, cmi.company_name, cpi.date, cpi.time FROM campus_placement_info cpi JOIN company_info cmi ON cpi.company_info_id = cmi.id";
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
    $companies_query = "SELECT cpi.id as driveId,cmi.id, cmi.company_name, cpi.date, cpi.time 
                         FROM campus_placement_info cpi 
                         JOIN company_info cmi ON cpi.company_info_id = cmi.id
                         WHERE cpi.batch_info_id = $batch_id";
    $companies_result = mysqli_query($conn, $companies_query);
    
    if (mysqli_num_rows($companies_result) > 0) {
        while ($company = mysqli_fetch_assoc($companies_result)): ?>
            <div class="company-item bg-white shadow-xl rounded-xl pl-5 p-3 hover:bg-cyan-600 hover:pl-10 hover:text-white hover:shadow-2xl transition-all" onclick="window.location.href='campus_drive_company.php?drive_id=<?php echo $company['driveId']; ?>'">
                <div class="flex justify-between items-center cursor-pointer toggle-rounds" data-company-id="<?php echo $company['driveId']; ?>">
                    <div class="flex items-center">
                        <h2 class="text-lg font-bold mr-2"><?php echo $company['company_name']; ?> </h2>
                        <span> - - | - - <strong>Date & Time: </strong>
                            <?php
                            if ($company['date'] && $company['time']) {
                                echo $company['date'] ? date("d/m/Y", strtotime($company['date'])) : "";
                                echo " - ";
                                echo $company['time'] ? date("g:i A", strtotime($company['time'])) : "";
                            } else {
                                echo "Will be declared";
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endwhile;
    } else {
        echo '<p class="text-white-500 mb-5">No companies found for this batch.</p>';
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus drives</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 flex h-screen overflow-hidden">
    <?php include('./sidebar.php'); ?>
    <div class="main-content pl-64 flex-1 ml-1/6 overflow-y-auto">
        <?php
        $page_title = "Campus drives";
        include('./navbar.php');
        ?>

        <div class="container mx-auto p-6">
            <!-- Add Campus Drive Button and Search Bar -->
            <div class="mb-4 flex items-center">
            <button onclick="window.location.href='add_campus_drive.php';" class="drop-shadow-md bg-cyan-500 px-6 hover:px-8 text-white p-2 hover:bg-cyan-600 rounded-full mb-4 transition-all">Add Campus Drive</button>

                <!-- Search Bar -->
                <input type="text" id="search" class="ml-10 drop-shadow-md border-2 pl-4 p-2 rounded-full w-1/2 mb-4" placeholder="Search Companies..." onkeyup="searchCompanies()">
                
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

            <!-- Display Companies -->
            <div id="companies-grid" class="grid grid-cols-1 gap-3">
                <?php while ($company = mysqli_fetch_assoc($companies_result)): ?>
                    <div class="company-item bg-white shadow-xl rounded-lg pl-5 p-3 hover:bg-cyan-600 hover:pl-10 hover:text-white hover:shadow-2xl transition-all" onclick="window.location.href='campus_drive_company.php?drive_id=<?php echo $company['driveId']; ?>'">
                        <div class="flex justify-between items-center cursor-pointer toggle-rounds" data-company-id="<?php echo $company['driveId']; ?>">
                            <div class="flex items-center">
                            <h2 class="text-lg font-bold mr-2"><?php echo $company['company_name']; ?> </h2>
                            <span> - - | - - <strong>Date & Time: </strong> 
                                <?php
                                if($company['date'] && $company['time']){
                                    echo $company['date'] ? date("d/m/Y", strtotime($company['date'])) : "";
                                    echo " - ";
                                    echo $company['time'] ? date("g:i A", strtotime($company['time'])) : "";
                                }
                                else{
                                    echo "Will be declared";
                                }
                                ?>
                            </span>
                        </div>
                        </div>
                    </div>
                <?php endwhile; ?>
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
            const companies = document.querySelectorAll('.company-item');
            
            companies.forEach(company => {
                const companyName = company.querySelector('h2').textContent.toLowerCase();
                
                if (companyName.includes(searchInput)) {
                    company.style.display = '';
                } else {
                    company.style.display = 'none';
                }
            });
        }

        // Fetch companies based on selected batch
        function fetchCompaniesByBatch() {
            const batchId = document.getElementById('batchDropdown').value;
            
            if (batchId) {
                $.ajax({
                    url: 'campus_drive.php', // Replace with your PHP file containing the `fetch_companies` logic
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

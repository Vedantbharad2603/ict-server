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

// Check if company_id is passed in the URL
if (isset($_GET['company_id'])) {
    $company_id = intval($_GET['company_id']);

    // Fetch company information from the database
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
    <title>Company Info</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body class="bg-gray-100 text-gray-800">

    <?php include('./sidebar.php'); ?>

    <div class="main-content pl-64 flex-1 ml-1/6 overflow-y-auto">
        <?php $page_title = "Campus Drive Company Information"; include('./navbar.php'); ?>

        <div class="container mx-auto p-6">
            <!-- Back Button -->
            <a href="campus_drive.php" class="text-white bg-gray-700 p-2 px-5 rounded-full mb-4  hover:px-7 inline-block transition-all">
                <i class="fa-solid fa-angle-left"></i> Back
            </a>
            <a href="campus_company_data_edit.php?company_id=<?php echo $company_id; ?>" class="ml-3 text-white bg-cyan-600 p-2 px-5 rounded-full mb-4 hover:px-7 inline-block transition-all">
                <i class="fa-regular fa-pen-to-square"></i> Edit
            </a>

            <!-- Display company information -->
            <div class="bg-white p-6 rounded-lg drop-shadow-xl">
                <p clas>
                <h1 class="text-3xl font-bold">
                    <?php echo $company['company_name']; ?>
                </h1>
                Batch : <?php echo $company['batch_start_year']; ?> - <?php echo $company['batch_end_year']; ?>
                </p>


               <div class="rounded-full w-full h-1 mt-2 bg-slate-100"></div>

                <!-- Grid Layout -->
                <div class="grid grid-cols-2 gap-4 mt-6">

                    <!-- Date & Time -->
                    <div>
                        <h2 class="text-xl mb-2 font-semibold flex items-center">
                            <div class="pl-1.5 py-3 bg-slate-600 rounded mr-2"></div>
                            <span>Date & Time</span>
                        </h2>
                        <div class="pl-5 text-cyan-600">
                            <?php
                            if ($company['date'] && $company['time']) {
                                echo $company['date'] ? date("d/m/Y", strtotime($company['date'])) : "";
                                echo " - ";
                                echo $company['time'] ? date("g:i A", strtotime($company['time'])) : "";
                            } else {
                                echo "Will be declared";
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Work Location -->
                    <div>
                        <h2 class="text-xl mb-2 font-semibold flex items-center">
                            <div class="pl-1.5 py-3 bg-slate-600 rounded mr-2"></div>
                            <span>Work Location</span>
                        </h2>
                        <div class="pl-5 text-cyan-600">
                            <?php echo $company['location']; ?>
                        </div>
                    </div>

                    <!-- Job Profile -->
                    <div>
                        <h2 class="text-xl mb-2 font-semibold flex items-center">
                            <div class="pl-1.5 py-3 bg-slate-600 rounded mr-2"></div>
                            <span>Job Profile</span>
                        </h2>
                        <div class="pl-5 text-cyan-600">
                            <?php echo nl2br(htmlspecialchars($company['job_profile'])); ?>
                        </div>
                    </div>

                    <!-- Package -->
                    <div>
                        <h2 class="text-xl mb-2 font-semibold flex items-center">
                            <div class="pl-1.5 py-3 bg-slate-600 rounded mr-2"></div>
                            <span>Package</span>
                        </h2>
                        <div class="pl-5 text-cyan-600">
                            <?php echo nl2br(htmlspecialchars($company['package'])); ?>
                        </div>
                    </div>

                </div>


                <!-- Display rounds information -->
                <h2 class="text-xl mt-5 mb-2 font-semibold flex items-center">
                    <div class="pl-1.5 py-3 bg-slate-600 rounded mr-2"></div>
                    <span>Selection Process</span>
                </h2>

                <?php if (!empty($rounds)): ?>
                <ul>
                    <?php foreach ($rounds as $index => $round): ?>
                        <li class="mb-3 pl-5">
                            <div class="flex items-center">
                                <strong class="mr-2"><?php echo $index + 1; ?>)</strong> <!-- Display index of the loop -->
                                <p class="text-cyan-600">
                                    <?php echo htmlspecialchars($round['round_name']); ?> 
                                    (<?php echo $round['mode']; ?>)
                                </p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="pl-5">No rounds found for this company.</p>
            <?php endif; ?>



                <h2 class="text-xl mt-5 mb-2 font-semibold flex items-center">
                    <div class="pl-1.5 py-3 bg-slate-600 rounded mr-3"></div>
                    <span>Other info</span>
                </h2>
                <div class="pl-5 text-cyan-600">
                    <?php
                        // Ensure the text is safe and formatted
                        echo nl2br(htmlspecialchars($company['other_info']));
                    ?>
                </div>

                
            </div>
        </div>
    </div>

</body>
</html>

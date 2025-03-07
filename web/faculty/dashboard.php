<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
</head>

<body class="bg-gray-100 text-gray-800 flex">

<?php include('./sidebar.php'); ?>

<!-- Main Content -->
<div class="main-content pl-64 flex-1">

<?php
$page_title = "Dashboard";
include('./navbar.php');

// Connect to the database
include('../../api/db/db_connection.php');

// Query to get the total number of holidays
$holiday_query = "SELECT COUNT(*) AS total_holidays FROM holiday_info";
$holiday_result = $conn->query($holiday_query);

$total_holidays = 0;
if ($holiday_result && $row = $holiday_result->fetch_assoc()) {
    $total_holidays = $row['total_holidays'];
}
// Query for semester-wise student count
$student_query = "
SELECT
    smi.sem,
    smi.edu_type,
    COUNT(*) AS total
FROM student_info si
JOIN sem_info smi ON si.sem_info_id = smi.id
GROUP BY smi.edu_type, smi.sem
ORDER BY smi.edu_type, smi.sem";
$student_result = $conn->query($student_query);

$student_data = [];
if ($student_result) {
    while ($row = $student_result->fetch_assoc()) {
        $student_data[] = $row;
    }
}

// Query to get the total number of students
$student_count_query = "SELECT COUNT(*) AS total_students FROM student_info";
$student_count_result = $conn->query($student_count_query);
$total_students = 0;
if ($student_count_result && $row = $student_count_result->fetch_assoc()) {
    $total_students = $row['total_students'];
}

// Query to get the total number of companies
$company_count_query = "SELECT COUNT(*) AS total_subjects FROM subject_info";
$company_count_result = $conn->query($company_count_query);
$total_subjects = 0;

if ($company_count_result && $row = $company_count_result->fetch_assoc()) {
    $total_subjects = $row['total_subjects'];
}

// Query to get the total number of faculties
$faculty_count_query = "SELECT COUNT(*) AS total_faculties FROM faculty_info";
$faculty_count_result = $conn->query($faculty_count_query);

$total_faculties = 0;
if ($faculty_count_result && $row = $faculty_count_result->fetch_assoc()) {
    $total_faculties = $row['total_faculties'];
}

?>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Holiday Card -->
            <div class="bg-white border border-yellow-500 rounded-xl p-6 text-center transform transition-all hover:scale-105 cursor-pointer group"
                onclick="window.location.href='holiday.php';">
                <div class="flex items-center">
                <i class="fa-solid fa-sun text-4xl text-yellow-500 "></i>
                    <h2 class="text-yellow-600 text-xl pl-5 font-semibold ">
                        Holidays
                    </h2>
                </div>
                <p id="holiday-count" class="text-5xl font-bold text-yellow-500 ">
                    00
                </p>
            </div>


            <!-- Total Students Card -->
            <div class="bg-white border border-cyan-500 rounded-xl p-6 text-center transform transition-all hover:scale-105 cursor-pointer group"
            onclick="window.location.href='student_search_page.php';">
                <div class="flex items-center">
                <i class="fa-solid fa-users text-3xl text-cyan-500 "></i>
                    <h2 class="text-cyan-600 text-lg pl-5 font-semibold ">
                        Total Students
                    </h2>
                </div>
                <p id="student-count" class="text-5xl font-bold text-cyan-500 ">
                    00
                </p>
            </div>


            <!-- Total Faculties Card -->
            <div class="bg-white border border-blue-500 rounded-xl p-6 text-center transform transition-all hover:scale-105 cursor-pointer group"
            onclick="window.location.href='faculty_list.php';">
                <div class="flex items-center">
                <i class="fa-solid fa-chalkboard-user text-3xl text-blue-500 "></i>
                    <h2 class="text-blue-600 text-lg pl-5 font-semibold ">
                        Total Faculties
                    </h2>
                </div>
                <p id="faculty-count" class="text-5xl font-bold text-blue-500 ">
                    00
                </p>
            </div>

             <!-- Total subjects Card -->
             <div class="bg-white border border-green-500 rounded-xl p-6 text-center transform transition-all hover:scale-105 cursor-pointer group">
                <div class="flex items-center">
                <i class="fa-solid fa-book text-3xl text-green-500 "></i>
                    <h2 class="text-green-600 text-lg pl-5 font-semibold ">
                        Total Subjects
                    </h2>
                </div>
                <p id="subject-count" class="text-5xl font-bold text-green-500 ">
                    00
                </p>
            </div>


        </div>

    </div>
</div>

<!-- Add JavaScript for Count-Up Animation and Bar Chart -->
<script>
    // Count-Up Animation Function
    const countUp = (elementId, start, end, duration) => {
        const element = document.getElementById(elementId);
        const range = end - start;
        const stepTime = Math.abs(Math.floor(duration / range));
        let current = start;
        const increment = end > start ? 1 : -1;

        const timer = setInterval(() => {
            current += increment;
            element.textContent = current;

            if (current === end) {
                clearInterval(timer);
            }
        }, stepTime);
    };

    // Animate Holiday Count
    const totalHolidays = <?php echo $total_holidays; ?>;
    countUp('holiday-count', 0, totalHolidays, 1000);

    // Animate Total Students Count
    const totalStudents = <?php echo $total_students; ?>;
    countUp('student-count', 0, totalStudents, 1000);

    // Animate Total Faculties Count
    const totalFaculties = <?php echo $total_faculties; ?>;
    countUp('faculty-count', 0, totalFaculties, 1000);

    // Animate Holiday Count
    const totalSubjectss = <?php echo $total_subjects; ?>;
    countUp('subject-count', 0, totalSubjectss, 1000);


</script>

</body>
</html>
    
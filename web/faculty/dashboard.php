<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
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

// Query to get the total number of faculties
$faculty_count_query = "SELECT COUNT(*) AS total_faculties FROM faculty_info";
$faculty_count_result = $conn->query($faculty_count_query);

$total_faculties = 0;
if ($faculty_count_result && $row = $faculty_count_result->fetch_assoc()) {
    $total_faculties = $row['total_faculties'];
}

?>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Holiday Card -->
            <div 
                class="bg-white shadow-md rounded-lg p-6 text-center transform transition-transform hover:scale-105 cursor-pointer"
                onclick="window.location.href='holiday.php';"
            >
                <h2 class="text-xl font-semibold mb-2">Total Holidays</h2>
                <p id="holiday-count" class="text-4xl font-bold text-cyan-500">0</p>
            </div>

            <!-- Total Students Card -->
            <div 
                class="bg-white shadow-md rounded-lg p-6 text-center transform transition-transform hover:scale-105 cursor-pointer"
            >
                <h2 class="text-xl font-semibold mb-2">Total Students</h2>
                <p id="student-count" class="text-4xl font-bold text-cyan-500">0</p>
            </div>

            <!-- Total Faculties Card -->
            <div 
                class="bg-white shadow-md rounded-lg p-6 text-center transform transition-transform hover:scale-105 cursor-pointer">
                <h2 class="text-xl font-semibold mb-2">Total Faculties</h2>
                <p id="faculty-count" class="text-4xl font-bold text-cyan-500">0</p>
            </div>
        </div>

        <!-- Bar Chart Section -->
        <div class="mt-8 h-96">
            <h2 class="text-xl font-bold mb-4">Semester-Wise Student Count</h2>
            <canvas id="studentBarChart" class="bg-white p-5 shadow-md rounded-lg "></canvas>
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
    countUp('student-count', 0, totalStudents, 1500);

    // Animate Total Faculties Count
    const totalFaculties = <?php echo $total_faculties; ?>;
    countUp('faculty-count', 0, totalFaculties, 1500);

    // Bar Chart Data
    const studentData = <?php echo json_encode($student_data); ?>;

    // Parse data for Chart.js
    const labels = [];
    const degreeData = [];
    const diplomaData = [];
    studentData.forEach(entry => {
        const label = `Sem ${entry.sem}`;
        if (!labels.includes(label)) {
            labels.push(label);
        }
        if (entry.edu_type === 'degree') {
            degreeData.push(entry.total);
        } else if (entry.edu_type === 'diploma') {
            diplomaData.push(entry.total);
        }
    });

    // Fill missing data for alignment
    while (degreeData.length < labels.length) degreeData.push(0);
    while (diplomaData.length < labels.length) diplomaData.push(0);

    // Chart.js Bar Chart
    const ctx = document.getElementById('studentBarChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Degree Students',
                    data: degreeData,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                },
                {
                    label: 'Diploma Students',
                    data: diplomaData,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Semester-Wise Student Count',
                },
            },
            scales: {
                x: {
                    beginAtZero: true,
                },
                y: {
                    beginAtZero: true,
                },
            },
        },
    });
</script>

</body>
</html>
    
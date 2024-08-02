<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Database connection
$host = 'localhost';
$db = 'timebuddy';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Fetch semester and department values from input table
$sql_select_semester = "SELECT sem, dept FROM input LIMIT 1"; // Assuming only one row is expected
$stmt_select = $pdo->query($sql_select_semester);
$row = $stmt_select->fetch();

if (!$row) {
    echo "No semester and department data found in the 'input' table";
    exit; // Exit the script if semester data is not found
}

$sem = $row['sem'];
$dept = $row['dept'];

// Truncate the input table
$sql_truncate_input = "TRUNCATE TABLE input";
$pdo->exec($sql_truncate_input);

// Define the years to filter by
$years = [1, 2, 3, 4]; // Year values

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set default styles
$defaultStyle = [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

// Set the row headers for the timetable (days)
$dayMap = [
    'MON' => 'Monday',
    'TUE' => 'Tuesday',
    'WED' => 'Wednesday',
    'THU' => 'Thursday',
    'FRI' => 'Friday',
];

// Starting position
$startRow = 1;

foreach ($years as $year) {
    // Fetch data from database for the given year and semester
    $stmt = $pdo->prepare('SELECT pno, day, subject FROM classtable WHERE year = :year AND sem = :sem AND dept = :dept ORDER BY day, pno');
    $stmt->execute(['year' => $year, 'sem' => $sem, 'dept' => $dept]);
    $rows = $stmt->fetchAll();

    // Set the title for the year and semester
    $sheet->setCellValue('A' . $startRow, "Year: $year, Semester: $sem, Department: $dept");
    $sheet->getStyle('A' . $startRow)->applyFromArray($defaultStyle); // Apply default style
    $startRow++;

    // Set the column headers for the periods
    $sheet->setCellValue('A' . $startRow, 'Day/Period');
    $columnIndex = 'B';
    for ($period = 1; $period <= 6; $period++) { // Assuming 6 periods per day
        $sheet->setCellValue($columnIndex . $startRow, 'P' . $period);
        $sheet->getStyle($columnIndex . $startRow)->applyFromArray($defaultStyle); // Apply default style
        $sheet->getColumnDimension($columnIndex)->setWidth(15); // Adjust column width
        $columnIndex++;
    }
    $startRow++;

    // Initialize the timetable array
    $timetable = [];
    foreach ($dayMap as $shortDay => $fullDay) {
        $timetable[$shortDay] = array_fill(1, 6, ''); // 6 periods
    }

    // Populate the timetable array with subjects
    foreach ($rows as $row) {
        $period = $row['pno'];
        $day = $row['day'];
        $subject = $row['subject'];
        $timetable[$day][$period] = $subject;
    }

    // Write the timetable array to the spreadsheet
    foreach ($dayMap as $shortDay => $fullDay) {
        $sheet->setCellValue('A' . $startRow, $fullDay);
        $sheet->getStyle('A' . $startRow)->applyFromArray($defaultStyle); // Apply default style
        $columnIndex = 'B';
        for ($period = 1; $period <= 6; $period++) {
            $sheet->setCellValue($columnIndex . $startRow, $timetable[$shortDay][$period]);
            $sheet->getStyle($columnIndex . $startRow)->applyFromArray($defaultStyle); // Apply default style
            $columnIndex++;
        }
        $startRow++;
    }

    // Add an empty row between timetables
    $startRow++;
}

// Apply additional styles if needed
// Example: Setting background color for specific cells
$sheet->getStyle('B3:G8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000'); // Red background for cells B3 to G8

// Export the spreadsheet
$writer = new Xlsx($spreadsheet);
$filename = 'timetable_sem_' . $sem . '.xlsx';

// Set headers to force download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: cache, must-revalidate');
header('Pragma: public');

ob_clean(); // Clear output buffer before sending spreadsheet
$writer->save('php://output');
exit;

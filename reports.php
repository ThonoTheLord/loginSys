<?php
ob_start();
ob_end_flush();
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'includes/dbh.inc.php';
require_once 'includes/functions.inc.php';

$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$department = $_POST['department'] ?? '';
$employee_id = $_POST['employee_id'] ?? '';
$export_type = $_POST['export_type'] ?? '';

$departments = [];
$employees = [];

try {
    
    $stmt = $conn->prepare("SELECT DISTINCT department FROM employees WHERE department IS NOT NULL");
    $stmt->execute();
    $departments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt = $conn->prepare("SELECT id, full_name FROM employees");
    $stmt->execute();
    $employees = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    header("Location: reports.php?error=dberror");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {

        $query = "SELECT e.full_name, e.department, c.timestamp, c.code_used 
                 FROM clock_ins c
                 JOIN employees e ON c.employee_id = e.id
                 WHERE 1=1";
        
        $params = [];
        $types = '';

        if (!empty($start_date)) {
            $query .= " AND DATE(c.timestamp) >= ?";
            $params[] = $start_date;
            $types .= 's';
        }
        
        if (!empty($end_date)) {
            $query .= " AND DATE(c.timestamp) <= ?";
            $params[] = $end_date;
            $types .= 's';
        }
        
        if (!empty($department)) {
            $query .= " AND e.department = ?";
            $params[] = $department;
            $types .= 's';
        }
        
        if (!empty($employee_id)) {
            $query .= " AND c.employee_id = ?";
            $params[] = $employee_id;
            $types .= 'i';
        }

        $query .= " ORDER BY c.timestamp DESC";

        $stmt = $conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if ($export_type === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="clock_in_report.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Employee Name', 'Department', 'Timestamp', 'Code Used']);
            
            foreach ($results as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
            exit();
            
        } elseif ($export_type === 'pdf') {
            require_once('fpdf/fpdf.php');
            
            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial','B',12);
            
            $pdf->Cell(60,10,'Employee Name',1);
            $pdf->Cell(40,10,'Department',1);
            $pdf->Cell(60,10,'Timestamp',1);
            $pdf->Cell(30,10,'Code Used',1);
            $pdf->Ln();
            
            $pdf->SetFont('Arial','',10);
            foreach ($results as $row) {
                $pdf->Cell(60,10,$row['full_name']);
                $pdf->Cell(40,10,$row['department']);
                $pdf->Cell(60,10,$row['timestamp']);
                $pdf->Cell(30,10,$row['code_used']);
                $pdf->Ln();
            }
            
            $pdf->Output('D', 'clock_in_report.pdf');
            exit();
        }

    } catch (Exception $e) {
        header("Location: reports.php?error=generationerror");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Digital CitiZen's</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once 'header.php'; ?>

    <main class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h2 class="mb-4">Generate Reports</h2>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="department">
                                <option value="">All Departments</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= htmlspecialchars($dept['department']) ?>">
                                        <?= htmlspecialchars($dept['department']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Employee</label>
                            <select class="form-select" name="employee_id">
                                <option value="">All Employees</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= htmlspecialchars($emp['id']) ?>">
                                        <?= htmlspecialchars($emp['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="export_type" 
                                       id="csv" value="csv" checked>
                                <label class="form-check-label" for="csv">CSV Export</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="export_type" 
                                       id="pdf" value="pdf">
                                <label class="form-check-label" for="pdf">PDF Export</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6 text-end">
                            <button type="submit" class="btn btn-primary">Generate Report</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
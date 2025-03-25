<?php
include_once 'db.php';

// Initialize variables for pre-filling employee and department data
$emp_name = $salary = $dept_id = '';
$dept_name = '';

// Handle edit employee request
if (isset($_GET['edit_employee'])) {
    $emp_id = $_GET['edit_employee'];
    $result = $conn->query("SELECT * FROM employees WHERE emp_id = $emp_id");
    if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();
        $emp_name = $employee['name'];
        $salary = $employee['salary'];
        $dept_id = $employee['department_id'];
    }
}

// Handle edit department request
if (isset($_GET['edit_department'])) {
    $dept_id = $_GET['edit_department'];
    $result = $conn->query("SELECT * FROM departments WHERE department_id = $dept_id");
    if ($result->num_rows > 0) {
        $department = $result->fetch_assoc();
        $dept_name = $department['department_name'];
    }
}

// Add or update employee
if (isset($_POST['emp_name'])) {
    $emp_name = $_POST['emp_name'];
    $dept_id = $_POST['department_id'];
    $salary = $_POST['salary'];

    if (isset($_POST['emp_id']) && $_POST['emp_id']) {
        $emp_id = $_POST['emp_id'];
        $sql = "UPDATE employees SET name='$emp_name', department_id=$dept_id, salary=$salary WHERE emp_id=$emp_id";
    } else {
        $sql = "INSERT INTO employees (name, department_id, salary) VALUES ('$emp_name', $dept_id, $salary)";
    }

    $conn->query($sql);
    header('Location: ' . $_SERVER['PHP_SELF']); // Prevent form resubmission
}

// Add or update department
if (isset($_POST['save_department'])) {
    $dept_name = $_POST['dept_name'];

    if (isset($_POST['department_id']) && $_POST['department_id']) {
        $dept_id = $_POST['department_id'];
        $sql = "UPDATE departments SET department_name='$dept_name' WHERE department_id=$dept_id";
    } else {
        $sql = "INSERT INTO departments (department_name) VALUES ('$dept_name')";
    }
    $conn->query($sql);
    header('Location: ' . $_SERVER['PHP_SELF']); // Prevent form resubmission
}

// Delete employee
if (isset($_GET['delete_employee'])) {
    $emp_id = $_GET['delete_employee'];
    $sql = "DELETE FROM employees WHERE emp_id=$emp_id";
    $conn->query($sql);
    header('Location: ' . $_SERVER['PHP_SELF']); // Prevent form resubmission
}

// Delete department
if (isset($_GET['delete_department'])) {
    $dept_id = $_GET['delete_department'];
    $sql = "DELETE FROM departments WHERE department_id=$dept_id";
    $conn->query($sql);
    header('Location: ' . $_SERVER['PHP_SELF']); // Prevent form resubmission
}

// Perform different joins
function performJoin($conn, $joinType) {
    switch ($joinType) {
        case 'left_join':
            $sql = "SELECT employees.name, departments.department_name, employees.salary 
                    FROM employees 
                    LEFT JOIN departments ON employees.department_id = departments.department_id";
            break;
        case 'right_join':
            $sql = "SELECT employees.name, departments.department_name, employees.salary 
                    FROM employees 
                    RIGHT JOIN departments ON employees.department_id = departments.department_id";
            break;
        case 'full_outer_join':
            $sql = "(SELECT employees.name, departments.department_name, employees.salary 
                    FROM employees 
                    LEFT JOIN departments ON employees.department_id = departments.department_id) 
                    UNION 
                    (SELECT employees.name, departments.department_name, employees.salary 
                    FROM employees 
                    RIGHT JOIN departments ON employees.department_id = departments.department_id)";
            break;
        case 'self_join':
            $sql = "SELECT e1.name AS Employee, e2.name AS Manager
                    FROM employees e1
                    JOIN employees e2 ON e1.department_id = e2.department_id 
                    WHERE e1.emp_id != e2.emp_id";
            break;
        default:
            return [];
    }
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

$joinType = isset($_POST['join_type']) ? $_POST['join_type'] : 'left_join';
$joinResults = performJoin($conn, $joinType);

$employees = $conn->query("SELECT * FROM employees")->fetch_all(MYSQLI_ASSOC);
$departments = $conn->query("SELECT * FROM departments")->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Management with Joins</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-image: linear-gradient(to right, blue, darkorange);
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .form-section {
            margin-bottom: 40px;
        }
        input, select, button {
            padding: 10px;
            margin-bottom: 20px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Data Management with Joins</h1>
        <div class="form-section">
            <h2>Add or Update Employee</h2>
            <form method="POST">
                <input type="hidden" name="emp_id" value="<?php echo isset($_GET['edit_employee']) ? $_GET['edit_employee'] : ''; ?>">
                <label>Name: </label>
                <input type="text" name="emp_name" value="<?php echo $emp_name; ?>" required>
                <label>Department: </label>
                <select name="department_id">
                    <?php foreach ($departments as $department) : ?>
                        <option value="<?php echo $department['department_id']; ?>" 
                            <?php echo $dept_id == $department['department_id'] ? 'selected' : ''; ?>>
                            <?php echo $department['department_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>Salary:</label>
                <input type="number" name="salary" value="<?php echo $salary; ?>" step="0.01" required>
                <button type="submit" name="save_employee">Save Employee</button>
            </form>
        </div>
        <div class="form-section">
            <h2>Add or Update Department</h2>
            <form method="POST">
                <input type="hidden" name="department_id" value="<?php echo $dept_id; ?>">
                <label>Department Name: </label>
                <input type="text" name="dept_name" value="<?php echo $dept_name; ?>" required>
                <button type="submit" name="save_department">Save Department</button>
            </form>
        </div>

        <h2>Employees</h2>
        <table>
            <tr>
                <th>ID</th><th>Name</th><th>Department</th><th>Salary</th><th>Actions</th>
            </tr>
            <?php foreach ($employees as $employee) : ?>
                <tr>
                    <td><?php echo $employee['emp_id']; ?></td>
                    <td><?php echo $employee['name']; ?></td>
                    <td>
                        <?php
                        $dept = array_filter($departments, function($dept) use ($employee) {
                            return $dept['department_id'] == $employee['department_id'];
                        });
                        echo !empty($dept) ? reset($dept)['department_name'] : 'N/A';
                        ?>
                    </td>
                    <td><?php echo $employee['salary']; ?></td>
                    <td><a href="?edit_employee=<?php echo $employee['emp_id']; ?>">Edit</a>
                     | <a href="?delete_employee=<?php echo $employee['emp_id']; ?>">Delete</a></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h2>Departments</h2>
        <table>
            <tr>
                <th>ID</th><th>Department</th><th>Actions</th>
            </tr>
            <?php foreach ($departments as $department) : ?>
                <tr>
                    <td><?php echo $department['department_id']; ?></td>
                    <td><?php echo $department['department_name']; ?></td>
                    <td><a href="?edit_department=<?php echo $department['department_id']; ?>">Edit</a>
                     | <a href="?delete_department=<?php echo $department['department_id']; ?>">Delete</a></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h2>Join Results</h2>
        <table>
            <tr>
                <?php if (!empty($joinResults)) : ?>
                    <?php foreach (array_keys($joinResults[0]) as $key) : ?>
                        <th><?php echo htmlspecialchars($key); ?></th>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tr>
            <?php foreach ($joinResults as $row) : ?>
                <tr>
                    <?php foreach ($row as $cell) : ?>
                        <td><?php echo htmlspecialchars($cell); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>

        <div class="form-section">
            <h2>Select Join Type</h2>
            <form method="POST">
                <select name="join_type">
                    <option value="left_join" <?php echo $joinType == 'left_join' ? 'selected' : ''; ?>>Left Join</option>
                    <option value="right_join" <?php echo $joinType == 'right_join' ? 'selected' : ''; ?>>Right Join</option>
                    <option value="full_outer_join" <?php echo $joinType == 'full_outer_join' ? 'selected' : ''; ?>>Full Outer Join</option>
                    <option value="self_join" <?php echo $joinType == 'self_join' ? 'selected' : ''; ?>>Self Join</option>
                </select>
                <button type="submit">Show Join Results</button>
            </form>
        </div>
    </div>
</body>
</html>

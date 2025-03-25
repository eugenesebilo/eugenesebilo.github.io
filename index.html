<?php
include_once 'db.php';

// Use prepared statements to prevent SQL injection
function createItem($name, $description, $conn) {
    $stmt = $conn->prepare("INSERT INTO items (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $description);

    if ($stmt->execute()) {
        return "New record created successfully";
    } else {
        return "Error creating record: " . $stmt->error;
    }
}

function getItems($conn) {
    $sql = "SELECT * FROM items";
    return $conn->query($sql);
}

function updateItem($id, $name, $description, $conn) {
    $stmt = $conn->prepare("UPDATE items SET name = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $description, $id);

    if ($stmt->execute()) {
        return "Record updated successfully.";
    } else {
        return "Error updating record: " . $stmt->error;
    }
}

function deleteItem($id, $conn) {
    $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        return "Record deleted successfully.";
    } else {
        return "Error deleting record: " . $stmt->error;
    }
}

$message = '';
if (isset($_POST['create'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $message = createItem($name, $description, $conn);
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $message = updateItem($id, $name, $description, $conn);
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $message = deleteItem($id, $conn);
}

$item = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PHP MySQL CRUD</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="container">
        <h1>PHP MySQL CRUD APPLICATION</h1>
        <?php if ($message): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <form action="index.php" method="POST">
            <input type="text" name="name" placeholder="Item Name" required>
            <input type="text" name="description" placeholder="Item Description" required>
            <button type="submit" name="create">Add Item</button>
        </form>

        <h2>Items List</h2>
        <table>
            <tr>
                <th>ID</th><th>Name</th><th>Description</th><th>Actions</th>
            </tr>
            <?php
            $result = getItems($conn);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['description']}</td>
                        <td>
                            <a href='index.php?delete={$row['id']}' class='btn-delete'>Delete</a>
                            <a href='index.php?edit={$row['id']}' class='btn-edit'>Edit</a>
                        </td>
                    </tr>";
                }
            }
            ?>
        </table>

        <?php if ($item): ?>
            <h2>Edit Item</h2>
            <form action="index.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                <input type="text" name="name" value="<?php echo $item['name']; ?>" required>
                <input type="text" name="description" value="<?php echo $item['description']; ?>" required>
                <button type="submit" name="update">Update Item</button>
            </form>
        <?php endif; ?>
        <a href="index.php">Home</a>
        <a href="next1.php">Next</a>
    </div>
</body>
</html>

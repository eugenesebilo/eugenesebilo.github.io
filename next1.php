<?php

include_once 'db.php';

function insertItem($name, $price, $category, $conn) {
    $sql = "INSERT INTO products(name, price, category) VALUES ('$name', '$price', '$category')";
    if ($conn->query($sql) === TRUE) {
        return "New record created successfully";
    } else {
        return "Error creating record: " . $sql . "<br>" . $conn->error;
    }
}

function getItems($conn, $filter = "") {
    $sql = "SELECT * FROM products";
    if (!empty($filter)) {
        // Use prepared statements to avoid SQL injection
        $stmt = $conn->prepare("SELECT * FROM products WHERE category = ? ORDER BY price DESC LIMIT 10");
        $stmt->bind_param("s", $filter);
    } else {
        $sql .= " ORDER BY price DESC LIMIT 10";
        $result = $conn->query($sql);
        return $result;
    }
    
    $stmt->execute();
    return $stmt->get_result();
}

$message = '';
if (isset($_POST['create'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $message = insertItem($name, $price, $category, $conn);
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>With Functions and Clauses</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="container">
        <h1>PHP MySQL with Functions and Clauses</h1>
        <?php if (isset($message)) {
            echo "<p class='message'>" . htmlspecialchars($message) . "</p>";
        } ?>
        <form action="next1.php" method="POST">
            <input type="text" name="name" placeholder="Product Name" required>
            <input type="number" step="0.01" name="price" placeholder="Product Price" required>
            <select name="category" required>
                <option value="Electronics">Electronics</option>
                <option value="Books">Books</option>
                <option value="Clothing">Clothing</option>
            </select>
            <button type="submit" name="create">Add Product</button>
        </form>
        
        <h2>Filter by Category</h2>
        <a href="next1.php">All</a>
        <a href="next1.php?filter=Electronics">Electronics</a>
        <a href="next1.php?filter=Books">Books</a>
        <a href="next1.php?filter=Clothing">Clothing</a>
        
        <h2>Product List</h2>
        <table>
            <tr>
                <th>ID</th><th>Name</th><th>Price</th><th>Category</th>
            </tr>
            <?php
            $result = getItems($conn, $filter);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['name']}</td>
                        <td>\${$row['price']}</td>
                        <td>{$row['category']}</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No Products found.</td></tr>";
            }
            ?>
        </table>
        <a href="index.php">Home</a>
        <a href="next2.php">Next</a>
    </div>
</body>
</html>

<?php

include_once 'db.php';

// ==================== Category Management ====================

// Function to create a new category
function createCategory($name, $conn) {
    $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->bind_param("s", $name);

    if ($stmt->execute()) {
        return "New category created successfully.";
    } else {
        if ($conn->errno == 1062) { // Duplicate entry
            return "Category already exists.";
        }
        return "Error creating category: " . $stmt->error;
    }
}

// Function to retrieve all categories
function getCategories($conn) {
    $sql = "SELECT * FROM categories ORDER BY name ASC";
    return $conn->query($sql);
}

// Function to delete a category
function deleteCategory($id, $conn) {
    // Before deleting, check if any products are using this category
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM products WHERE category_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];

    if ($count > 0) {
        return "Cannot delete category. There are products assigned to it.";
    }

    // Proceed to delete
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        return "Category deleted successfully.";
    } else {
        return "Error deleting category: " . $stmt->error;
    }
}

// ==================== Product Management ====================

// Insert a new product
function insertItem($name, $price, $category_id, $conn) {
    $stmt = $conn->prepare("INSERT INTO products (name, price, category_id) VALUES (?, ?, ?)");
    $stmt->bind_param("sdi", $name, $price, $category_id);

    if ($stmt->execute()) {
        return "New product created successfully.";
    } else {
        return "Error creating product: " . $stmt->error;
    }
}

// Fetch all products with optional filtering by category
function getItems($conn, $filter = "") {
    if (!empty($filter)) {
        $stmt = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE c.name = ? ORDER BY p.price DESC LIMIT 10");
        $stmt->bind_param("s", $filter);
        $stmt->execute();
        return $stmt->get_result();
    } else {
        $sql = "SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.price DESC LIMIT 10";
        return $conn->query($sql);
    }
}

// Get category statistics (count and average price by category)
function getCategoryStats($conn) {
    $sql = "SELECT c.name AS category, COUNT(p.id) AS total_products, AVG(p.price) AS average_price 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id 
            GROUP BY c.name 
            ORDER BY c.name ASC";
    return $conn->query($sql);
}

// Update product details
function updateItem($id, $name, $price, $category_id, $conn) {
    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, category_id = ? WHERE id = ?");
    $stmt->bind_param("sdii", $name, $price, $category_id, $id);

    if ($stmt->execute()) {
        return "Product updated successfully.";
    } else {
        return "Error updating product: " . $stmt->error;
    }
}

// Delete a product from the database
function deleteItem($id, $conn) {
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        return "Product deleted successfully.";
    } else {
        return "Error deleting product: " . $stmt->error;
    }
}

// ==================== Handling Requests ====================

$message = '';

// Handle product creation
if (isset($_POST['create'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $category_id = $_POST['category'];
    $message = insertItem($name, $price, $category_id, $conn);
}

// Handle product update
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $category_id = $_POST['category'];
    $message = updateItem($id, $name, $price, $category_id, $conn);
}

// Handle product deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $message = deleteItem($id, $conn);
}

// Handle category creation
if (isset($_POST['create_category'])) {
    $category_name = $_POST['category_name'];
    $message = createCategory($category_name, $conn);
}

// Handle category deletion
if (isset($_GET['delete_category'])) {
    $id = $_GET['delete_category'];
    $message = deleteCategory($id, $conn);
}

// Fetch the product details for editing
$item = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PHP MySQL: DISTINCT and GROUP BY</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <!-- Optional: Include some JavaScript for better UX, e.g., modal handling -->
</head>
<body>
    <div class="container">
        <h1>PHP MySQL: DISTINCT and GROUP BY</h1>
        
        <?php if ($message): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <!-- Form to add a new product -->
        <form action="next2.php" method="POST">
            <h2>Add New Product</h2>
            <input type="text" name="name" placeholder="Product Name" required>
            <input type="number" step="0.01" name="price" placeholder="Product Price" required>
            <select name="category" required>
                <option value="">Select Category</option>
                <?php
                $categories = getCategories($conn);
                if ($categories->num_rows > 0) {
                    while ($row = $categories->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                    }
                }
                ?>
            </select>
            <button type="submit" name="create">Add Product</button>
        </form>

        <!-- Form to add a new category -->
        <div class="category-management">
            <h2>Manage Categories</h2>
            <div class="category-actions">
                <a href="#" id="show-add-category-form" class="btn-add-category">Create New Category</a>
            </div>
            <div id="add-category-form" style="display: none;">
                <form action="next2.php" method="POST">
                    <input type="text" name="category_name" placeholder="New Category Name" required>
                    <button type="submit" name="create_category">Add Category</button>
                    <button type="button" id="cancel-add-category" class="btn-cancel">Cancel</button>
                </form>
            </div>

            <h3>Distinct Categories</h3>
            <div class="category-links">
                <a href="next2.php">All</a>
                <?php
                // Reset the categories result set
                $categories->data_seek(0);
                if ($categories->num_rows > 0) {
                    while ($row = $categories->fetch_assoc()) {
                        echo "<a href='next2.php?filter=" . urlencode($row['name']) . "'>{$row['name']}</a>";
                    }
                }
                ?>
            </div>
        </div>

        <!-- Product List -->
        <h2>Product List</h2>
        <table>
            <tr>
                <th>ID</th><th>Name</th><th>Price</th><th>Category</th><th>Actions</th>
            </tr>
            <?php
            // Reset the categories result set
            $categories->data_seek(0);
            $result = getItems($conn, $filter);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['name']}</td>
                        <td>\${$row['price']}</td>
                        <td>{$row['category_name']}</td>
                        <td>
                            <a href='next2.php?delete={$row['id']}' class='btn-delete' onclick=\"return confirm('Are you sure you want to delete this product?');\">Delete</a>
                            <a href='next2.php?edit={$row['id']}' class='btn-edit'>Edit</a>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No Products found.</td></tr>";
            }
            ?>
        </table>

        <!-- Edit Product Form -->
        <?php if ($item): ?>
            <h2>Edit Product</h2>
            <form action="next2.php" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($item['id']); ?>">
                <input type="text" name="name" value="<?php echo htmlspecialchars($item['name']); ?>" required>
                <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($item['price']); ?>" required>
                <select name="category" required>
                    <option value="">Select Category</option>
                    <?php
                    // Fetch categories again for the dropdown
                    $categories_edit = getCategories($conn);
                    if ($categories_edit->num_rows > 0) {
                        while ($cat = $categories_edit->fetch_assoc()) {
                            $selected = ($cat['id'] == $item['category_id']) ? 'selected' : '';
                            echo "<option value='{$cat['id']}' {$selected}>{$cat['name']}</option>";
                        }
                    }
                    ?>
                </select>
                <button type="submit" name="update">Update Product</button>
                <a href="next2.php" class="btn-cancel">Cancel</a>
            </form>
        <?php endif; ?>

        <!-- Category Statistics -->
        <h2>Products Grouped by Category</h2>
        <table>
            <tr>
                <th>Category</th><th>Total Products</th><th>Average Price</th><th>Actions</th>
            </tr>
            <?php
            $statsResult = getCategoryStats($conn);
            if ($statsResult->num_rows > 0) {
                while ($row = $statsResult->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['category']}</td>
                        <td>{$row['total_products']}</td>
                        <td>\$" . number_format($row['average_price'], 2) . "</td>
                        <td>
                            <a href='next2.php?delete_category={$row['category']}' class='btn-delete-category' onclick=\"return confirm('Are you sure you want to delete this category?');\">Delete</a>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No statistics available.</td></tr>";
            }
            ?>
        </table>
        <a href="index.php">Home</a>
        <a href="next3.php">Employees</a>
    </div>

    <!-- Optional JavaScript for handling the add category form toggle -->
    <script>
        document.getElementById('show-add-category-form').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('add-category-form').style.display = 'block';
            this.style.display = 'none';
        });

        document.getElementById('cancel-add-category').addEventListener('click', function() {
            document.getElementById('add-category-form').style.display = 'none';
            document.getElementById('show-add-category-form').style.display = 'inline';
        });
    </script>
</body>
</html>
<?php
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect to the login page if the user is not logged in
    header("Location: ../index.php");
    exit();
}

// Check if the user is not an admin
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    // Redirect to an unauthorized page or show an error message for non-admin users
    header("Location: ../unauthorized.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Δημιουργία Ανακοίνωσης</title>
    <link rel="icon" type="image/x-icon" href="../styles/civil_protection.png">

    <style>
         * {
        font-family: "Lato", sans-serif;
        }
        .content-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .title {
            text-align: center;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px; margin-bottom: 20px;
            max-width: 1200px;
        }

        th, td {
                border: 1px solid #dddddd;
                text-align: left;
                padding: 8px;
        }

        th {
                background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <?php include('../test_connection.php')?>
    <?php include('../components/navbar.php')?>

    
    <h1 class="title">Δημιουργία Ανακοίνωσης</h1>
    <div class="content-container">
    
    <form action="../actions/process_announcement.php" method="post">
        
    <h2 class="title">Επιλογή Ειδών</h2>
<table border="1">
    <tr>
        <th>Κατηγορία</th>
        <th>Προϊόν</th>
        <th>Επιλογή</th>
    </tr>

    <?php
    // Fetch products
    $sqlProducts = "SELECT Products.product_id, Products.name AS product_name, Categories.category_name, Products.quantity
                    FROM Products
                    INNER JOIN Categories ON Products.category_id = Categories.category_id;";
    $resultProducts = $conn->query($sqlProducts);

    // Display products in a table
    while ($rowProduct = $resultProducts->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$rowProduct['category_name']}</td>";
        echo "<td>{$rowProduct['product_name']}</td>";
        echo "<td><input type='checkbox' name='selected_products[]' value='{$rowProduct['product_id']}'></td>";
        echo "</tr>";
    }

    // Display message if no products exist
    if ($resultProducts->num_rows == 0) {
        echo "<tr><td colspan='3'>Δεν υπάρχουν προϊόντα.</td></tr>";
    }
    ?>
</table>

        <label for="title">Τίτλος:</label>
        <input type="text" name="title" required><br>

        <label for="content">Περιεχόμενο:</label>
        <textarea name="content" rows="4" required></textarea><br>

        <input type="submit" value="Δημιουργία Ανακοίνωσης">
    </form>

    </div>

<?php    // Εκτέλεση του SQL query για ανακοινώσεις
$sqlAnnouncements = "SELECT 
                        Announcements.announcement_id,
                        Announcements.title,
                        Announcements.content,
                        Announcements.date_created,
                        GROUP_CONCAT(Products.name) AS product_names
                    FROM 
                        Announcements
                    LEFT JOIN 
                        AnnouncementProducts ON Announcements.announcement_id = AnnouncementProducts.announcement_id
                    LEFT JOIN 
                        Products ON AnnouncementProducts.product_id = Products.product_id
                    GROUP BY 
                        Announcements.announcement_id";
$resultAnnouncements = $conn->query($sqlAnnouncements);
?>
<h1 class="title">Ιστορικό Ανακοινώσεων</h1>


<div class="content-container">

    <?php
// Εμφάνιση ανακοινώσεων
if ($resultAnnouncements->num_rows > 0) {
    echo "<table border='1'>
    <tr>
    <th>Ανακοίνωση ID</th>
    <th>Τίτλος</th>
    <th>Περιεχόμενο</th>
    <th>Ημερομηνία Δημιουργίας</th>
    <th>Προϊόντα</th>
    </tr>";
    
    while ($row = $resultAnnouncements->fetch_assoc()) {
        echo "<tr>
        <td>{$row['announcement_id']}</td>
        <td>{$row['title']}</td>
        <td>{$row['content']}</td>
        <td>{$row['date_created']}</td>
        <td>{$row['product_names']}</td>
        </tr>";
    }
    
    echo "</table>";
} else {
    echo "Δεν υπάρχουν ανακοινώσεις.";
}

// Κλείσιμο της σύνδεσης
$conn->close();
?>
</div>
</body>
</html>

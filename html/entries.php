<?php
// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session to check if user is logged in
session_start();

// Redirect to login if user is not authenticated
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== TRUE) {
    header("Location: http://100.119.133.29");
    exit;
}


// Include database connection file
include('../secrets/db_conn.php');

// Get filter values from URL parameters (GET request)
// These allow users to filter by student ID, equipment, date/time, or source table
$filter_student_id = isset($_GET['student_id']) ? $_GET['student_id'] : '';
$filter_equipment = isset($_GET['equipment']) ? $_GET['equipment'] : '';
$filter_datetime = isset($_GET['datetime']) ? $_GET['datetime'] : '';
$filter_table = isset($_GET['table']) ? $_GET['table'] : '';

// Build SQL query for gear_checkout table
// Start with SELECT statement that includes all columns plus a 'source_table' identifier
$sql = "(SELECT id, student_id, equipment, date_time, 'gear_checkout' AS source_table FROM gear_checkout WHERE 1=1";

// Add filter conditions if student_id is provided
if (!empty($filter_student_id)) {
    // Escape input to prevent SQL injection
    $filter_student_id = mysqli_real_escape_string($conn, $filter_student_id);
    // Use LIKE for partial matching (finds 1045 in 1045776)
    $sql .= " AND student_id LIKE '%$filter_student_id%'";
}

// Add filter conditions if equipment is provided
if (!empty($filter_equipment)) {
    $filter_equipment = mysqli_real_escape_string($conn, $filter_equipment);
    $sql .= " AND equipment LIKE '%$filter_equipment%'";
}

// Add filter conditions if date_time is provided
if (!empty($filter_datetime)) {
    $filter_datetime = mysqli_real_escape_string($conn, $filter_datetime);
    // This will match any part of the datetime (date or time)
    $sql .= " AND date_time LIKE '%$filter_datetime%'";
}

// Close the first SELECT statement
$sql .= ")";

// Add UNION to combine results from both tables
// UNION merges results from multiple SELECT statements into one result set
$sql .= " UNION ";

// Build SQL query for gear_return table (same structure as checkout)
$sql .= "(SELECT id, student_id, equipment, date_time, 'gear_return' AS source_table FROM gear_return WHERE 1=1";

// Apply the same filters to the gear_return table
if (!empty($filter_student_id)) {
    $filter_student_id_escaped = mysqli_real_escape_string($conn, $filter_student_id);
    $sql .= " AND student_id LIKE '%$filter_student_id_escaped%'";
}

if (!empty($filter_equipment)) {
    $filter_equipment_escaped = mysqli_real_escape_string($conn, $filter_equipment);
    $sql .= " AND equipment LIKE '%$filter_equipment_escaped%'";
}

if (!empty($filter_datetime)) {
    $filter_datetime_escaped = mysqli_real_escape_string($conn, $filter_datetime);
    $sql .= " AND date_time LIKE '%$filter_datetime_escaped%'";
}

// Close the second SELECT statement
$sql .= ")";

// Apply table filter if user selected to view only checkout or only return records
if (!empty($filter_table)) {
    $filter_table = mysqli_real_escape_string($conn, $filter_table);
    // Wrap the UNION query in a subquery and filter by source_table
    $sql = "SELECT * FROM ($sql) AS combined WHERE source_table = '$filter_table'";
}

// Order results by ID in ascending order (oldest to newest)
$sql .= " ORDER BY id ASC";

// Execute the query
$result = mysqli_query($conn, $sql);

// Check if query was successful
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Main body styling with gradient background matching style.css */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }

        /* Navigation bar */
        nav {
            margin-bottom: 20px;
            text-align: right;
        }

        nav a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            transition: background 0.3s ease;
            display: inline-block;
        }

        nav a:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Main heading */
        h1 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        /* Whitelist form container */
        .whitelist_form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            margin: 0 auto 30px;
        }

        .whitelist_form h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 1.5em;
            text-align: center;
        }

        /* Whitelist form layout */
        .whitelist_form form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .whitelist_form input[type="text"] {
            flex: 1;
            min-width: 200px;
            padding: 12px;
            border: 2px solid #667eea;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }

        .whitelist_form input[type="text"]:focus {
            outline: none;
            border-color: #764ba2;
        }

        .whitelist_form input[type="submit"] {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .whitelist_form input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .whitelist_form input[type="submit"]:active {
            transform: translateY(0);
        }

        /* Remove button with red styling */
        .whitelist_form input[type="submit"][value="Remove"] {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }

        .whitelist_form input[type="submit"][value="Remove"]:hover {
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }

        /* Error message styling */
        .whitelist_form .error {
            color: #e74c3c;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background: #fadbd8;
            border-radius: 8px;
            border-left: 4px solid #e74c3c;
        }

        .whitelist_form .success {
            color: #3ce756ff;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background: #fadbd8;
            border-radius: 8px;
            border-left: 4px solid #3ce756ff;
        }

        /* Filter form container */
        .filter-form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 1200px;
            margin: 0 auto 30px;
        }

        .filter-form h3 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        /* Form layout - displays inputs in a grid */
        .filter-form form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }

        /* Text input styling matching style.css */
        .filter-form input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #667eea;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }

        .filter-form input[type="text"]:focus {
            outline: none;
            border-color: #764ba2;
        }

        /* Dropdown select styling */
        .filter-form select {
            width: 100%;
            padding: 12px;
            border: 2px solid #667eea;
            border-radius: 8px;
            font-size: 1em;
            background: white;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        .filter-form select:focus {
            outline: none;
            border-color: #764ba2;
        }

        /* Button styling matching style.css gradient */
        .filter-form button {
            padding: 12px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .filter-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .filter-form button:active {
            transform: translateY(0);
        }

        /* Clear button with different styling */
        .filter-form .clear-btn {
            background: linear-gradient(135deg, #888 0%, #666 100%);
        }

        /* Results count display */
        .result-count {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            max-width: 1200px;
            margin: 0 auto 20px;
            text-align: center;
            color: #667eea;
            font-size: 1.1em;
        }

        /* Table container for responsive scrolling */
        .table-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Table header styling */
        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9em;
            letter-spacing: 0.5px;
        }

        /* Table body rows */
        tbody tr {
            border-bottom: 1px solid #e0e0e0;
            transition: background-color 0.2s ease;
        }

        tbody tr:hover {
            background-color: #f5f5f5;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        td {
            padding: 15px;
            color: #333;
        }

        /* Badge styling for source table column */
        .table-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Checkout badge - green color scheme */
        .badge-checkout {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        /* Return badge - orange color scheme */
        .badge-return {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }

        /* Main heading */
        h1 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        /* Filter form container */
        .filter-form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 1200px;
            margin: 0 auto 30px;
        }

        .filter-form h3 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        /* Form layout - displays inputs in a grid */
        .filter-form form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }

        /* Text input styling matching style.css */
        .filter-form input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #667eea;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }

        .filter-form input[type="text"]:focus {
            outline: none;
            border-color: #764ba2;
        }

        /* Dropdown select styling */
        .filter-form select {
            width: 100%;
            padding: 12px;
            border: 2px solid #667eea;
            border-radius: 8px;
            font-size: 1em;
            background: white;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        .filter-form select:focus {
            outline: none;
            border-color: #764ba2;
        }

        /* Button styling matching style.css gradient */
        .filter-form button {
            padding: 12px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .filter-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .filter-form button:active {
            transform: translateY(0);
        }

        /* Clear button with different styling */
        .filter-form .clear-btn {
            background: linear-gradient(135deg, #888 0%, #666 100%);
        }

        /* Results count display */
        .result-count {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            max-width: 1200px;
            margin: 0 auto 20px;
            text-align: center;
            color: #667eea;
            font-size: 1.1em;
        }

        /* Table container for responsive scrolling */
        .table-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Table header styling */
        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9em;
            letter-spacing: 0.5px;
        }

        /* Table body rows */
        tbody tr {
            border-bottom: 1px solid #e0e0e0;
            transition: background-color 0.2s ease;
        }

        tbody tr:hover {
            background-color: #f5f5f5;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        td {
            padding: 15px;
            color: #333;
        }

        /* Badge styling for source table column */
        .table-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Checkout badge - green color scheme */
        .badge-checkout {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        /* Return badge - orange color scheme */
        .badge-return {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        /* Empty state message */
        .empty-message {
            text-align: center;
            color: #999;
            font-style: italic;
            padding: 40px;
        }
        /* Empty state message */
        .empty-message {
            text-align: center;
            color: #999;
            font-style: italic;
            padding: 40px;
        }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Gear Tracking System</title>
</head>
<body>
    <nav><a href="http://100.119.133.29/">Logout</a></nav>
    <h1>Gear Tracking System</h1>
    <div class="whitelist_form">
        <h2>Whitelist controls</h2>
        <?php
        if (isset($_GET['error']) && $_GET['error'] == "1") {
            echo '<p class="error">Cannot submit an empty form</p>';
        } elseif (isset($_GET['error']) && $_GET['error'] == "2") {
            echo '<p class="error">There is no such ID on amp certified list</p>';
        } elseif (isset($_GET['error']) && $_GET['error'] == "3") {
            echo '<p class="error">That ID is already amp certified!</p>';
        }
        ?>
        <form action="./whitelist.php" method="post">
            <input type="text" name="new_whitelist_id" placeholder="Student ID">
            <input type="submit" name="submit" value="Add">
            <input type="submit" name="submit" value="Remove">
        </form>
    </div>
    <!-- Filter Form Section -->
    <div class="filter-form">
        <h3>Filter Records</h3>
        <!-- Form uses GET method to pass filters via URL parameters -->
        <form method="GET" action="">
            <!-- Student ID filter input -->
            <input type="text" 
                   name="student_id" 
                   placeholder="Filter by Student ID" 
                   value="<?php echo htmlspecialchars($filter_student_id); ?>">
            
            <!-- Equipment filter input -->
            <input type="text" 
                   name="equipment" 
                   placeholder="Filter by Equipment" 
                   value="<?php echo htmlspecialchars($filter_equipment); ?>">
            
            <!-- Date/Time filter input -->
            <input type="text" 
                   name="datetime" 
                   placeholder="Filter by Date/Time" 
                   value="<?php echo htmlspecialchars($filter_datetime); ?>">
            
            <!-- Table selection dropdown -->
            <select name="table">
                <option value="">All Records</option>
                <option value="gear_checkout" <?php echo $filter_table === 'gear_checkout' ? 'selected' : ''; ?>>
                    Checkouts Only
                </option>
                <option value="gear_return" <?php echo $filter_table === 'gear_return' ? 'selected' : ''; ?>>
                    Returns Only
                </option>
            </select>
            
            <!-- Submit button to apply filters -->
            <button type="submit">Apply Filters</button>
            
            <!-- Clear button to reset all filters -->
            <button type="button" 
                    class="clear-btn" 
                    onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>'">
                Clear Filters
            </button>
        </form>
    </div>

    <!-- Display count of records found -->
    <div class="result-count">
        <strong>Results: <?php echo mysqli_num_rows($result); ?> records found</strong>
    </div>

    <!-- Data Table Section -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>ID</th>
                    <th>Student ID</th>
                    <th>Equipment</th>
                    <th>Date & Time</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Check if any records were found
                if (mysqli_num_rows($result) > 0) {
                    // Loop through each record and display it
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Determine badge class based on source table
                        // gear_checkout gets green badge, gear_return gets orange badge
                        $badge_class = $row['source_table'] === 'gear_checkout' ? 'badge-checkout' : 'badge-return';
                        
                        // Output table row with data
                        echo "<tr>";
                        // Source table column with colored badge
                        echo "<td><span class='table-badge $badge_class'>" . 
                             htmlspecialchars($row['source_table'] === 'gear_checkout' ? 'CHECKOUT' : 'RETURN') . 
                             "</span></td>";
                        // ID column
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        // Student ID column
                        echo "<td>" . htmlspecialchars($row['student_id']) . "</td>";
                        // Equipment column
                        echo "<td>" . htmlspecialchars($row['equipment']) . "</td>";
                        // Date/Time column
                        echo "<td>" . htmlspecialchars($row['date_time']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    // Display message if no records found
                    echo "<tr><td colspan='5' class='empty-message'>No records found matching your filters</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <?php
    mysqli_close($conn);
    ?>

</body>
</html>
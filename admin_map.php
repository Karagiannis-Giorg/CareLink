<?php
// Σύνδεση στη βάση δεδομένων
$servername = "localhost";
$username = "evelina";
$password = "Evel1084599!";
$dbname = "carelink";

$conn = new mysqli($servername, $username, $password, $dbname);

// Έλεγχος σύνδεσης
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: sign_in.php');
    exit();
}

/* // Επιλογή των συντεταγμένων της βάσης από τη βάση δεδομένων
$base = "SELECT base_location FROM base WHERE base_id = 1"; // Υποθέτουμε ότι η βάση έχει μόνο ένα στοιχείο με αναγνωριστικό 1
$location = $conn->query($base);

if ($location->num_rows > 0) {
    $row = $location->fetch_assoc();
    $base_location = $row["base_location"];
} else {
    echo "Base location not found";
} */

// Function to fetch data and write to JSON file
function fetchRequests($conn) {
    $request = "SELECT request.*, civilian.civilian_number, civilian.civilian_first_name, civilian.civilian_last_name,
                SUBSTRING_INDEX(civilian.civilian_location, ',', 1) AS latitude,
                SUBSTRING_INDEX(civilian.civilian_location, ',', -1) AS longitude
                FROM request 
                JOIN civilian ON request.request_civilian = civilian.civilian_username ";

    $data1 = array();
    $sqlrequest = $conn->query($request);

    if ($sqlrequest) {
        while ($row = $sqlrequest->fetch_assoc()) {
            $data1[] = array(
                "id_request" => $row["id_request"],
                "request_civilian" => $row["request_civilian"],
                "request_category" => $row["request_category"],
                "request_product_name" => $row["request_product_name"],
                "persons" => $row["persons"],
                "request_date_posted" => $row["request_date_posted"],
                "request_time_posted" => $row["request_time_posted"], 
                "state" => $row["state"],
                "number" => $row["civilian_number"],
                "first_name" => $row["civilian_first_name"],
                "last_name" => $row["civilian_last_name"],
                "latitude" => $row["latitude"], 
                "longitude" => $row["longitude"]
            );
        }

        // Encode $data1 array to JSON
        $json_data = json_encode($data1);

        // Specify the path to store the JSON file
        $json_file = 'data1.json';

        // Write JSON data to file
        if (file_put_contents($json_file, $json_data)) {
            return "JSON data successfully written to $json_file";
        } else {
            return "Unable to write JSON data to $json_file";
        }

        $sqlrequest->close();
    } else {
        return "Error executing the SQL query: " . $conn->error;
    }
}

// Check if this request is to update the JSON file
if (isset($_GET['update_json1'])) {
    echo fetchRequests($conn);
    exit();
} else {
    fetchRequests($conn);
}

// Κλείσιμο σύνδεσης με τη βάση δεδομένων
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>CareLink Map</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin_map.css">
    <link rel="icon" type="image/jpg" sizes="96x96" href="images/favicon.png">
    <link rel="stylesheet" href="leaflet-routing-machine.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.2.0/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <script src="https://unpkg.com/leaflet@1.2.0/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
</head>

<body>
    <div class="header">
        <h2><img src="images/logo.png" alt="Logo" width="200"></h2>
        <div class="h3-header">
            <h3>Back to tracking!</h3> 
          </div>
    </div>

    <div class="navbar">
        <ul class="nav">
            <li><a href="admin.php">Home</a></li>
            <li><a class="active" href="admin_map.html">Map</a></li>
            <li><a href="base.php">Database </a></li>
        </ul>
        <ul class="nav">            
            <li><a href="#"><i class="fa fa-sign-out" style="font-size:24px" ></i> Log out</a></li>
        </ul>
    </div>

    <div class="Firstsection">
        <h2> Maps </h2>
        <br>
        <form class="filters">
                <input type="radio" id="layer1" name="mapLayer" onchange="toggleLayer('layer1')" checked>
                <label for="layer1">Requests</label>

                <input type="radio" id="layer2" name="mapLayer" onchange="toggleLayer('layer2')">
                <label for="layer2">Offers</label>

                <input type="radio" id="layer3" name="mapLayer" onchange="toggleLayer('layer3')">
                <label for="layer3">Lines</label>

                <input type="radio" id="layer4" name="mapLayer" onchange="toggleLayer('layer4')">
                <label for="layer4">Vehicles on Action</label>

                <input type="radio" id="layer5" name="mapLayer" onchange="toggleLayer('layer5')">
                <label for="layer5">Vehicles without tasks</label>
            </form>
            <div id='map'></div>
   </div>
    
    <div class="Footer container-fluid">
        <div class="row">
          <div class="column col-sm-3">
            <h2>Social Media</h2>
            <nav class="nav02">
              <a href="https://www.linkedin.com/in/george-karagiannis-00a683222/" class="fa fa-linkedin"></a>
              <a href="https://www.facebook.com/george.karagiannis.9406" class="fa fa-facebook"></a>
              <a href="https://www.instagram.com/_karagiannis_g/" class="fa fa-instagram "></a>
            </nav>
          </div>
          <div class="column col-sm-6">
            <h2>Licences</h2>
            <br>
            <p>© George Karagiannis/Ceid/Upatras/Year 2023-2024</p>
            <p>© Evelina Kolagki/Ceid/Upatras/Year 2023-2024</p>
    
          </div>
          <div class="column col-sm-3">
            <h2>Contact info</h2>
            <br>
            <ul>
              <li>Email: Karagiannis.giorg@gmail.com</li>
              <br>
              <li>Phone: +30 123456789</li>
            </ul>
    
          </div>
        </div>
    
    </div>

    <script src="admin_map.js"></script>

</body>
</html>
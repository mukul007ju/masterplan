<?php
require "./dbinfo.php";

//connect to postgis using PDO
$conn = new PDO("pgsql:host=".$remote_dbhost.";port=16211;dbname=".$remote_dbname, 
                $remote_dbuser, 
                $remote_dbpassword, 
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

//SQL Geo dbms
$sql = "SELECT *, st_asgeojson(geom) AS geojson FROM canada_plot_wgs";

$result = $conn->query($sql);

if(!$result) {
	echo "SQL ERROR";
	exit();
}


//Build Geojson feature collection
$geojson = array(
"type" => "FeatureCollection",
"features" => array()
);

//Loop through rows to build feature arrays
while($row = $result->fetch(PDO::FETCH_ASSOC)){
$properties = $row;

//Remove geojson & geometry fields from properties
unset($properties["geojson"]);
unset($properties["geom"]);

$feature = array(
"type" => "Feature",
"geometry" => json_decode($row["geojson"], true), 
"properties" => $properties
);

//Add feature arrays to feature collection array
array_push($geojson["features"], $feature);
}

//Create JSON Header
header("Content-type: application/json");

//Display Geojson output

echo json_encode ($geojson, JSON_NUMERIC_CHECK);

//Close DB Connection
$conn = NULL;

?>

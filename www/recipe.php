<?php
require("../recipes-bin/wrb-inc-global.php");

global $iniPath;

// Open connection to database
try{
	$dbConn = Db_Conn();
	$dbStmt = "SELECT pk_recipe, recipe_title, recipe_source, recipe_description, recipe_servings FROM recipes WHERE recipe_title_id = '" . htmlspecialchars($_GET["name"]) . "'";
	$dbRset = $dbConn -> query($dbStmt);
} catch(Exception $error) {
	throw new Exception("Unable to display recipe!");
}

while($row = $dbRset -> fetch_assoc()){
	$rId = $row["pk_recipe"];
	$rTitle = $row["recipe_title"];
	$rSource = $row["recipe_source"];
	$rDescription = $row["recipe_description"];
	$rServings = $row["recipe_servings"];
}

// Close connection to database
$dbRset -> free_result();
Db_Close($dbConn);

Page_Init("");
Page_Header(0);
?>
	<main>
		<div class="box-content">
			<h2><?php print($rTitle); ?></h2>
			<ul class="list-summary">
				<li>Servings: <b><?php print($rServings); ?></b></li>
				<li>Difficulty: <b>Easy</b></li>
				<li>Estimated Time: <b>3 hrs (30 min prep; 2.5 hrs baking)</b></li>
			</ul>
			<p><?php print($rDescription); ?></p>
			<h3>Ingredients</h3>
			<div class="column-ingredients">
			<ul class="list-ingredients">
<?php
function Convert_Decimal($value){
	$output = "";
	$amount = explode(".", $value);
	
	if($amount[0] != "0"){
		$output = $amount[0];
	}

	switch($amount[1]){
		case "00":
			break;
		case "25":
			$output .= "&frac14;";
			break;
		case "50":
			$output .= "&frac12;";
			break;
		case "75":
			$output .= "&frac34;";
			break;
		default:
			$output .= "." . $amount[1];
			break;
	}

	return $output;
}

// Open connection to database
$c = 0;
try{
	$dbConn = Db_Conn();
	$dbStmt = "SELECT ingredients_qty, ingredients_unit, ingredients_name FROM ingredients WHERE fk_recipe = $rId";
	$dbRset = $dbConn -> query($dbStmt);
} catch(Exception $error) {
	throw new Exception("Unable to display recipe ingredients!");
}

if($dbRset -> num_rows > 0){
$colBreak = ceil(($dbRset -> num_rows) / 2);
	while($row = $dbRset -> fetch_assoc()){
		if($c == $colBreak){
			print("					</ul>\n");
			print("					<ul class=\"list-ingredients\">\n");
		}
		print("				<li>" . $row["ingredients_name"] . "<br /><span class=\"ingredient-amount\">" . Convert_Decimal($row["ingredients_qty"]) . " " . $row["ingredients_unit"] . "</span></li>\n");
		$c++;
	}
}

// Close connection to database
$dbRset -> free_result();
Db_Close($dbConn);
?>
			</ul>
		</div>
			<h3>Directions</h3>
			<ol class="list-directions">
<?php
// Open connection to database
try{
	$dbConn = Db_Conn();
	$dbStmt = "SELECT step_instruction FROM steps WHERE fk_recipe = $rId ORDER BY step_number ASC";
	$dbRset = $dbConn -> query($dbStmt);
} catch(Exception $error) {
	throw new Exception("Unable to display recipe directions!");
}

if($dbRset -> num_rows > 0){
	while($row = $dbRset -> fetch_assoc()){
		print("				<li>" . $row["step_instruction"] . "</li>\n");
	}
}

// Close connection to database
$dbRset -> free_result();
Db_Close($dbConn);
?>
			</ol>
		</div>
	</main>
<?php
Page_Footer();
Page_End();
?>

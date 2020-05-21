<?php
/*
  File Name  wrb-inc-global.php
  Project    web-recipe-book
  Version    0.1.0
  Author     Peter Lindstrom
  Purpose    Global PHP functions utilized throughout the web recipe book.
  Copyright  2020, Peter Lindstrom
  Link       https://github.com/plindstrom/web-recipe-book
*/


// Load ini files ------------------------------------------------------------
$cfg = parse_ini_file("wrb-cfg-global.ini", true);


// Page setup ----------------------------------------------------------------
function Page_Init($pgTitle){
	// Print page beginning html
	global $cfg;
	print("<!DOCTYPE HTML>\n");
	print("<html lang=\"en\">\n");
	print("<head>\n");
	print("	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n");
	print("	<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1\" />\n");
	print("	<title>" . $cfg[wrb][name] . "</title>\n");
	print("	<link rel=\"stylesheet\" type=\"text/css\" href=\"/css/global.css\" />\n");
	print("	<script type=\"text/javascript\" src=\"/js/jquery-3.4.1.min.js\"></script>\n");
	print("	<script type=\"text/javascript\" src=\"/js/global.js\"></script>\n");
	print("</head>\n");
	print("<body>\n");
}


// Page ending ---------------------------------------------------------------
function Page_End(){
    // Print page end html
	print("</body>\n");
	print("</html>\n");
}


// Page header ---------------------------------------------------------------
function Page_Header(){
	// Print page header html
	global $cfg;
	print("	<header>\n");
	print("		<div class=\"box-content\">\n");
	print("			<h1>" . $cfg[wrb][name] . "</h1>\n");
	print("		</div>\n");
	print("	</header>\n");
	print("	<nav>\n");
	print("		<div class=\"box-content\">\n");
	print("			<ul>\n");
	print("				<li><a href=\"/\">Browse</a></li>\n");
	print("				<li><a href=\"/browse.php?list=favorites\">Favorites</a></li>\n");
	print("				<li><a href=\"/browse.php?list=just-added\">Just Added</a></li>\n");
	print("			</ul>\n");
	print("		</div>\n");
	print("	</nav>\n");
}


// Page footer ---------------------------------------------------------------
function Page_Footer(){
	// Print page footer html
	print("	<footer>\n");
	print("		<div class=\"box-content\">\n");
	print("			<p>&nbsp;</p>\n");
	print("		</div>\n");
	print("	</footer>\n");
}


// Open database connection --------------------------------------------------
function Db_Conn(){
	// Get DB conn info from global config ini
	global $cfg;

	// Establish a connection
	$conn = new mysqli($cfg[database][hostname], $cfg[database][username], $cfg[database][password], $cfg[database][database]);
	if($conn -> connect_error){
		die("Connection failed: ".$conn -> connect_error);
	}
	
	return $conn;
}


// Close database connection -------------------------------------------------
function Db_Close($conn){
	// Close the connection
	$conn->close();
	$conn = null;
}


//$db_categories_stmt = "SELECT category_name, tag_name FROM categories, tags, recipes_categories, recipes_tags WHERE pk_category = recipes_categories.fk_category AND recipes_categories.fk_recipe = recipes_tags.fk_recipe AND recipes_tags.fk_tag = tags.pk_tag ORDER BY categories.category_name ASC";		
// List recipe cateogries ----------------------------------------------------
function Get_Categories(){
	global $iniPath;
	$c = 0;

	// Open connection to database
	try{
		$db_conn = Db_Conn();
		$db_categories_stmt = "SELECT category_name FROM categories ORDER BY category_name ASC";
		$db_categories_rset = $db_conn->query($db_categories_stmt);
	} catch(Exception $error) {
		throw new Exception("Unable to display recipe categories!");
	}

	if($db_categories_rset->num_rows > 0){
		while($category = $db_categories_rset->fetch_assoc()){
			$output[$c] = $category["category_name"];
			$c++;
		}
	} else {
		$output[$c] = "Could not find any categories.";
	}

	// Close connection to database
	Db_Close($db_conn);

	return $output;
}


// List tags -----------------------------------------------------------------
function Get_Tags($category){
	global $iniPath;
	$c = 0;

	// Open connection to database
	try{
		$db_conn = Db_Conn();
		$db_tags_stmt = "SELECT DISTINCT tag_name FROM categories, tags, recipes_categories, recipes_tags WHERE categories.category_name = '$category' AND categories.pk_category = recipes_categories.fk_category AND recipes_categories.fk_recipe = recipes_tags.fk_recipe AND recipes_tags.fk_tag = tags.pk_tag";
		$db_tags_rset = $db_conn->query($db_tags_stmt);
	} catch(Exception $error) {
		throw new Exception("Unable to display recipe tags!");
	}

	if($db_tags_rset->num_rows > 0){
		while($tag = $db_tags_rset->fetch_assoc()){
			$output[$c] .= $tag["tag_name"];
			$c++;
		}
		return $output;
	} else {
		// Return false if tags were not found
		return false;
	}

	// Close connection to database
	Db_Close($db_conn);
}


// List matching recipes -----------------------------------------------------
function Get_Recipes($category, $tag = NULL){
	global $cfg;

	// Open connection to database
	try{
		$dbConn = Db_Conn();
		if(is_null($tag) != true){
			$dbStmt = "SELECT pk_recipe, recipe_title_id, recipe_title FROM categories, recipes_categories, recipes WHERE categories.pk_category = $category AND categories.pk_category = recipes_categories.fk_category AND recipes_categories.fk_recipe = recipes.pk_recipe";
		} else {
			$dbStmt = "SELECT pk_recipe, recipe_title_id, recipe_title FROM categories, recipes_categories, recipes WHERE categories.pk_category = $category AND categories.pk_category = recipes_categories.fk_category AND recipes_categories.fk_recipe = recipes.pk_recipe";
		}
		$dbRset = $dbConn -> query($dbStmt);
	} catch(Exception $error) {
		throw new Exception("Unable to find matching recipes!  I'm hungry.");
	}

	// Return recipes array if results were found
	if($dbRset -> num_rows > 0){
		// Create the array
		$output = array();
		while($row = $dbRset -> fetch_assoc()){
			$output[] = array(
				"pk_recipe"       => $row["pk_recipe"],
				"recipe_title_id" => $row["recipe_title_id"],
				"recipe_title"    => $row["recipe_title"]
			);
		}
		return $output;
	} else {
		// Return false if recipes were not found
		return false;
	}

	// Close connection to database
	$output -> free_result();
	Db_Close($dbConn);
}

// List matching search results ----------------------------------------------
function Get_SearchResults($query){
	global $cfg;

	// Split query into array of keywords
	$keywords = explode(" ", $query);

	/*
		Search Result Scoring:

		1. Match keywords to alias list and add matching aliases to keyword array
		   For example: 'WW' aliases = ['weight', 'watchers', 'weightwatchers']

		2. Search for keywords in recipe names
		   +10/keyword found

		3. Search for keywords in tags
		   +5/keyword found

		4. Search for keywords in ingredients
	   	   +3/keyword found

	   	5. Search for keywords in description
	   	   +1/keyword found
	*/

	// Open connection to database
	try{
		$dbConn = Db_Conn();
		$dbStmt = "SELECT pk_recipe, recipe_title_id, recipe_title FROM categories, recipes_categories, recipes WHERE categories.pk_category = $category AND categories.pk_category = recipes_categories.fk_category AND recipes_categories.fk_recipe = recipes.pk_recipe";
		$dbRset = $dbConn -> query($dbStmt);
	} catch(Exception $error) {
		throw new Exception("Unable to find matching recipes!  I'm hungry.");
	}

	// Return recipes array if results were found
	if($dbRset -> num_rows > 0){
		// Create the array
		$output = array();
		while($row = $dbRset -> fetch_assoc()){
			$output[] = array(
				"pk_recipe"       => $row["pk_recipe"],
				"recipe_title_id" => $row["recipe_title_id"],
				"recipe_title"    => $row["recipe_title"]
			);
		}
		return $output;
	} else {
		// Return false if recipes were not found
		return false;
	}

	// Close connection to database
	$output -> free_result();
	Db_Close($dbConn);
}

// Get current working directory ---------------------------------------------
function Get_CWD(){
	// Get the current working directory
	$cwd = explode("/", getcwd());
	$cwd = $cwd[4];
	
	// Return the current working directory
	return $cwd;
}
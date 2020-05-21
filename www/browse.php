<?php
require("../recipes-bin/wrb-inc-global.php");

global $iniPath;
$c = 0;

// Open connection to database
try{
	$db_Conn = Db_Conn();
	if(empty(htmlspecialchars($_GET["tag"])) == false){
		$db_categories_stmt = "SELECT pk_category, category_name FROM categories WHERE category_name = '" . htmlspecialchars($_GET["category"]) . "'";
	} else {
		$db_categories_stmt = "SELECT pk_category, category_name FROM categories WHERE category_name = '" . htmlspecialchars($_GET["category"]) . "'";
	}
	$db_categories_rset = $db_conn->query($db_categories_stmt);
} catch(Exception $error) {
	throw new Exception("Unable to display recipe categories!");
}

if($db_categories_rset->num_rows > 0){
	while($category = $db_categories_rset->fetch_assoc()){
		$category_pk = $category["pk_category"];
		$category_name = $category["category_name"];
	}
} else {
	$output = "Could not find any categories.";
}

// Close connection to database
Db_Close($db_conn);

Page_Init("");
Page_Header(0);
?>
	<main>
		<div class="box-content">
			<h2><?php print($category_name); ?></h2>
			<div class="box-filter">
				<h4>Filter by Tag</h4>
				<ul><?php if(empty(htmlspecialchars($_GET["tag"])) == false){
				print("<li class=\"link-all\"><a href=\"/browse.php?category=".strtolower($category_name)."\">View all</a></li>");
			}
			?>
					<?php
						$tags = Get_Tags($category_name);
						if($tags != false){
							foreach($tags as $tag){
								print("							<li><a href=\"/browse.php?category=".strtolower($category_name)."&tag=".strtolower($tag)."\">$tag</a></li>\n");
							}
						} else {
							print("							<li>&nbsp;</li>\n");
						}
						?>
				</ul>
			</div>
			<?php if(empty(htmlspecialchars($_GET["tag"])) == false){
				print("<p>Only showing recipes with the <strong>" . htmlspecialchars($_GET["tag"]) . "</strong> tag.  <a href=\"/browse.php?category=".strtolower($category_name)."\">Show all $category_name</a> instead.</p>");
			}
			?>
			<ul class="list-browse"><?php
						$c = 0;
						$recipes = Get_Recipes($category_pk);
						foreach($recipes as $recipe){
							print("						<li><a href=\"/recipe.php?name=" . strtolower($recipe["recipe_title_id"]) . "&category=".strtolower($category_name)."&tag=".strtolower($tag)."\">".$recipe["recipe_title"] . "</a><br />
					<span class=\"ingredient-amount\">Difficulty: <b>Easy</b>; Estimated Time: <b>3 hrs</b></span></li>\n");
							$c++;
						}
					?>
			</ul>
			<br class="clear-both" />
		</div>
	</main>
<?php
Page_Footer();
Page_End();
?>

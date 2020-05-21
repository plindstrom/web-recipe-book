<?php
require("../recipes-bin/wrb-inc-global.php");

global $iniPath;

$recipes = Get_SearchResults(htmlspecialchars($_GET["query"]));
?>
	<main>
		<div class="box-content">
			<h2>Search Results</h2>
			<p>Found <strong>2</strong> matching recipes.</p>
			<ul class="list-browse"><?php
						$c = 0;
						foreach($recipes as $recipe){
							print("						<li><a href=\"/recipe.php?name=" . strtolower($recipe["recipe_title_id"]) . "\">".$recipe["recipe_title"] . "</a><br />
					<span class=\"ingredient-amount\">Difficulty: <b>Easy</b>; Estimated Time: <b>3 hrs</b></span></li>\n");
							$c++;
						}
					?>
			</ul>
		</div>
	</main>
<?php
Page_Footer();
Page_End();
?>

<?php
require("../recipes-bin/wrb-inc-global.php");

Page_Init("");
Page_Header(0);
?>
	<main>
		<div class="box-content">
			<div class="box-search">
				<form method="post" action="">
					<input type="text" class="txt-search" title="Search for a Recipe" />
					<input type="submit" title="Search" class="btn-search" />
				</form>
			</div>
			<div class="box-browse">
				<div class="column-browse">
					<ul class="list-categories">
<?php
$c = 0;
$categories = Get_Categories();
$colBreak = count($categories) / 2;
foreach($categories as $category){
	if($c == $colBreak){
		print("					</ul>\n");
		print("					<ul class=\"list-categories\">\n");
	}
	print("						<li><a href=\"/browse.php?category=".strtolower($category)."\">$category</a><ul>\n");
	$tags = Get_Tags($category);
	if($tags != false){
		foreach($tags as $tag){
			print("							<li><a href=\"/browse.php?category=".strtolower($category)."&tag=".strtolower($tag)."\">$tag</a></li>\n");
		}
	} else {
		print("							<li>&nbsp;</li>\n");
	}
	print("						</ul></li>\n");
	$c++;
}
?>					</ul>
				</div>
			</div>
		</div>
	</main>
<?php
Page_Footer();
Page_End();
?>

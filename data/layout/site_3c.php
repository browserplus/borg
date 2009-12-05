<?php 
//
// SITE_2C template takes following vars:
//    For Header
//        $title
//        $active (tab, i.e. "Discuss")
//    For This 
//        $body
//        $widgets
//    For Footer
//        [nothing]
//

include "_header.php" 
?>

<div id="wrapper">
	<div id="container">
		<div id="side-a">
            <div id="widgets_left">
                <?php echo $widgets_left; ?>
            </div>
		</div>
		
		<div id="content" class="main">
			<?php echo $body;l ?>
		</div>
		
		<div id="side-b">
            <div id="widgets_right">
                <?php echo $widgets_right; ?>
            </div>
		</div>
	</div>
</div>

<?php include "_footer.php" ?>
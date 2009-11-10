<?php 
//
// DOK_1C template takes following vars:
//    For Header
//        $title
//        $active (tab, i.e. "Discuss")
//    For This (supplied from dok):
//        $body
//    For Footer
//        [nothing]
//
include "_header.php"
?>

<div id="bd" class="main" role="main"> 
    <?php echo $body; ?>
</div>

<?php include "_footer.php" ?>
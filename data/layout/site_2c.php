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

<div id="bd" role="main"> 
    <div class="yui-ge"> 
        <div class="main yui-u first"> 
            <?php echo $body; ?>
        </div>

        <div class="yui-u">
            <div id="widgets">
                <? echo $widgets; ?>
            </div>
        </div>
    </div>
</div>

<?php include "_footer.php" ?>
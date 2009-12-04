<?php 
//
// DOK_2C template takes following vars:
//    For Header
//        $title
//        $active (tab, i.e. "Discuss")
//    For This (supplied from dok):
//        $body
//        $dirs
//        $files
//        $up_dir
//        $filename
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

                <!-- div class="widget-search widget">
                    <h1>Search</h1>
                    <div class="widget-content">
                        <form method="get" action="/search.php">
                            <input type="search" id="search" class="ti" name="search" value="" />
                        </form>
                    </div>
                </div -->

                <?php echo dok_pages_widget($dirs, $files, $up_dir, $cur_dir_title, $filename); ?>
            </div>
        </div>
    </div>
</div>

<?php include "_footer.php" ?>
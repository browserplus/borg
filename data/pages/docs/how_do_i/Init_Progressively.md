# How do I use BrowserPlus only if the user already has it installed?

You can progressively enhance a web page to only use BrowserPlus if it is installed with the built-in init method.

    <script src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>  
    <script>
    BrowserPlus.init({}, function(result){
        if (result.success) {
            // use BrowserPlus
        } else {
            // fallback to original page
        }
    }); 
    </script>

var JsonRequestDemoModule = function() {
    var $ = function(el) {
        return (el && el.nodeType) ? el : document.getElementById(el);
    };
    
    var print = function(str) {
        var outputArea = $("jsonOutput");
        while (outputArea.firstChild) {
            outputArea.removeChild(outputArea.firstChild);
        }
        outputArea.appendChild(document.createTextNode(str));

        // now kill all old div entries.
        var container = $("output_container");
        while (container.firstChild.nodeName == "DIV" || container.firstChild.nodeName == "#text") {
            container.removeChild(container.firstChild);
        }

        dp.SyntaxHighlighter.HighlightAll('code', true, false, false, 1);
    };

    var handleResults = function(o) {
        if (o.success) {
            try {
                print(JSON.stringify(o.value, null, "  ")+"\n \n");
            } catch(x) {
                print("Error: " + x + "\n");
            }
        } else {
            print("Call failed: (" + o.error + "): " + o.verboseError);
        }
    };

    var makeRequest = function() {
        var form = $("url_input_form");
        var sUrl = form.url.value;
        print("Fetching " + sUrl);

        BrowserPlus.JSONRequest.get({url: sUrl}, handleResults);
        return false;
    };

    // bp.notInstalled
    
    var progressCB = function(status) {
        $('bpinstall').innerHTML = "Loading " + status.name + " - " + status.totalPercentage + "%";
    };

    var requireCB = function(res) {
        $("bpinstall").style.display='none';
        if (res.success) {
            var f = $("url_input_form");
            f.onsubmit = makeRequest;
            makeRequest();    
        } else {
            print("error loading required BrowserPlus services " + res.error + ", " + res.verboseError);
        }
    };

    var initCB = function(res) {
        if (res.success) {
            $("bpinstall").style.display='block';
            var services = [{ service: "JSONRequest", version: "1", minversion: "1.0.5" }];
            BrowserPlus.require( {services: services, progressCallback: progressCB }, requireCB);
        } else if (res.error === "bp.notInstalled") {
          $("bpinstall").style.display='block';
          $("bpinstall").innerHTML = 'Click here to <a target="_blank" href="http://browserplus.yahoo.com/install/">install BrowserPlus</a>.';
        } else if (res.error === "bp.unsupportedClient") {
          $("bpinstall").style.display='block';
          $("bpinstall").innerHTML = "Oops!  This demonstration doesn't yet run on your browser.  Click here for <a target=\"_blank\" href=\"/install\">system requirements</a>.";
        } else {
           throw "initCB failed err=" + res.error;
        }
    };
    
    return {
        init: function() {
            var url;
            if ( location.hash.length == 0 ) {
                url = "http://api.flickr.com/services/feeds/photos_public.gne?format=json&nojsoncallback=1";
            } else {
                url = location.hash.substring(1);
            }

            var form = $("url_input_form");
            form.url.value = url;

			BPInstallerUI.start({pathToJar: "/installer"}, initCB);
        }
    };
}();

JsonRequestDemoModule.init();

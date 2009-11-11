# BrowserPlus.org Website


## Assumptions

The current directory is:
	/home/websites/browserplus
	
In php.ini, the following variable is set:

    dok_base=/home/websites/browserplus/data


## Database

While the borg uses the file system to serve most of it's content, a database (MySQL) is used
to retrieve IRC conversations and cronjob result.

Schemas are located under data/sql.

The file "dbpasswd.json" lives in /home/borg/dbpasswd.json  Contents:

	{ 
	    "irc": {
	        "server": "DB Host",
	        "db": "DB Table Name",
	        "user": "DB User Name",
	        "pass": "DB Password"
	    },
    
	    "git": {
	        "server": "DB Host",
	        "db": "DB Table Name",
	        "user": "DB User Name",
	        "pass": "DB Password"
	    }
	}

## Cron Jobs

To keep git commits up-to-date, `bin/fetch_commits.php` needs to run periodically.

## Dok CMS

The Dok CMS translates a directory of text files into a web site.  The Doc/ tab on the web site
is made from the files under data/pages/docs.

Rules of the road:

* Default File
  * If "index.*" appears in the directory being served, that file is the default shown.
  * Above is a problem, cause title of file would appear as "Index".  
  * Instead, prefix file names with digits like this: "00_this_is_my_default_file.md"
* Extensions
  * <file>.md is translated into html via Markdown and put in the site template
  * <file>.html is untouched but put within the site template too
  * <file>.raw is untouched and served as html without a template.  Use this for code examples.
* File Names
  * File names are translated into the html title element. 
  * Words break on underscores.  
  * Each word is capitalized.
  * If you want special capitalization, like BrowserPlus, include capitalization in the filename.
  * If you want special characters, `data/dok/helper.php` include TitleMap which can translate files like "c_cpp" into "C/C++".

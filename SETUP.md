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

The file "dbpasswd.json" lives in /home/browserplus.  Contents:

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

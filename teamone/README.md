## How to view the website on a local environment using xampp.

1) Download XAMPP from https://www.apachefriends.org/index.html.

2) Install it to your preferred directory/folder.

3) Put the root directory (CS-160-Project) of our git repository anywhere you wish. It is desirable to have it directly under the xampp > htdocs directory.

4) Create a new virtual host for the website. Access the hosts file (In linux-based system, it is usually under /etc).<br />
Inside httpd-vhosts.conf, go to line (about) 20, uncomment (get rid of the sharp '#') NameVirtualHost: *80.
At the very bottom of the file, add the following code:<br />
```
<VirtualHost *:80>
    DocumentRoot "<path to the root folder CS-160-Project>/teamone"
	ServerName teamone
</VirtualHost>
```
In the hosts file, add the following line:<br />
`127.0.0.1 teamone`

5) Open httpd.conf (usually directly under the _etc_ directory), and uncomment (get rid of the sharp #) the line where it says `Include etc/extra/httpd-vhosts.conf `.

6) Run xampp with administrator privilege (sudo, perhaps?), and start running the apache server.

7) It works!

## What to include in the .htaccess

To correctly run the website we need the appropriate .htaccess file.

Create a file named ".htaccess" and add the following inside:
```
ErrorDocument 404 "/err/404.php"

php_value include_path "<Absolute path to the root directory 'CS-160-Project'>/teamone/php"
php_value display_errors 1
php_value max_file_uploads 10M
php_value post_max_size 11M
SetEnv PGSQL_DB_DSN "pgsql:host=<host name (usually localhost)> user=<username to connect as> dbname=teamone password=<password for your postgresql user>"
 ```
 Restart Xampp and run the website!
 
 ## If videos seem to not get processed
 
 Try giving all of what is under teamone/res the permission of 777.
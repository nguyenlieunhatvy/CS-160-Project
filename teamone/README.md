How to view the website on a local environment using xampp.

1) Download XAMPP from https://www.apachefriends.org/index.html.

2) There should be a folder named 'xampp'; move it to your preferred directory.

3) Put the root directory (CS-160-Project) of our git repository directly under xampp > htdocs.

3) Create a new virtual host for the website. For Mac, this article may help: https://jonathannicol.com/blog/2012/03/11/configuring-virtualhosts-in-xampp-on-mac/
'httpd-vhosts.conf' should be in the xampp > apache > conf > extra directory.
To acccess the host file, check the article posted above.
Inside httpd-vhosts.conf, go to line (about) 20, uncomment (get rid of the sharp '#') NameVirtualHost: *80.
At the very bottom of the file, add the following code:
<VirtualHost *:80>
    DocumentRoot "<path to xampp folder>/xampp/htdocs/CS-160-Project/teamone"
	ServerName teamone
</VirtualHost>

Make sure to edit the hosts file as shown in the article above!

4) Run xampp with administrator privilege (sudo, perhaps?), and click the 'start' button on the Apache row (topmost).

5) It works!

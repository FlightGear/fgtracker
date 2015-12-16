FGtracker server version 2 incomplete

1.Supporting FGMS
Current version (Version 2.0a) support the following fgms:
v0.10 : v0.10.23 and above
v0.11 :	NOT OFFICALLY SUPPORTED (Due to bugs)
v0.12 : All versions

2. NOTICE to Windows user
This program should be able to run in Windows environment. However, the exit routine is not 
implemented because of lack of signal handling (SIGINT). Unless at the time of quit the
sockets are idle, otherwise data discrepancy may occur.

3. System requirements
See heading of server.php

4. Parameters
System parameters can be set at the heading of server.php

5. Run
Simply type "php server.php" to run the server

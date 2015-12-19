FGtracker server version 2.1alpha

1.Supporting FGMS
Current version (Version 2.1alpha) support the following fgms:
v0.10 : v0.10.23 and above
v0.11 :	NOT OFFICALLY SUPPORTED (Due to bugs)
v0.12 : All versions

2. NOTICE to Windows user
This program should be able to run in Windows environment. However, the exit
routine is not implemented because of lack of signal handling (SIGINT). Unless
at the time of quit the sockets are idle, otherwise data discrepancy may occur.

3. System requirements
See heading of server.php

4. Parameters
System parameters can be set at the heading of server.php

5. Run
Simply type "php server.php" to run the server

6. FGMS identification
A new function "IDENT" is introduced to identify FGMS along with introduction 
of new protocal between FGMS and FGtracker since FGMS v0.12. Only identified
FGMS can send data to FGtracker. FGMS information is stored in table 
fgms_servers. Here below is the example:

	For FGMS v0.10.23 and above:
	name = <FGMS name (e.g. MPSERVERXX)>
	ip = <IP of FGMS>
	key = NOWAIT
	maintainer = <FGMS maintainer>
	location = <FGMS location (e.g. Berlin, Germany)>
	email = <email address of FGMS maintainer> (ISNULL)
	receive_email = <boolean TRUE/FALSE>
	last_comm = NULL
	enabled = (boolean) TRUE

	For FGMS v0.12 and above:
	name = <FGMS name (e.g. MPSERVERXX)>
	ip = <IP/Domain of FGMS>
	key = V20151207
	maintainer = <FGMS maintainer>
	location = <FGMS location (e.g. Berlin, Germany)>
	email = <email address of FGMS maintainer> (ISNULL)
	receive_email = <boolean TRUE/FALSE>
	last_comm = NULL
	enabled = (boolean) TRUE

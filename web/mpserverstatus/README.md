# FGMS Status

This code generates a FGMS Server status, updated each minute... shows a list of FG Multiplayer servers active, and whether they are `tracked`...

The list of FGMS servers `tested` is controlled by the file `_fgms_conf.php`. It generates the list of servers `checked`. Adjust this to suit your needs...

That file also sets the host site name displayed on the status page. See `$host_site = "localhost";`, and adjust accordingly!

The update of the FGMS server table is set to each minute. See `index.php`, and change `setInterval('check_mpservers()',60000);` to as desired... 

Be aware this check UP/DOWN is based solely on doing a telnet fetch to the control port of each FGMS server! This should **NOT** be done too frequently. It was **NOT** the original purpose of this port, and should technically only be done with the approval of each FGMS maintainer. But light usage would probably be tolerated by **ALL**...

On the other hand it does provide [FG](http://www.flightgear.org/) multiplayer users a valuable service, to choose a mpserver close to their location, and/or less busy, thus helping balance the network load... Or at least select a `working` mpserver!

; eof

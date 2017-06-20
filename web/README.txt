FGTracker Web

1. FGTracker web consists of the following parts:
-xoops_modules/fgplanner: FGPlanner (XOOPS modules)
-xoops_modules/fgtracker: FGTracker (XOOPS modules)
-xoops_modules/fgtracker/interface.php: FGTracker API
-mpserverstatus: Multiplayer server status page, telnet based
-mpserverstatus2: Multiplayer server status page, tracker based
-maps: Useful map(s) which gets data from FGTracker

2. Database parameters
You have to set Database parameters (e.g. DB username and password) in the 
following files:
-xoops_modules/fgplanner/include/db_connect.php
-xoops_modules/fgtracker/include/db_connect.php

3. Consent from FGMS maintainer
You MUST　obtain　consent from any maintainer of target FGMS before running 
mpserverstatus on that target FGMS as mpserverstatus may generate huge load to
that FGMS.
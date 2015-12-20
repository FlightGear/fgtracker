FGTracker service Version 1.0INCOMPLETE

1. This program fixes irregular data and data twisting on completed flights. It's
main function is to:
- remove irregular waypoints
- remove flights with negative duration
- add arrival and departure airports
- update effective flight time
- update callsign rankings (all time, last week, last 30days etc.)

2. NOTICE to Windows user
This program should be able to run in Windows environment. However, the exit
routine is not implemented because of lack of signal handling (SIGINT). Please
terminate the program when the service is idle, otherwise data discrepancy may
occur.

3. System requirements
See heading of service.php

5. Run
Simply type "php service.php" to run the service
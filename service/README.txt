FGTracker service Version 1.0INCOMPLETE

1. This program consistes of two modes: Normal mode and archive mode.
In normal mode, this program fixes irregular data and data twisting on 
completed flights. I.e.
- remove irregular waypoints
- remove flights with negative duration
- add arrival and departure airports
- update effective flight time
- update callsign rankings (all time, last week, last 30days etc.)

In archive mode, this program will attempt to archive flight data by moving
flight data in table 'flights' to table 'flights_archive'. It also try to shrink
data by removing non-useful data such as nowaypoint flights, waypoints during 
flight idling etc.

2. NOTICE to Windows user
This program should be able to run in Windows environment. However, the exit
routine is not implemented because of lack of signal handling (SIGINT). Please
terminate the program when the service is idle, otherwise data discrepancy may
occur.

3. System requirements
See heading of service.php

4. Parameters
Sample of Parameters is located in config.sample.php. Copy this file to 
config.php and set the parameters according to your system enviroment.

5. Run
Normal mode: Simply type "php service.php" to run the service
Archive mode: Type "php service.php archive" to run the service
-Note: No more than one instance of FGTracker service shall be run at the same
time (regardless of serving mode).
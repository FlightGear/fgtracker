The FlightGear Tracker (FGTracker) tracks flights performed on FlightGear MultiPlayer Server (FGMS). 

In order the flights in FGMS be tracked, FGMS maintainer must set its FGMS be tracked and Tracker IP/Domain be pointed to FGTracker.

FGTracker consists of three modules:
- server : A PHP-CLI program that receive position information of the flight and stores into Database (currently postgresql only)
- service: A standalone PHP-CLI program that do datafixes and ETL (generate Dep/Arr airport, callsign ranking etc.)
- web : Including XOOPS module presenting flight information on web server and API service

See individual README file in the modules for more information.
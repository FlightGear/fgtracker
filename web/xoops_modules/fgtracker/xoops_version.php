<?php
// $Id: xoops_version.php,v 1.10 2004/12/26 19:12:10 onokazu Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
$modversion['name'] = _MI_FGTRACKER_NAME;
$modversion['version'] = 1.00;
$modversion['description'] = "_MI_FGTRACKER_DESC";
$modversion['credits'] = "Gabor Toth ( http://www.i-net.hu/ ), The XOOPS Project";
$modversion['author'] = "Hazuki Amamiya (http://mpserver15.flightgear.org)";
$modversion['help'] = "";
$modversion['license'] = "GPL see LICENSE";
$modversion['official'] = "no";
$modversion['image'] = "icons/xoops_logo.png";
$modversion['dirname'] = "fgtracker";

// Admin things
$modversion['hasAdmin'] = 0;
$modversion['adminmenu'] = "";

// Menu
$modversion['hasMain'] = 1;

// Templates
$modversion['templates'][1]['file'] = 'select_callsign.html';
$modversion['templates'][1]['description'] = '';
$modversion['templates'][2]['file'] = 'show_flights.html';
$modversion['templates'][2]['description'] = '';
$modversion['templates'][3]['file'] = 'show_flight.html';
$modversion['templates'][3]['description'] = '';
$modversion['templates'][4]['file'] = 'show_flight2.html';
$modversion['templates'][4]['description'] = '';
$modversion['templates'][5]['file'] = 'show_flight_plan.html';
$modversion['templates'][5]['description'] = '';
$modversion['templates'][6]['file'] = 'show_error.html';
$modversion['templates'][6]['description'] = '';
$modversion['templates'][7]['file'] = 'show_rank.html';
$modversion['templates'][7]['description'] = '';
$modversion['templates'][8]['file'] = 'show_airport.html';
$modversion['templates'][8]['description'] = '';
$modversion['templates'][9]['file'] = 'show_plane.html';
$modversion['templates'][9]['description'] = '';
?>

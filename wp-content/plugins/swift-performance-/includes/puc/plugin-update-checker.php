<?php
/**
 * Plugin Update Checker Library 4.0.3
 * http://w-shadow.com/
 * 
 * Copyright 2017 Janis Elsts
 * Released under the MIT license. See license.txt for details.
 */

require dirname(__FILE__) . '/Puc/v4/Autoloader.php';
new Puc_v4_Autoloader();

//Register classes defined in this file with the factory.
Puc_v4_Factory::addVersion('Plugin_UpdateChecker', 'Puc_v4_Plugin_UpdateChecker', '4.0');
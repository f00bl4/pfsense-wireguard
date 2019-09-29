#!/usr/local/bin/php-cgi -f

#rc.wireguard.sh
#
#part of pfSense (https://www.pfsense.org)
#Copyright (c) 2015 Rubicon Communications, LLC (Netgate)
#All rights reserved.
#
#Licensed under the Apache License, Version 2.0 (the "License");
#you may not use this file except in compliance with the License.
#You may obtain a copy of the License at
#
#http://www.apache.org/licenses/LICENSE-2.0
#
#Unless required by applicable law or agreed to in writing, software
#distributed under the License is distributed on an "AS IS" BASIS,
#WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#See the License for the specific language governing permissions and
#limitations under the License.


<?php

/*
    Start enabled Wireguard tunnels.
    /usr/local/etc/rc.d/
*/

require_once("globals.inc");
require_once("functions.inc");
require_once("wireguard/wireguard.inc");

parse_config('true');

$wg_config = $config['wireguard'];

foreach($wg_config as $tmp_interface => $tmp_int_config) {
    if(empty($wg_config[$tmp_interface]['disable'])){
        exec('ifconfig ' . escapeshellarg($tmp_interface ) . ' destroy');
        wg_start($tmp_interface);
    }

}

?>


<?php
/*
 * pkg_wireguard.inc
 *
 * modified by f00bl4
 *
 * part of pfSense (https://www.pfsense.org)
 * Copyright (c) 2016 Rubicon Communications, LLC (Netgate)
 * Copyright (c) 2013 Marcello Coutinho <marcellocoutinho@gmail.com>
 * All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

global $shortcuts;

$shortcuts['wireguard'] = array();
$shortcuts['wireguard']['main'] = "/wireguard/wg_config.php";
/*$shortcuts['wireguard']['log'] = "/status_pkglogs.php?pkg=wireguard";*/
$shortcuts['wireguard']['status'] = "/wireguard/wg_stats.php";

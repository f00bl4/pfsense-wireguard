<?php
/*
 * wireguard.priv.inc
 *
 * modified by f00bl4
 *
 * part of pfSense (https://www.pfsense.org)
 * Copyright (c) 2015 Rubicon Communications, LLC (Netgate)
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

global $priv_list;

$priv_list['page-vpn-wireguard'] = array();
$priv_list['page-vpn-wireguard']['name'] = "WebCfg - VPN: Wireguard package";
$priv_list['page-vpn-wireguard']['descr'] = "Allow access to Wireguard package GUI";
$priv_list['page-vpn-wireguard']['match'] = array();

$priv_list['page-vpn-wireguard']['match'][] = "wireguard/wg_config.php*";
$priv_list['page-vpn-wireguard']['match'][] = "wireguard/wg_interface.php*";
$priv_list['page-vpn-wireguard']['match'][] = "wireguard/wg_peer.php*";
$priv_list['page-vpn-wireguard']['match'][] = "wireguard/wg_function.php*";
$priv_list['page-vpn-wireguard']['match'][] = "wireguard/wg_import.php*";

$priv_list['page-vpn-wireguard-stats'] = array();
$priv_list['page-vpn-wireguard-stats']['name'] = "WebCfg - VPN: Wireguard package stats";
$priv_list['page-vpn-wireguard-stats']['descr'] = "Allow access to Wireguard package GUI stats";
$priv_list['page-vpn-wireguard-stats']['match'] = array();
$priv_list['page-vpn-wireguard-stats']['match'][] = "wireguard/wg_stats.php*";

?>

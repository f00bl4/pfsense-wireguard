<?php
/*
 * wg_function.php
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

function wg_write($tmp_config, $interface){

	/*config file mapping*/
	$map_interface = array ( 
		'local_address' => 'Address',
		'listen_port' => 'ListenPort',
		'private_key' => 'PrivateKey',
		'wg_dns' => 'DNS'
	);

	$map_peer = array (
		'endpoint' => 'Endpoint',
		'public_key' => 'PublicKey',
		'psk' => 'PresharedKey',
		'allowed_ips' => 'AllowedIPs',
		'keepalive' => 'PersistentKeepalive'
	);

	$wgcfg_location = '/usr/local/etc/wireguard/';
	$tmp_file_name = $interface . '.conf';
	$tmp_cfg_location = $wgcfg_location . $tmp_file_name;
	
	if (is_file($tmp_cfg_location)) {
		unlink($wgcfg_location);	//rewrite config file
	}
	
	$wg_file = fopen($wgcfg_location . $tmp_file_name, 'w');

	/*writing interface to config file*/
	fwrite ($wg_file,'[Interface]'. "\n" );
	
	foreach($map_interface as $key => $tmp_interface) {
		if(!empty($tmp_config[$key])){
			fwrite($wg_file,$tmp_interface . ' = ' . $tmp_config[$key] . "\n");
		};
	};

	/*writing peers to config file*/
	foreach($tmp_config['peer'] as $key_peer => $peer) {
		fwrite ($wg_file,"\n" . '[Peer]'. "\n" );
		if (!$peer['disable']) {
			foreach($map_peer as $key => $tmp_peer) {
				if(!empty($peer[$key])){
					fwrite($wg_file,$tmp_peer . ' = ' . $peer[$key] . "\n");
				}
			};
		};
	};

};

function wg_key($private_key = null){
	$keyoutput = "";
	$keystatus = "";
	if (!isset($private_key)){
		exec("wg genkey", $keyoutput, $keystatus);
		exec("echo $keyoutput[0] | wg pubkey", $pub_keyoutput, $pub_keystatus);
		return array($keyoutput[0],$pub_keyoutput[0]);
	}
	elseif((bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $private_key)) {
		exec('echo ' . escapeshellarg($private_key) . ' | wg pubkey', $pub_keyoutput, $pub_keystatus);
		return array($private_key, $pub_keyoutput[0]); 
	}
	else {
		return array('false', 'false');
	}
}

function wg_status($interface){
	exec('wg show ' . escapeshellarg($interface), $output);
	return $output;
}

function wg_start($interface){
	exec('wg-quick up ' . escapeshellarg($interface));
}

function wg_stop($interface){
	exec('wg-quick down ' . escapeshellarg($interface));
}

function wg_restart($interface){
	wg_stop($interface);
	wg_start($interface);
}

function get_wg_action($interface){
	if (! wg_status($interface)){
		$link = '<a class="fa fa-play-circle" title="Start Tunnel" href="wg_config.php?act=start&amp;interface=' . $interface . '"></a>';
		#$link = '<a id=wg-start-' . $interface .' class="fa fa-play-circle" title="Start Tunnel" href="#"></a>';
	}
	else{
		#$link = '<a href="#" id="wg-stop-' . $interface . '" >';
		#$link = '<i class="fa fa-stop-circle-o" title="Stop Tunnel" href="wg_config.php?act=stop&amp;interface=' . $interface .'"></i></a>';
		$link = '<a class="fa fa-stop-circle" title="Stop Tunnel" href="wg_config.php?act=stop&amp;interface=' . $interface . '"></a>';
	}

	return $link;
}

?>
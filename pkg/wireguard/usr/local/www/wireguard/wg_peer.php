<?php
/*
 * wg_peer.php
 * 
 * modified by f00bl4
 *
 * part of pfSense (https://www.pfsense.org)
 * Copyright (c) 2004-2018 Rubicon Communications, LLC (Netgate)
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

##|+PRIV
##|*IDENT=page-vpn-vpnwg
##|*NAME=VPN: L2TP
##|*DESCR=Allow access to the 'VPN: W' page.
##|*MATCH=vpn_wg.php*
##|-PRIV

require_once("guiconfig.inc");
require_once("vpn.inc");
require_once("wireguard/wireguard.inc");


$wgcfg = &$config['wireguard'];
$wgcfg_location = '/usr/local/etc/wireguard/';

if ($_REQUEST['generatepsk']) {
	$keyoutput = "";
	$keystatus = "";
	/*exec("/bin/dd status=none if=/dev/random bs=4096 count=1 | /usr/bin/openssl sha224 | /usr/bin/cut -f2 -d' '", $keyoutput, $keystatus);*/
	exec("wg genpsk", $keyoutput, $keystatus);
	print json_encode(['psk' => $keyoutput[0]]);
	exit;
}


if ($_POST['save'] && $_GET['interface']) {
	unset($input_errors);
	$wg_peer = $_POST;
	/* input validation */
	
	$reqdfields = explode(" ", "peer_name public_key allowed_ips");
	$reqdfieldsn = array(gettext("Server address"), gettext("Remote start address"));

	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	
	if ($_GET['act'] == 'add' && !empty($wgcfg[$_GET['interface']]['peer'][$_POST['peer_name']])) {
		$input_errors[] = gettext("Peer Name already exist");
	};

	/*Check Endpoint*/
	$tmp_endpoint = explode ( ':' , $_POST['endpoint']);
	if ( !((is_domain( $tmp_endpoint[0]) xor is_ipaddr($tmp_endpoint[0])) && is_port(intval($tmp_endpoint[1]))) xor empty($_POST['enpoint']))  {
		$input_errors[] = gettext("Endpoint is not configured correctly");
	}

	/*Check Allowed IPs*/
	$tmp_allowed_ips = explode ( ',' , $_POST['allowed_ips']);
	foreach($tmp_allowed_ips as $key_allowed_ips => $tmp_ip) {
		if ( !(is_ipaddr($tmp_ip) xor is_subnet($tmp_ip))){
			$input_errors[] = gettext($tmp_ip . "is not a valid IP address");
		}
		if ( is_ipaddr_configured($tmp_ip)) {
			$input_errors[] = gettext($tmp_ip . "is already in use");
		}
	}

	/*Check public for base64 and length*/
	if (preg_match('/[^a-z0-9\+\/\=]/i', $_POST['public_key']) || strlen(base64_decode($_POST['public_key'])) != 32 ) {
		$input_errors[] = gettext("Public key has to be BASE64 and 32 byte");
	};

	/*Check PSK for BASE64*/
	if (preg_match('/[^a-z0-9\+\/\=]/i', $_POST['psk']) || strlen(base64_decode($_POST['psk'])) > 32 ) {
		$input_errors[] = gettext("PSK has to be BASE64 and max. 32 byte");
	};

	/*Check Keepalive for int*/
	if ( !(is_numericint($_POST['keepalive']) xor empty($_POST['keepalive']))){
		$input_errors[] = gettext("Keepalive has to be an Integer");
	}

	/* input validation end*/	

	if (!$input_errors) {
		parse_config(true);
		$wgcfg[$_GET['interface']]['peer'][$_POST['peer_name']] = array (
			'disable' => $_POST['disable'],
			'description' => $_POST['description'],
			'endpoint' => $_POST['endpoint'],
			'public_key' => $_POST['public_key'],
			'psk' => $_POST['psk'],
			'allowed_ips' => $_POST['allowed_ips'],
			'keepalive' => $_POST['keepalive'],
		);

		write_config(gettext('Wireguard configuration changed.'));

		if (($_GET['act'] == 'edit' || $_GET['act'] == 'add' && empty($_POST['disable'])) && empty(wgcfg[$_REQUEST['interface']]['disable']) ) {
			wg_write($wgcfg[$_GET['interface']], $_REQUEST['interface']);
			wg_restart($_REQUEST['interface']);
		};
		
		$changes_applied = true;
		$retval = 0;   
	}
}
?>
<?php
if ($_GET['interface'] && $_REQUEST['peer_name'] && !$input_errors){ //&& !empty($wgcfg[$_GET['interface']['peer'][$_REQUEST['peer_name']])) {

	/*input validation*/
	$wg_peer = $wgcfg[$_GET['interface']]['peer'][$_REQUEST['peer_name']];
	$wg_peer['peer_name'] = $_REQUEST['peer_name'];

}
?>
<?php
	if ($_GET['act'] == 'del' && $_GET['interface'] && $_GET['peer_name']) {
		parse_config(true);
		unset($config['wireguard'][$_GET['interface']]['peer'][$_GET['peer_name']]);

		//delete config file and rewrite it withoud deleted peer
		$tmp_cfg_location = $wgcfg_location . $_GET['interface'] . '.conf';
		if (!$config['wireguard'][$_POST['interface']]['disable'] && is_file($tmp_cfg_location) && ctype_alnum($_GET['interface'])) {
			unlink($tmp_cfg_location);
			wg_write($config['wireguard'][$_GET['interface']], $_GET['interface']);
			wg_restart($_GET['interface']);
		}

		if (empty($config['wireguard'][$_GET['interface']]['peer'])) {
			unset($config['wireguard'][$_GET['interface']]['peer']);
		}
		
		write_config(gettext('Wireguard configuration changed.'));

		header("Location: " . $_SERVER['HTTP_REFERER']);
		die();
	}

?>


<?php
if (($_GET['act'] == 'add' || $_GET['act'] == 'edit' ) && !empty($wgcfg[$_GET['interface']])) {
	
	$pgtitle = array(gettext("VPN"), gettext("Wireguard"), gettext("Peer"));
	$pglinks = array("", "wireguard/wg_config.php", "@self");
	$shortcut_section = "wgs";
	include("head.inc");
	
	if ($input_errors) {
		print_input_errors($input_errors);
	}
	
	if ($changes_applied) {
		print_apply_result_box($retval);
	}
	
	
	$form = new Form();
	
	$section = new Form_Section('General Configuration');

	$section->addInput(new Form_Checkbox(
			'disable',
			'Disabled',
			'Disable this peer',
			$wg_peer['disable']
	))->setHelp('Set this option to disable this peer without removing it from the list.');

	$section->addInput(new Form_Input(
		'peer_name',
		'*Peer Name',
		'text',
		$wg_peer['peer_name']
	))->setPattern('[a-zA-Z0-9.-_]+')->setHelp('Peer Name only for Web Configuration');


	$section->addInput(new Form_Input(
			'description',
			'*Description',
			'text',
			$wg_peer['description']
	))->setHelp('Description of Wireguard Tunnel');

	$form->add($section);	
	$section = new Form_Section("Peer");

	$section->addInput(new Form_Input(
			'endpoint',
			'*Endpoint',
			'text',
			$wg_peer['endpoint']
	))->setPattern('[a-zA-Z0-9.:-_]+')->setHelp('Local IP and Port');
	
	$section->addInput(new Form_Input(
			'public_key',
			'*Public key',
			'text',
			$wg_peer['public_key']
	))->setPattern('[a-zA-Z0-9+/=]+')->setHelp('Public key');
	
	$group = new Form_Group($counter == 0 ? '*Preshared Key' : '');

	$group->add(new Form_Input(
			'psk',
			'*Preshared key',
			'text',
			$wg_peer['psk']
	)) ->setHelp('Preshared key');

	$group->add(new Form_Button(
	'generate_psk',
	'Generate PSK',
	null,
	'fa-refresh'))->addClass('btn btn-xs btn-warning');

	$section->add($group);

	$section->addInput(new Form_IpAddress(
			'allowed_ips',
			'*Allowed IPs',
			$wg_peer['allowed_ips']
	)) ->setHelp('Allowed IPs');
	
	$section->addInput(new Form_Input(
			'keepalive',
			'*Keep alive',
			'number',
			$wg_peer['keepalive']
	))->setPattern('[0-9]+')->setHelp('Keep alive');
	
	$form->add($section);
	print($form);

}


?>
<div class="infoblock blockopen">
<?php
	print_info_box(gettext("Don't forget to add a firewall rule to permit traffic from Wireguard."), 'info', false);
?>
</div>

<form action="wg_peer.php" method="post" name="iform" id="iform">

<script type="text/javascript">
//<![CDATA[
events.push(function() {

	
	$('[id^=generate]').prop('type','button');
	$('[id^=generate_psk]').click(function() {
			
		$.ajax({
			type: 'get',
			url: 'wg_peer.php?generatepsk=true',
			dataType: 'json',
			success: function(data) {
				$(psk).val(data.psk.replace(/\\n/g, '\n'));
			}
		});
	});


});
//]]>
</script>
</form>



<?php include("foot.inc")?>


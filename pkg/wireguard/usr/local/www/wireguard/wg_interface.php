<?php
/*
 * wg_interface.php
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

if ($_REQUEST['generatekey']) {
	
	$tmp_wg_key = wg_key();
	print json_encode(['private_key' => $tmp_wg_key[0], 'public_key' => $tmp_wg_key[1]]);
	exit;
}

if ($_POST['save'] ) {

	unset($input_errors);
	$wg_interface = $_POST;
	/* input validation */
	
	$reqdfields = explode(" ", "interface local_ip local_subnet private_key");
	$reqdfieldsn = array(gettext("Server address"), gettext("Remote start address"), gettext("Listen Port"), gettext("Private Key"));

	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	
	/*Peer name exist*/
	if ($_GET['act'] == 'add' && !empty($wgcfg[$_GET['interface']]['peer'][$_POST['peer_name']])) {
		$input_errors[] = gettext("Peer Name already exist");
	};
	/*Interface exist in wireguard config and global*/
	if(!ctype_alnum($_GET['interface']) || $_GET['act'] == add && !empty($wgcfg[$_GET['interface']])){
		$input_errors[] = gettext("A valid Interface is needed.");
	}
	if ( !is_ipaddr($_POST['local_ip'])) {
		$input_errors[] = gettext("A valid server address must be specified.");
	}
	if (is_ipaddr_configured($_POST['local_ip'] . '/' . $_POST['local_subnet'])) {
		$input_errors[] = gettext("'Server address' parameter should NOT be set to any IP address currently in use on this firewall.");
	}
/*	if (0 > $_POST['local_subnet'] && $_POST['local_subnet'] > 32) {
		$input_errors[] = gettext("A valid subnet.");
	}*/
	if (!(is_port($_POST['listen_port']) xor empty($_POST['listen_port']))) {
		$input_errors[] = gettext("Please enter a valid port number.");
	}
	if (!(empty($_POST['wg_dns'] xor is_ipaddr($_POST['wg_dns'])) )){
		$input_errors[] = gettext("Please enter a valid DNS IP address.");
	}
	/* input validation end*/

	if (!$input_errors) {
		parse_config(true);	
		/*Has to be each. Otherwise the peers will be overwritten.*/
		$wg_interface = &$wgcfg[$_REQUEST['interface']];

		$wg_interface['disable'] = $_POST['disable'];
		$wg_interface['description'] = $_POST['description'];
		$wg_interface['local_address'] = $_POST['local_ip'] . "/" . $_POST['local_subnet'];
		$wg_interface['listen_port'] = $_POST['listen_port'];
		$wg_interface['private_key'] = $_POST['private_key'];
		$wg_interface['wg_dns'] = $_POST['wg_dns'];

		write_config(gettext('Wireguard configuration changed.'));

		if (empty($_POST['disable'])) {
			wg_write($wgcfg[$_REQUEST['interface']], $_REQUEST['interface']);
			wg_restart($_REQUEST['interface']);
		}
		elseif ($_POST['disable'] && $_GET['act'] == 'edit'){
			wg_stop($_REQUEST['interface']);
			/*delete config file if not enabled*/
			if (file_exists('/usr/local/etc/wireguard/' . $_REQUEST['interface'] . '.conf')){
				unlink('/usr/local/etc/wireguard/' . $_REQUEST['interface'] . '.conf');
			}
		};
		$changes_applied = true;
		$retval = 0;
	}
};

if (($_GET['act'] == 'edit' || $_POST['save']) && $_REQUEST['interface'] && !$input_errors) {

	/*input validation*/
	$wg_interface = $wgcfg[$_REQUEST['interface']];
	$wg_interface['interface'] = $_REQUEST['interface'];
	list($wg_interface['local_ip'],$wg_interface['local_subnet']) = explode("/",$wg_interface['local_address']); //split local_address to IP and subnetmask
};

?>

<?php
	if ($_GET['act'] == 'del' && $_GET['interface'] ) {
		parse_config(true);
		unset($config['wireguard'][$_GET['interface']]);

		//delete interface configuration
		$tmp_cfg_location = $wgcfg_location . $_GET['interface'] . '.conf';
		if (!$config['wireguard'][$_POST['interface']]['disable'] && is_file($tmp_cfg_location) && ctype_alnum($_GET['interface'])) {
			wg_stop($_GET['interface']);
			unlink($tmp_cfg_location);
		}
		
		if (empty($config['wireguard'])) {
			unset($config['wireguard']);
		}
		
		write_config(gettext('Wireguard configuration changed.'));
		header("Location: " . $_SERVER['HTTP_REFERER']);
		die();
	}

?>


<?php
if ($_GET['act'] == 'add' || ($_GET['act'] == 'edit' )) {
	
	$pgtitle = array(gettext("VPN"), gettext("Wireguard"), gettext("Configuration"));
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
			'Disable this server',
			$wg_interface['disable']
	))->setHelp('Set this option to disable this server without removing it from the list.');

	$section->addInput(new Form_Input(
			'interface',
			'*Local Interface',
			'text',
			$wg_interface['interface']
	))->setPattern('[a-zA-Z0-9]+')->setHelp('Choose an interface Number');

	$section->addInput(new Form_Input(
			'description',
			'*Description',
			'text',
			$wg_interface['description']
	))->setHelp('Description of Wireguard Tunnel');


	$form->add($section);	
	$section = new Form_Section("Interface");
	
	$section->addInput(new Form_IpAddress(
			'local_ip',
			'*Local address',
			$wg_interface['local_ip']
	))->addMask('local_subnet', $wg_interface['local_subnet'])
	  ->setHelp('Local IP and Subnet');
	
	$section->addInput(new Form_Input(
			'listen_port',
			'*Listen port',
			'text',
			$wg_interface['listen_port']
	))->setPattern('[0-9]+')->setHelp('Listen Port');
	
	$group = new Form_Group($counter == 0 ? '*Private Key' : '');

	$group->add(new Form_Input(
			'private_key',
			'*Private key',
			'text',
			$wg_interface['private_key']
	)) ->setHelp('Private key');

	$group->add(new Form_Button(
	'generate_priv_key',
	'Generate private Key',
	null,
	'fa-refresh'))->addClass('btn btn-xs btn-warning');

	$section->add($group);

	$section->addInput(new Form_Input(
			'pub_srv_key',
			'*Public Server key',
			'text',
			wg_key($wg_interface['private_key'])[1]
	))->setPattern('[a-zA-Z0-9+/=]+')->setHelp('Public Key. Copy-Paste this key to your endpoint.');

	
	$section->addInput(new Form_IpAddress(
			'wg_dns',
			'*DNS',
			$wg_interface['wg_dns']
	)) ->setHelp('DNS');
	$form->add($section);
	print($form);

}


?>
<div class="infoblock blockopen">
<?php
	print_info_box(gettext("Don't forget to add a firewall rule to permit traffic from Wireguard."), 'info', false);
?>
</div>

<form action="wg_interface.php" method="post" name="iform" id="iform">

<script type="text/javascript">
//<![CDATA[
events.push(function() {

		$('[id^=generate]').prop('type','button');
	$('[id^=generate_priv_key]').click(function() {
		$.ajax({
			type: 'get',
			url: 'wg_interface.php?generatekey=true',
			dataType: 'json',
			success: function(data) {
				$('#private_key').val(data.private_key.replace(/\\n/g, '\n'));
				$('#pub_srv_key').val(data.public_key.replace(/\\n/g, '\n'));
			}
		});
	});

});
//]]>
</script>
</form>



<?php include("foot.inc")?>



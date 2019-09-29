<?php
/*
 * wg_config.php
 * 
 * Written and modified by f00bl4
 *
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
##|*IDENT=page-wireguard-config
##|*NAME=VPN: Wireguard 
##|*DESCR=Allow access to the 'Firewall: Aliases' page.
##|*MATCH=wg_config.php.php*
##|-PRIV

require_once("guiconfig.inc");
require_once("functions.inc");
require_once("filter.inc");
require_once("shaper.inc");
require_once("wireguard/wireguard.inc");

$pgtitle = array(gettext("VPN"), gettext("Wireguard"), $bctab);
$pglinks = array("", "wireguard/wg_config.php");
$shortcut_section = "wireguard";

include("head.inc");
parse_config(true);

$wg_config = $config['wireguard'];

if($_GET['act'] == 'start') {
	if(!empty($wg_config[$_REQUEST['interface']]['disable'])){
		$input_errors[] = gettext("Wireguard Interface is disabled");
	}
	else{
		wg_start($_GET['interface']);
		$changes_applied = true;
		$retval = 0;   
	}

	if(!wg_status($_REQUEST['interface'])){
		$input_errors[] = gettext("Wireguard Interface was not able to start");
	}

}

elseif($_GET['act'] == 'stop') {
	wg_stop($_GET['interface']);

	if(wg_status($_REQUEST['interface'])){
		$input_errors[] = gettext("Wireguard Interface was not able to stop");
	}
	else {
		$changes_applied = true;
		$retval = 0;   
	}
}

elseif($_GET['act'] == 'import') {
}

/*Error Messages for Start/Stop and Import Function*/
if ($input_errors) {
	print_input_errors($input_errors);
}

if ($changes_applied) {
	print_apply_result_box($retval);
}

?>

<div class="panel panel-default">
	<div class="panel-heading"><h2 class="panel-title"><?=sprintf(gettext('Wireguard Tunnels'))?></h2></div>
	<div class="panel-body">

<div class="table-responsive">
<table class="table table-striped table-hover table-condensed sortable-theme-bootstrap" > 
	<thead>
		<tr>
			<th><?=gettext("Name")?></th>
			<th><?=gettext("IP Address")?></th>
			<th><?=gettext("Description")?></th>
			<th><?=gettext("Actions")?></th>
		</tr>
	</thead>

	<tbody>
		<?php
			asort($wg_config);
			foreach ($wg_config as $key_interface => $interface):
			unset ($show_alias);	//debug (wirklich nötig?)
			$show_alias = true;		//debug
			if ($show_alias):
		?>
		<tr>
			<td ondblclick="document.location='wg_interface.php?act=edit&interface=<?=$key_interface;?>';">
				<?=htmlspecialchars($interface['interface'])?>
				<?php echo $key_interface;?>
			</td>
			<td ondblclick="document.location='wg_interface.php?act=edit&interface=<?=$key_interface;?>';">
				<?php echo $interface['local_address']; ?>
			</td>
			<td ondblclick="document.location='wg_interface.php?act=edit&interface=<?=$key_interface;?>';">
				<?=htmlspecialchars($interface['description'])?>&nbsp;
			</td>
			<td>
				<?= get_wg_action($key_interface); ?>
				<a class="fa fa-pencil" title="<?=gettext("Edit Interface"); ?>" href="wg_interface.php?act=edit&amp;interface=<?=$key_interface?>"></a>
				<a class="fa fa-trash"	title="<?=gettext("Delete Interface")?>" href="wg_interface.php?act=del&amp;interface=<?=$key_interface?>"></a>
			</td>
		</tr>
		<tr>
			<td></td>
			<td class="contains-table" colspan="3">
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<th><?=gettext("Peer")?></th>
							<th><?=gettext("Description")?></th>
							<th><?=gettext("Actions")?></th>
						</tr>
					</thead>
					<tbody>
						<?php
							foreach ($interface['peer'] as $key_peer => $peer):
						?>
						<tr>
							<td ondblclick="document.location='wg_peer.php?act=edit&interface=<?=$key_interface;?>&peer_name=<?=$key_peer?>';">
								<?=htmlspecialchars($key_peer)?>&nbsp;
							</td>
	
							<td ondblclick="document.location='wg_peer.php?act=edit&interface=<?=$key_interface;?>&peer_name=<?=$key_peer?>';">
								<?=htmlspecialchars($peer['description'])?>&nbsp;
							</td>
	
							<td>
							<a class="fa fa-pencil" title="<?=gettext("Edit Peer"); ?>" href="wg_peer.php?act=edit&amp;interface=<?=$key_interface?>&amp;peer_name=<?=$key_peer?>"></a>
								<a class="fa fa-trash" title="<?=gettext("Delete Peer")?>" href="wg_peer.php?act=del&amp;interface=<?=$key_interface?>&amp;peer_name=<?=$key_peer?>"></a>
							</td>
						</tr>
						<?php endforeach?>
						<tr>
							<td colspan=3>
								<a href="wg_peer.php?act=add&amp;interface=<?=$key_interface?>" role="button" class="btn btn-success btn-sm">
								<i class="fa fa-plus icon-embed-btn"></i>
								<?=gettext("Add Peer");?>
								</a>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>

		<?php endif?>
		<?php endforeach?>
	</tbody>
</table>
</div>

	</div>
</div>

<nav class="action-buttons">
	<a href="wg_interface.php?act=add" role="button" class="btn btn-success btn-sm">
		<i class="fa fa-plus icon-embed-btn"></i>
		<?=gettext("Add");?>
	</a>
	<a href="wg_config.php?act=import" role="button" class="btn btn-primary btn-sm">
		<i class="fa fa-upload icon-embed-btn"></i>
		<?=gettext("Import");?>
	</a>
</nav>

<!-- Information section. Icon ID must be "showinfo" and the information <div> ID must be "infoblock".
	 That way jQuery (in pfenseHelpers.js) will automatically take care of the display. -->
<div>
	<div class="infoblock">
		<?php print_info_box(gettext('Wireguard VPN'), 'info', false); ?>
	</div>
</div>

<?php
include("foot.inc");
?>

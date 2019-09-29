<?php
/*
 * wg_stats.php
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

$pgtitle = array(gettext("Stats"), gettext("Wireguard"), $bctab);
$pglinks = array("", "wireguard/wg_stats.php");
$shortcut_section = "wireguard";

include("head.inc");
parse_config(true);
$wg_config = $config['wireguard']

?>

<div class="panel panel-default">

<?php
	exec('wg show interfaces',$wg_interfaces);
	$tmp_interfaces = explode(' ', $wg_interfaces[0]);
	foreach($tmp_interfaces as $key_interfaces => $tmp_int): 
	unset($tmp_int_details);
	exec("wg show " . escapeshellarg($tmp_int), $tmp_int_details);
	unset($tmp_int_sort);
	$tmp_int_sort = array();
?>
	<div class="panel-heading"><h2 class="panel-title"><?=sprintf($tmp_int)?></h2></div>
	<div class="panel-body">
	<div class="table-responsive">
<table class="table table-striped table-hover table-condensed sortable-theme-bootstrap" > 
	<thead>
		<tr>
			<th><?=gettext($wg_config[$tmp_int]['description'])?></th>
			<th></th>
		</tr>
	</thead>

	<tbody>
<?php
	foreach($tmp_int_details as $key_int_part => $tmp_int_part):
		$tmp_int_sort[ltrim(explode(': ', $tmp_int_part)[0],' ')] = explode(': ', $tmp_int_part)[1];

		if(strpos($tmp_int_part,'peer') !== false ):
?>
		<tr>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<th><?=gettext(ltrim(explode(': ', $tmp_int_part)[0],' '))?></td>
			<th><?=gettext(explode(': ', $tmp_int_part)[1])?></td>
		</tr>
<?php
		elseif(strpos($tmp_int_part,'interface') !== false ):
		elseif(ltrim($tmp_int_part,' ')):
?>	
		<tr>
			<td><?=gettext(ltrim(explode(': ', $tmp_int_part)[0],' '))?></td>
			<td><?=gettext(explode(': ', $tmp_int_part)[1])?></td>
		</tr>
<?php endif ?>
<?php endforeach ?>
	</tbody>
</table>

</div>
	
	
	
</div>

<?php endforeach?>
</div>

<div>
	<div class="infoblock">
		<?php print_info_box(gettext('Wireguard VPN'), 'info', false); ?>
	</div>
</div>

<?php
include("foot.inc");
?>

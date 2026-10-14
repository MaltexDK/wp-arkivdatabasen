<?php

function adb_options_page() 
{

	global $adb_options;
	global $adb_plugin_name;
	global $adb_shortcode;

	ob_start(); ?>
	<div class="wrap">
		<h2>Indstillinger for Arkivdatabasen</h2>
		
		<form method="post" action="options.php">
		
			<?php
			settings_fields('adb_settings_group');
			// Check the API-key and give a message
			$security_check = adb_security_check();
			if ($security_check)
			{
				if (!$security_check["error"])
				{
					$api_message = "<div id='adb-error-valid_api_key' class='updated settings-error'><p><strong>Primært arkiv: ".$security_check["message"]."</strong></p></div> ";
					$adb_arkivnavn = $security_check["arkivnavn"];
				}
				elseif ($security_check["error"])
				{
					$api_message = "<div id='adb-error-invalid_api_key' class='error settings-error'><p><strong>Primært arkiv: ".$security_check["message"]."</strong></p></div> ";
					$adb_arkivnavn = "Ukendt";
				}
				else
				{
					$api_message = "<div id='adb-error-invalid_api_key' class='error settings-error'><p><strong>Primært arkiv: Det er et forkert API der svarer</strong></p></div> ";
					$adb_arkivnavn = "Ukendt";
				}
			}
			else
			{
				$api_message = "<div id='adb-error-invalid_api_key' class='error settings-error'><p><strong>Primært arkiv: API-adressen er forkert eller svarer ikke</strong></p></div> ";
				$adb_arkivnavn = "Ukendt";
			}
			echo $api_message;

			$security_checkurl = adb_security_checkurl();

			if($adb_options['debug']=="1"){
				if ($security_checkurl)
				{
					$api_message = "<div id='adb-error-invalid_api_key' class='notice notice-info'><p><strong>Tjekker på <a href='".$security_checkurl."' target='_blank'>".$security_checkurl."</a>.</strong></p></div> ";
				}
				else
				{
					$api_message = "<div id='adb-error-invalid_api_key' class='error settings-error'><p><strong>API svarer ikke korrekt!</strong></p></div> ";
				}
				echo $api_message;
			}
	
			// Check sekundært arkiv/api
			
			if($adb_options['database2_url']!=""){
				
				$security_check = adb_security_check(2);

				if ($security_check)
				{
					if (!$security_check['error'])
					{
						$api_message = "<div id='adb-error-valid_api_key' class='updated settings-error'><p><strong>Sekundært arkiv: ".$security_check['message']."</strong></p></div> ";
						$adb_arkivnavn2 = $security_check['arkivnavn'];
					}
					elseif ($security_check['error'])
					{
						$api_message = "<div id='adb-error-invalid_api_key' class='error settings-error'><p><strong>Sekundært arkiv: ".$security_check['message']."</strong></p></div> ";
						$adb_arkivnavn2 = "Ukendt";
					}
					else
					{
						$api_message = "<div id='adb-error-invalid_api_key' class='error settings-error'><p><strong>Sekundært arkiv: Det er et forkert API der svarer</strong></p></div> ";
						$adb_arkivnavn2 = "Ukendt";
					}
				}
				else
				{
					$api_message = "<div id='adb-error-invalid_api_key' class='error settings-error'><p><strong>Sekundært arkiv: API-adressen er forkert eller svarer ikke</strong></p></div> ";
					$adb_arkivnavn2 = "Ukendt";
				}
			echo $api_message;
			}
			else{
				$api_arkivnavn ="Ikke valgt/indstillet";
				}
			?>

			<?if($adb_options['debug']=="1"){?><div id='adb-error-valid_api_key' class='notice notice-warning is-dismissible'><p><strong>Data (primær):</strong> <?php var_dump ($security_check); ?></p></div><?}?>
			
			<table class="form-table">
			<tr><th colspan="2"><h3><?php _e('Primær Arkivdatabase', 'adb_domain'); ?></h3><p class="description">Angiv adresse (URL) og API-nøgle til det primære arkiv. Oplysninger findes i Arkivdatabasen under indstillinger.</p></th></tr>
			<tr>
				<th scope="row">
				<label for="adb_settings[database_url]"><?php _e('Database-adresse (URL)', 'adb_domain'); ?></label></th>
				<td><input id="adb_settings[database_url]" name="adb_settings[database_url]" type="text" value="<?php echo $adb_options['database_url']; ?>" class="regular-text" />
				<p class="description" id="database_url-description">Indtast adressen til databasen.</p></td>
			</tr>
			<tr>
				<th scope="row">
				<label for="adb_settings[api_key]"><?php _e('API-nøgle', 'adb_domain'); ?></label></th>
				<td><input id="adb_settings[api_key]" name="adb_settings[api_key]" type="text" value="<?php echo $adb_options['api_key']; ?>" class="regular-text" /> 
				<p class="description" id="api_key-description">Indtast en gyldig API-nøgle.</p></td>
			</tr>
			<tr>
			<?php
				if ($adb_arkivnavn != "Ukendt")
				{
					?>
					<th scope="row">
					<label for="adb_settings[arkivnavn]"><?php _e('Arkivnavn', 'adb_domain'); ?></label></th>
					<td>
						<!--<input id="adb_settings[arkivnavn]" name="adb_settings[arkivnavn] "type="hidden" value="<?php //echo $adb_arkivnavn; ?>" class="regular-text" /> -->
					<?php echo $adb_arkivnavn; ?></td>
					<?php
				}
				else
				{
					?>
					<th scope="row">
					<label for="adb_settings[arkivnavn]"><?php _e('Arkivnavn', 'adb_domain'); ?></label></th>
					<td>
						<!--<input id="adb_settings[arkivnavn]" name="adb_settings[arkivnavn] "type="hidden" value="<?php //echo $adb_arkivnavn; ?>" class="regular-text" /> -->
					<em><?php echo $adb_arkivnavn; ?></em></td>
					<?php
				}
			?>
			</tr>
			<tr><th colspan="2"><h3><?php _e('Sekundær Arkivdatabase', 'adb_domain'); ?></h3><p class="description">Med en sekundær Arkivdatabase har man mulighed for at trække data fra et andet arkiv.</p></th></tr>
			<tr>
				<th scope="row">
				<label for="adb_settings[database2_url]"><?php _e('Database-adresse (URL)', 'adb_domain'); ?></label></th>
				<td><input id="adb_settings[database2_url]" name="adb_settings[database2_url]" type="text" value="<?php echo $adb_options['database2_url']; ?>" class="regular-text" />
				<p class="description" id="database2_url-description">Indtast adressen til databasen.</p></td>
			</tr>
			<tr>
				<th scope="row">
				<label for="adb_settings[api2_key]"><?php _e('API-nøgle', 'adb_domain'); ?></label></th>
				<td><input id="adb_settings[api2_key]" name="adb_settings[api2_key]" type="text" value="<?php echo $adb_options['api2_key']; ?>" class="regular-text" /> 
				<p class="description" id="api2_key-description">Indtast en gyldig API-nøgle.</p></td>
			</tr>
			<tr>
			<?php
				if ($adb_arkivnavn != "Ukendt")
				{
					?>
					<th scope="row">
					<label for="adb_settings[arkivnavn2]"><?php _e('Arkivnavn', 'adb_domain'); ?></label></th>
					<td>
					<?php echo $adb_arkivnavn2; ?></td>
					<?php
				}
				else
				{
					?>
					<th scope="row">
					<label for="adb_settings[arkivnavn]"><?php _e('Arkivnavn', 'adb_domain'); ?></label></th>
					<td>
						<!--<input id="adb_settings[arkivnavn]" name="adb_settings[arkivnavn] "type="hidden" value="<?php //echo $adb_arkivnavn; ?>" class="regular-text" /> -->
					<em><?php echo $adb_arkivnavn; ?></em></td>
					<?php
				}
			?>
			</tr>
			<tr><th colspan="2"><h3><?php _e('Feltvisning ved søgeresultater', 'adb_domain'); ?></h3><p class="description">Marker de felter der skal vises i oversigten over søgeresultater. Angiv også hvilket felt der skal anvendes til sortering første gang der vises et resultat.</p></th></tr>

			<tr>
				<th scope="row">
				<input id="adb_settings[sortering]" name="adb_settings[sortering]" type="radio" value="billede" class="regular-radio" disabled="true">
				<label for="adb_settings[resultatvisning][billede]"><?php _e('Billede', 'adb_domain'); ?></label></th>
				<td><input id="adb_settings[resultatvisning][billede]" name="adb_settings[resultatvisning][billede]" type="checkbox" value="1" <?php if($adb_options['resultatvisning']['billede']=="1"){print(" checked");}?>  class="regular-checkbox"/> 
				<span class="description">Billede (thumpnail) vises (hvis der er et)</span></td>
			</tr>
			<tr>
				<th scope="row">
				<input id="adb_settings[sortering]" name="adb_settings[sortering]" type="radio" value="titel" <?php if($adb_options['sortering']=="titel"){print(" checked");}?>  class="regular-radio"/>
				<label for="adb_settings[resultatvisning][titel]"><?php _e('Titel/overskrift', 'adb_domain'); ?></label></th>
				<td><input id="adb_settings[resultatvisning][titel]" name="adb_settings[resultatvisning][titel]" type="checkbox" value="1" checked disabled="true" class="regular-checkbox"/> 
				<span class="description">Titel/overskrift (kan ikke fravælges)</span></td>
			</tr>
			<tr>
				<th scope="row">
				<input id="adb_settings[sortering]" name="adb_settings[sortering]" type="radio" value="periode" <?php if($adb_options['sortering']=="periode"){print(" checked");}?>  class="regular-radio"/>
				<label for="adb_settings[resultatvisning][periode]"><?php _e('Periode', 'adb_domain'); ?></label></th>
				<td><input id="adb_settings[resultatvisning][periode]" name="adb_settings[resultatvisning][periode]" type="checkbox" value="1" <?php if($adb_options['resultatvisning']['periode']=="1"){print(" checked");}?>  class="regular-checkbox"/> 
				<span class="description">Periode (til og fra) der er registreret.</span></td>
			</tr>
			<tr>
				<th scope="row">
				<input id="adb_settings[sortering]" name="adb_settings[sortering]" type="radio" value="arkiv" <?php if($adb_options['sortering']=="arkiv"){print(" checked");}?>  class="regular-radio"/>
				<label for="adb_settings[resultatvisning][arkiv]"><?php _e('Arkiv', 'adb_domain'); ?></label></th>
				<td><input id="adb_settings[resultatvisning][arkiv]" name="adb_settings[resultatvisning][arkiv]" type="checkbox" value="1" <?php if($adb_options['resultatvisning']['arkiv']=="1"){print(" checked");}?>  class="regular-checkbox"/> 
				<span class="description">Navn på arkivet</span></td>
			</tr>
			<tr>
				<th scope="row">
				<input id="adb_settings[sortering]" name="adb_settings[sortering]" type="radio" value="materialetype" <?php if($adb_options['sortering']=="materialetype"){print(" checked");}?>  class="regular-radio"/>
				<label for="adb_settings[resultatvisning][materialetype]"><?php _e('Materialetype', 'adb_domain'); ?></label></th>
				<td><input id="adb_settings[resultatvisning][materialetype]" name="adb_settings[resultatvisning][materialetype]" type="checkbox" value="1" <?php if($adb_options['resultatvisning']['materialetype']=="1"){print(" checked");}?>  class="regular-checkbox"/> 
				<span class="description">Materialetype</span></td>
			</tr>
			<tr>
				<th scope="row">
				<input id="adb_settings[sortering]" name="adb_settings[sortering]" type="radio" value="placering" <?php if($adb_options['sortering']=="placering"){print(" checked");}?>  class="regular-radio"/>
				<label for="adb_settings[resultatvisning][placering]"><?php _e('Placering', 'adb_domain'); ?></label></th>
				<td><input id="adb_settings[resultatvisning][placering]" name="adb_settings[resultatvisning][placering]" type="checkbox" value="1" <?php if($adb_options['resultatvisning']['placering']=="1"){print(" checked");}?>  class="regular-checkbox"/> 
				<span class="description">Placering i arkivet</span></td>
			</tr>
			<tr>
				<th scope="row">
				<input id="adb_settings[sortering]" name="adb_settings[sortering]" type="radio" value="registreringsdato" <?php if($adb_options['sortering']=="registreringsdato"){print(" checked");}?>  class="regular-radio"/>
				<label for="adb_settings[resultatvisning][registreringsdato]"><?php _e('Registreringsdato', 'adb_domain'); ?></label></th>
				<td><input id="adb_settings[resultatvisning][registreringsdato]" name="adb_settings[resultatvisning][registreringsdato]" type="checkbox" value="1" <?php if($adb_options['resultatvisning']['registreringsdato']=="1"){print(" checked");}?>  class="regular-checkbox"/> 
				<span class="description">Registreringsdato</span></td>
			</tr>
			<tr><th colspan="2"><h3><?php _e('Øvrige indstillinger', 'adb_domain'); ?></h3></th></tr>
			<tr>
				<th scope="row">
				<label for="adb_settings[visnavn]"><?php _e('Vis arkivnavn', 'adb_domain'); ?></label></th>
				<td><input id="adb_settings[visnavn]" name="adb_settings[visnavn]" type="checkbox" value="1" <?php if($adb_options['visnavn']=="1"){print(" checked");}?>  class="regular-checkbox"/> 
				<span class="description">Sæt kryds hvis arkivnavnet skal vises ved søgeboksen.</span></td>
			</tr>
			<tr>
				<th scope="row">
				<label for="adb_settings[sogplacering]"><?php _e('Placering af søgeboks', 'adb_domain'); ?></label></th>
				<td>
					<input id="adb_settings[sogplacering]" name="adb_settings[sogplacering]" type="radio" value="1" <?php if($adb_options['sogplacering']=="1"){print(" checked");}?>  class="regular-radio"/><span class="description">Under søgeresultatet</span></br>
					<input id="adb_settings[sogplacering]" name="adb_settings[sogplacering]" type="radio" value="2" <?php if($adb_options['sogplacering']=="2"){print(" checked");}?>  class="regular-radio"/><span class="description">Over søgeresultatet</span></br>
					<input id="adb_settings[sogplacering]" name="adb_settings[sogplacering]" type="radio" value="3" <?php if($adb_options['sogplacering']=="3"){print(" checked");}?>  class="regular-radio"/><span class="description">Både over og under søgeresultatet</span></br>
				</td>
			</tr>

			<tr>
				<th scope="row">
				<label for="adb_settings[stylesheet]"><?php _e('Vælg stylesheet (udseende)', 'adb_domain'); ?></label></th>
				<td>
					<input id="adb_settings[stylesheet]" name="adb_settings[stylesheet]" type="radio" value="standard" <?php if($adb_options['stylesheet']=="standard"){print(" checked");}?>  class="regular-radio"/><span class="description">Standard</span></br>
					<input id="adb_settings[stylesheet]" name="adb_settings[stylesheet]" type="radio" value="valby" <?php if($adb_options['stylesheet']=="valby"){print(" checked");}?>  class="regular-radio"/><span class="description">Valby</span></br>
				</td>
			</tr>

			<tr>
				<th scope="row">
				<label for="adb_settings[featherlight]"><?php _e('Visning af billeder', 'adb_domain'); ?></label></th>
				<td>
					<input id="adb_settings[stylesheet]" name="adb_settings[featherlight]" type="radio" value="featherlight" <?php if($adb_options['featherlight']=="featherlight"){print(" checked");}?>  class="regular-radio"/><span class="description">Brug indbygget (featherlight)</span></br>
					<input id="adb_settings[stylesheet]" name="adb_settings[featherlight]" type="radio" value="ekstern" <?php if($adb_options['featherlight']=="ekstern"){print(" checked");}?>  class="regular-radio"/><span class="description">Brug eksterne plugins (via wordpress)</span></br>
				</td>
			</tr>

			<tr>
				<th scope="row">
				<label for="adb_settings[debug]"><?php _e('Debugbeskeder', 'adb_domain'); ?></label></th>
				<td>
					<input id="adb_settings[debug]" name="adb_settings[debug]" type="radio" value="1" <?php if($adb_options['debug']=="1"){print(" checked");}?>  class="regular-radio"/><span class="description">Debugbeskeder vises</span></br>
					<input id="adb_settings[debug]" name="adb_settings[debug]" type="radio" value="0" <?php if($adb_options['debug']=="0"){print(" checked");}?>  class="regular-radio"/><span class="description">Debugbeskeder vises ikke</span></br>
				</td>
			</tr>

			</table>
			<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Gem ændringer', 'adb_domain'); ?>" /></p>

			<h3>Opsætning</h3>
			<p>For at få vist <?php echo $adb_plugin_name; ?>, skal du indsætte følgende shortcode i tekstindholdsfeltet på den side, du gerne vil bruge til at få vist oplysningerne:</p>
			<p><code><?php echo $adb_shortcode; ?></code></p>
			<p>Du skal kopiere hele shortcoden inkl. de firkantede parenteser!</p>
		
		</form>
		
	</div>
	<?php
	echo ob_get_clean();
}


function adb_add_options_link() 
{
	add_options_page('Indstillinger for Arkivdatabasen', 'Arkivdatabasen', 'manage_options', 'adb-options', 'adb_options_page');
}
add_action('admin_menu', 'adb_add_options_link');

function adb_register_settings() 
{
	register_setting('adb_settings_group', 'adb_settings');
}
add_action('admin_init', 'adb_register_settings');


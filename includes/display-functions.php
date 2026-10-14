<?php

// Our display functions for outputting information

// Create security string to add the search url
function adb_security($arkiv=1) {
	global $adb_options;

	$timestamp = gmdate('U');
	if($arkiv==1){
		$signatur = md5($adb_options['api_key'] . $timestamp);
	}
	elseif($arkiv==2){
		$signatur = md5($adb_options['api2_key'] . $timestamp);
	}

	$security_code = 'timestamp='.$timestamp.'&signatur='.$signatur;

	return $security_code;
}

// Checks the API
function adb_security_check($arkiv=1)
{
	global $adb_options;

	$security_string = adb_security($arkiv);

	if($arkiv==1){
		$check_url = $adb_options['database_url'].'?'.$security_string.'&action=get_check';
	}
	elseif($arkiv==2){
		$check_url = $adb_options['database2_url'].'?'.$security_string.'&action=get_check';
	}

	$request = wp_remote_get( $check_url );
	if( is_wp_error( $request ) ){
		return false;
	}
	$body = wp_remote_retrieve_body( $request );
	$body = utf8_encode($body);
	$data = json_decode($body,true);
	return $data;
}

function adb_security_checkurl($arkiv=1)
{
	global $adb_options;

	$security_string = adb_security($arkiv);

	if($arkiv==1){
		$check_url = $adb_options['database_url'].'?'.$security_string.'&action=get_check';
	}
	elseif($arkiv==2){
		$check_url = $adb_options['database2_url'].'?'.$security_string.'&action=get_check';
	}

	return $check_url;
}

// Main functionality build in a single short code
function adb_shortcode( $atts, $content = NULL ) 
{
	global $adb_options;
	global $adb_plugin_name;

	$security_string = adb_security();
	$base_url = $adb_options['database_url'].'?'.$security_string;
	
	// Check the API-key and give a message
	$security_check = adb_security_check();
	$adb_arkivnavn = $security_check['arkivnavn'];


	if ($security_check)
	{
		if (!$security_check['error'])
		{
			// Check om der er et sekundært arkiv og alt er i orden (hvis det ikke er i orden lader vi bare som om der IKKE er et sekundært arkiv)
			$secondarchive_check = adb_security_check(2);
			$secondarchive = 0;
			$usesecond = 0;
				if($secondarchive_check){
					if(!$secondarchive_check['error']){
						$secondarchive = 1;
						$adb_arkivnavn2 = $secondarchive_check['arkivnavn'];
						if (isset($_POST['usesecond']))
						{
							$usesecond = $_POST['usesecond'];
						}
						else
						{
							if(isset($_GET['usesecond']))
								{
								$usesecond = $_GET['usesecond'];
								}
						}
						if($usesecond){
							// Sekundært arkiv aktivt - override!
								$security_string = adb_security(2);
								$base_url = $adb_options['database2_url'].'?'.$security_string;
								$adb_arkivnavn = $adb_arkivnavn2;
						}				
					}
				}

			if(array_key_exists("visnavn", $adb_options)){
				if($adb_options['visnavn'])
				{
					$content .= '<h2>Søg i '.$adb_arkivnavn.'</h2>';
				}
			}
			if($usesecond)
			{
				$content .= '<p><strong>Der søges i alternativ samling!</strong></p>';
			}
			if (isset($_GET['action']))
			{
				$action = $_GET['action'];
			}
			else
			{
				$action = FALSE;
			}
			// Show this when clicking on a search result
			if ($action == 'get_details' && isset($_GET['id']))
			{

				if (isset($_GET["sortering"])){
					$this_sortering = $_GET["sortering"];
				}
				else{
					$this_sortering = $adb_options['sortering'];
				}


				$search_url = $base_url.'&action=get_details&id='.htmlspecialchars($_GET['id']).'&arkivnummer='.htmlspecialchars($_GET['arkivnummer']);
				$request = wp_remote_get( $search_url );

				if( is_wp_error( $request ) ) 
				{
					return false; 
				}

				$body = wp_remote_retrieve_body( $request );
				$body = utf8_encode($body);
				$data = json_decode( $body, true );

				if( ! empty( $data ) ) 
				{
					if ( isset($data['error']) && $data['error']==true)
					{
						$content .= '<p><strong>'.$data['errormessage'].'</p></strong>';
					}
					else 
					{
						$content .= '<h3>'.$data['Titel'].'</h3>';
						if($data['Titel'] != $data['Forklaring']){
							$content .= '<p>'.$data['Forklaring'].'</p>';
						}
						$content .= '<div class="adb_img_container">';
						if (!empty($data['Billeder']))
						{
							if($adb_options['featherlight']=="featherlight")
								{
								$content .= '<style>@import url("//cdn.rawgit.com/noelboss/featherlight/1.3.2/release/featherlight.min.css");</style>';
								$content .= '<script src="//code.jquery.com/jquery-latest.js"></script>';
								$content .= '<script src="//cdn.rawgit.com/noelboss/featherlight/1.3.2/release/featherlight.min.js" type="text/javascript" charset="utf-8"></script>';
								}
							foreach( $data['Billeder'] as $image )
							{
								if (is_array($image))
								{
									if($adb_options['featherlight']=="featherlight")
									{
									$content .= '<div class="adb_img_large"><a href="#" data-featherlight="'.$image['url'].'"><img src='.$image['thumpnail'].' /></a></div>';
									}
									else
									{
									$content .= '<div class="adb_img_large"><a href="'.$image['url'].'"><img src='.$image['thumpnail'].' /></a></div>';
									}
								}
								else
								{
									$content .= '';
								}
							}
						}
						else
						{
							$content .= '';
						}
						$content .= '</div>';

						$content .= '<h4>Standardinformation</h4>';
						$content .= '<table class=\"wp-list-table widefat fixed posts\">';
						$content .= '<tr><td>Registreringsnummer</td><td>'.$data['Registreringsnummer'].'</td></tr>';
						$content .= '<tr><td>Registreringsdato</td><td>'.$data['Registreringsdato'].'</td></tr>';
						$content .= '<tr><td>Tidsperiode</td><td>';
						if ($data['periode_start']==$data['periode_slut']){
							$content .= $data['periode_start'];
						}
						else{
							$content .= $data['periode_start'].' - '.$data['periode_slut'];
						}
						$content .= '</td></tr>';
						$content .= '<tr><td>Placering</td><td>'.$data['Placering'].'</td></tr>';
						$content .= '<tr><td>Arkiv</td><td><a href='.$data['Hjemmeside'].' target=_blank>'.$data['Arkiv'].'</a></td></tr>';
						// $content .= '<tr><td>Arkivdatabase</td><td><a href='.$data['Arkivdatabase'].' target=_blank>Arkivdatabase</a></td></tr>';
						$content .= '</table>';

						
						if (!empty($data['Filer']))
						{
							$content .= '<h4>Filer</h4>';
							$content .= '<ul>';
							foreach( $data['Filer'] as $file )
							{
								if (is_array($file))
								{
									$content .= '<li><a href='.$file['url'].' target=_new>'.$file['filnavn'].'</a></li>';
								}
								else
								{
									$content .= '';
								}
							}
							$content .= '</ul>';
						}
						else
						{
							$content .= '';
						}

						$content .= '<h4>'.$data['Registreringstype'].'</h4>';
						$content .= '<table>';
						if (!empty($data['Registreringstypeindhold']))
						{
							foreach( $data['Registreringstypeindhold'] as $type => $typeinfo )
							{
								if($typeinfo)
								{
									$content .= '<tr><td>'.$type.'</td><td>'.$typeinfo.'</td></tr>';
								}
							}
						}
						else
						{
							$content .= '<li>Der er ikke yderligere information om indholdet.</li>';
						}
						$content .= '</table>';

						if (!empty($data['Links']))
						{
							$content .= '<h4>Links (henvisninger)</h4>';
							$content .= '<ul>';
							foreach( $data['Links'] as $link )
							{
								if (is_array($link))
								{
									$content .= '<li><a href='.$link['url'].' target=_new>'.$link['titel'].'</a></li>';
								}
								else
								{
									$content .= '';
								}
							}
							$content .= '</ul>';
						}
						else
						{
							$content .= '';
						}


						if ($data['Emneord'][0]!="")
						{
							$form_url = esc_url( $_SERVER["REDIRECT_URL"] ) .'?action=get_search';

							$content .= '<h4>Emneord</h4>';
							$content .= '<ul>';


							$content .= '<form name="searchform" action='.$form_url.' method="post">';
							$content .= '<input type="hidden" name="string" value="">';
							$content .= '<input type="hidden" id="sortering" name="sortering" value="'.$this_sortering.'">';
							$content .= '<input type="hidden" name="usesecond" value="'.$usesecond.'">';
							$content .= '<input type="hidden" name="method" value="emneord">';
							$content .= '</form>';


							$content .= '<script>';
							$content .= 'function submitsearch(string){';
							$content .= '	document.searchform.string.value = string;';
							$content .= '	document.searchform.submit();';
							$content .= '}';
							$content .= '</script>';

							foreach( $data['Emneord'] as $tag )
							{
								$content .= '<li><a href="#" onClick="submitsearch(\''.$tag.'\')" >'.$tag.'</a></li>';

							}
						$content .= '</ul>';
						}
						else
						{
							$content .= '';
						}
						$content .= '<p><input type="button" value="Tilbage" onClick="window.history.back();"></p></td>';						
					}
				} 
			}
			// Show this when database page is viewed first time and at search result
			else
			{
			$method = 'string';
				// Show this after search
				$form_url = esc_url( $_SERVER["REDIRECT_URL"] ) .'?action=get_search';

				$content .= '<script>';
				$content .= 'function checksearch(){';
				$content .= ' if(document.searchform.string.value!=""){';
				$content .= '	 return true;';
				$content .= '	 }';
				$content .= '	else {'; 
				$content .= '	 alert("En søgning kan ikke være tom");';
				$content .= '	 return false;';
				$content .= '	 }';
				$content .= '}';
				$content .= '</script>';

			if (isset($_POST["string"])){
				$this_sogestreng = $_POST["string"];
			}
			else{
				$this_sogestreng = "";
			}

			if (isset($_POST["sortering"])){
				$this_sortering = $_POST["sortering"];
			}
			else{
				$this_sortering = $adb_options['sortering'];
			}


				if ($action == 'get_search' && isset($_POST['string']))
				{
					if (isset($_POST['method']) )
					{
						$method = $_POST['method'];
					}
					$search_url = $base_url.'&action=get_search&string='.urlencode($_POST['string']).'&method='.$method;
					$timeout = 120;
					$request = wp_remote_get( $search_url, array( 'timeout' => $timeout ));
					$response_code = wp_remote_retrieve_response_code( $request );

					if( is_wp_error( $request ) ) 
					{
						$content .= "<p><strong>WP fejlkode: ".$response_code."</strong></p>"; 
					}

					if( $response_code != 200  ) 
					{
						$content .= '<p><strong>Databasen svarer ikke!</strong> Prøv igen. Hvis problemet gentager sig kan der være problemer med databasen eller den opsatte forbindelse.</p>';
					}
					else
					{
						$body = wp_remote_retrieve_body( $request );
						$body = utf8_encode($body);
						$data = json_decode( $body, true );

						if( ! empty( $data ) ) 
						{
							if ( isset($data['error']) && $data['error']==true)
							{
								$content .= '<p><strong>'.$data['errormessage'].'!</strong> Kontroller indstillingerne om du har angivet den korrekt API-nøgle.</p>';
							}
							else 
							{
								$content .= '<p>Søgningen gav <strong>'.count($data).'</strong> resultater.</p>';

								$content .= '<script src="../wp-content/plugins/arkivdatabasen/includes/sorttable.js"></script>';
								$content .= '<style>';
								$content .= '/* Sortable tables */';
								$content .= 'table.sortable thead {';
								$content .= 'font-weight: bold;';
								$content .= 'cursor: hand;';
								$content .= '}';
								$content .= 'table.sortable th:not(.sorttable_sorted):not(.sorttable_sorted_reverse):not(.sorttable_nosort):after { ';
								$content .= 'content: " \25B4\25BE" ';
								$content .= '}';
								$content .= 'table.sortable tbody tr:nth-child(2n) td {';
 								$content .= '';
								$content .= '}';
								$content .= 'table.sortable tbody tr:nth-child(2n+1) td {';
								$content .= '';
								$content .= '}';
								$content .= '/* Sortable tables */';
								$content .= '</style>';
								
								if(($adb_options['sogplacering']=="2")||($adb_options['sogplacering']=="3")){
									$content .= '<form name="searchform" action="'.$form_url.'" method="post">';
									$content .= ' <input type="hidden" name="sortering" id="sortering" value="'.$this_sortering.'">';
									$content .= '<p><input type="text" name="string" size="40" value="'.$this_sogestreng.'">';
									$content .= ' <input type="submit" value="Søg" onClick="return checksearch()"></p>';
									$content .= '<input type="radio" name="method" ';
									if (isset($method) && $method=="string") 
									{ 
										$content .= 'checked ';
									}
									$content .= 'value="string"> Som sætning<br/>';
									$content .= '<input type="radio" name="method" ';
									if (isset($method) && $method=="and") 
									{ 
										$content .= 'checked ';
									}
									$content .= 'value="and"> Alle ordene<br/>';
									$content .= '<input type="radio" name="method" ';
									if (isset($method) && $method=="or") 
									{ 
										$content .= 'checked ';
									}
									$content .= 'value="or"> Mindst et af ordene<br/>';
									$content .= '<input type="radio" name="method" ';
									if (isset($method) && $method=="emneord") 
									{ 
										$content .= 'checked ';
									}
									$content .= 'value="emneord"> Som emneord<br/><br/>';
									if($secondarchive)
									{
										$content .= '<input type="checkbox" name="usesecond" ';
										if (isset($usesecond) && $usesecond=="1") 
										{ 
											$content .= 'checked ';
										}
										$content .= 'value="1"> Søg i alternativ samling: '.$adb_arkivnavn2.'<br/>';
									}
									$content .= '</form><p>&nbsp;</p>';
								}

 								$content .= '<table class="sortable">';
 								$content .= '<thead>';
 								$content .= '<tr>';
 								if($adb_options['resultatvisning']['billede']=="1"){$content .= '<th class="sorttable_nosort"></th>';}
 								$content .= '<th id="titel" onClick="document.getElementById(\'sortering\').value = \'titel\'">Titel</th>';
 								if($adb_options['resultatvisning']['periode']=="1"){$content .= '<th class="sortable_alpha" id="periode" onClick="document.getElementById(\'sortering\').value = \'periode\'">Periode</th>';}
 								if($adb_options['resultatvisning']['arkiv']=="1"){$content .= '<th id="arkiv" onClick="document.getElementById(\'sortering\').value = \'arkiv\'">Arkiv</th>';}
 								if($adb_options['resultatvisning']['materialetype']=="1"){$content .= '<th id="materialetype" onClick="document.getElementById(\'sortering\').value = \'materialetype\'">Materialetype</th>';}
 								if($adb_options['resultatvisning']['placering']=="1"){$content .= '<th id="placering" onClick="document.getElementById(\'sortering\').value = \'placering\'">Placering</th>';}
 								if($adb_options['resultatvisning']['registreringsdato']=="1"){$content .= '<th id="registreringsdato" onClick="document.getElementById(\'sortering\').value = \'registreringsdato\'">Reg.dato</th>';}
 								$content .= '</tr>';
 								$content .= '</thead>';
								$content .= '<tbody>';
								foreach( $data as $value ) 
								{
									$content .= '<tr>';
									if (empty($value['thumbnail'])) 
									{
										$thumbnail = plugins_url( 'images/noimage.gif', dirname(__FILE__) );
									}
									else
									{
										$thumbnail = $value['thumbnail'];
									}
									if($adb_options['resultatvisning']['billede']=="1"){$content .= '<td align="center"><a href='.$_SERVER["REDIRECT_URL"].'?action=get_details&id='.$value['id'].'&sortering='.$this_sortering.'&usesecond='.$usesecond.'&arkivnummer='.$value['arkivnummer'].'><img src='.$thumbnail.' alt='.$value['navn'].' class="adb_thumbnail" /></a></td>';}
									$content .= '<td><a href='.$_SERVER["REDIRECT_URL"].'?action=get_details&id='.$value['id'].'&sortering='.$this_sortering.'&usesecond='.$usesecond.'&arkivnummer='.$value['arkivnummer'].'>'.$value['navn'].'</a></td>';
									if($adb_options['resultatvisning']['periode']=="1"){
										// reducer dato til årstal
										$value['periode_start']=substr($value['periode_start'], 6, 4);
										$value['periode_slut']=substr($value['periode_slut'], 6, 4);
										if ($value['periode_start']==$value['periode_slut']){
											$content .= '<td>'.$value['periode_start'].'</td>';
										}
										else{
											$content .= '<td>'.$value['periode_start'].' - '.$value['periode_slut'].'</td>';
										}
									}
									if($adb_options['resultatvisning']['arkiv']=="1"){$content .= '<td>'.$value['arkiv'].'</td>';}
									if($adb_options['resultatvisning']['materialetype']=="1"){$content .= '<td>'.$value['materialetype'].'</td>';}
									if($adb_options['resultatvisning']['placering']=="1"){$content .= '<td>'.$value['placering'].'</td>';}
									if($adb_options['resultatvisning']['registreringsdato']=="1"){$content .= '<td>'.$value['dato'].'</td>';}
									$content .= '</tr>';
								}
								$content .= '</tbody>';
								$content .= '</table>';
						
								$content .= '<script>';
								$content .= '	document.addEventListener("DOMContentLoaded", () => {';
								$content .= '		var startsortering = "'.$this_sortering.'";';
								$content .= '		sorttable.innerSortFunction.apply(document.getElementById(startsortering), []);';
								$content .= '	})';
								$content .= '</script>';

							}
						} 
						else 
						{
							$content .= '<p>Søgningen gav <strong>ingen</strong> resultater. Prøv at udvide din søgning!</p>';

								if($adb_options['sogplacering']=="2"){
									$content .= '<form name="searchform" action="'.$form_url.'" method="post">';
									$content .= ' <input type="hidden" name="sortering" id="sortering" value="'.$this_sortering.'">';
									$content .= '<p><input type="text" name="string" size="40" value="'.$this_sogestreng.'">';
									$content .= ' <input type="submit" value="Søg" onClick="return checksearch()"></p>';
									$content .= '<input type="radio" name="method" ';
									if (isset($method) && $method=="string") 
									{ 
										$content .= 'checked ';
									}
									$content .= 'value="string"> Som sætning<br/>';
									$content .= '<input type="radio" name="method" ';
									if (isset($method) && $method=="and") 
									{ 
										$content .= 'checked ';
									}
									$content .= 'value="and"> Alle ordene<br/>';
									$content .= '<input type="radio" name="method" ';
									if (isset($method) && $method=="or") 
									{ 
										$content .= 'checked ';
									}
									$content .= 'value="or"> Mindst et af ordene<br/>';
									$content .= '<input type="radio" name="method" ';
									if (isset($method) && $method=="emneord") 
									{ 
										$content .= 'checked ';
									}
									$content .= 'value="emneord"> Som emneord<br/><br/>';
									if($secondarchive)
									{
										$content .= '<input type="checkbox" name="usesecond" ';
										if (isset($usesecond) && $usesecond=="1") 
										{ 
											$content .= 'checked ';
										}
										$content .= 'value="1"> Søg i alternativ samling: '.$adb_arkivnavn2.'<br/>';
									}
									$content .= '</form><p>&nbsp;</p>';
								}

						}
					}
				}
				else{
					if($adb_options['sogplacering']=="2"){
						$content .= '<form name="searchform" action="'.$form_url.'" method="post">';
						$content .= ' <input type="hidden" name="sortering" id="sortering" value="'.$this_sortering.'">';
						$content .= '<p><input type="text" name="string" size="40" value="'.$this_sogestreng.'">';
						$content .= ' <input type="submit" value="Søg" onClick="return checksearch()"></p>';
						$content .= '<input type="radio" name="method" ';
						if (isset($method) && $method=="string") 
						{ 
							$content .= 'checked ';
						}
						$content .= 'value="string"> Som sætning<br/>';
						$content .= '<input type="radio" name="method" ';
						if (isset($method) && $method=="and") 
						{ 
							$content .= 'checked ';
						}
						$content .= 'value="and"> Alle ordene<br/>';
						$content .= '<input type="radio" name="method" ';
						if (isset($method) && $method=="or") 
						{ 
							$content .= 'checked ';
						}
						$content .= 'value="or"> Mindst et af ordene<br/>';
						$content .= '<input type="radio" name="method" ';
						if (isset($method) && $method=="emneord") 
						{ 
							$content .= 'checked ';
						}
						$content .= 'value="emneord"> Som emneord<br/><br/>';
						if($secondarchive)
						{
							$content .= '<input type="checkbox" name="usesecond" ';
							if (isset($usesecond) && $usesecond=="1") 
							{ 
								$content .= 'checked ';
							}
							$content .= 'value="1"> Søg i alternativ samling: '.$adb_arkivnavn2.'<br/>';
						}
						$content .= '</form>';
					}
				}

				if(($adb_options['sogplacering']=="1")||($adb_options['sogplacering']=="3")){
					$content .= '<form name="searchform" action="'.$form_url.'" method="post">';
					$content .= ' <input type="hidden" name="sortering" id="sortering" value="'.$this_sortering.'">';
					$content .= '<p><input type="text" name="string" size="40" value="'.$this_sogestreng.'">';
					$content .= ' <input type="submit" value="Søg" onClick="return checksearch()"></p>';
					$content .= '<input type="radio" name="method" ';
					if (isset($method) && $method=="string") 
					{ 
						$content .= 'checked ';
					}
					$content .= 'value="string"> Som sætning<br/>';
					$content .= '<input type="radio" name="method" ';
					if (isset($method) && $method=="and") 
					{ 
						$content .= 'checked ';
					}
					$content .= 'value="and"> Alle ordene<br/>';
					$content .= '<input type="radio" name="method" ';
					if (isset($method) && $method=="or") 
					{ 
						$content .= 'checked ';
					}
					$content .= 'value="or"> Mindst et af ordene<br/>';
					$content .= '<input type="radio" name="method" ';
					if (isset($method) && $method=="emneord") 
					{ 
						$content .= 'checked ';
					}
					$content .= 'value="emneord"> Som emneord<br/><br/>';
					if($secondarchive)
					{
						$content .= '<input type="checkbox" name="usesecond" ';
						if (isset($usesecond) && $usesecond=="1") 
						{ 
							$content .= 'checked ';
						}
						$content .= 'value="1"> Søg i alternativ samling: '.$adb_arkivnavn2.'<br/>';
					}
					$content .= '</form>';
				}


			}
		}
		else
		{
		// GIV GENERISK FEJLBESKED
			$content .= 'Beklager, men arkivet ser ikke ud til at være korrekt opsat.';
		}
	}
	else
	{
	// GIV GENERISK FEJLBESKED
		$content .= 'Beklager, men der er ikke forbindelse til databasen (opsætningen er muligvis forkert).';
	}
	return $content;
}
add_shortcode( 'adb', 'adb_shortcode' );
<?php

header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json; charset=UTF-8"); 
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



if ($_SERVER['REQUEST_METHOD']) { 

	if (($_SERVER['REQUEST_METHOD']) == 'GET' ) {
		
		if ( md5(md5($_GET["key"])) == "44fa9c5d0b724491d69b7d50b8bb3cdd" ) { //sistema di sicurezza md5
			// Object Oriented connection 
			$host = "localhost";     
			$user = "root";          
			$password = "";
			$dbname = "agenzia";
			$conn = new mysqli("$host","$user","$password","$dbname");
			
			if ($conn -> connect_errno) { //se la connessione al DB riporta un errore
			  echo "Impossibile connettersi a MySQL: " . $conn -> connect_error; //comunico l'errore
			  exit(); //termino l'esecuzione dello script
			}	
				
				
				
			if ( isset($_GET["journey"]) || isset($_GET["Journey"]) ) {			
				if ( isset($_GET["journey"]) && ($_GET["journey"] != NULL)  ) {
					$journey = $conn -> real_escape_string(htmlspecialchars(strip_tags($_GET["journey"])));
				}
				if ( isset($_GET["Journey"]) && ($_GET["Journey"] != NULL)  ) {
					$journey = $conn -> real_escape_string(htmlspecialchars(strip_tags($_GET["Journey"])));
				}
				$journey = strtolower($journey);
				
				
				
				
				//inizio acquisizione POSTI del viaggio
				if ( isset($_GET["seats"]) && ($_GET["seats"] != NULL)  ) {
					$seat = $conn -> real_escape_string(htmlspecialchars(strip_tags($_GET["seats"])));
				}
				if ( isset($_GET["Seats"]) && ($_GET["Seats"] != NULL)  ) {
					$seat = $conn -> real_escape_string(htmlspecialchars(strip_tags($_GET["Seats"])));
				}
				$seat = strtolower($seat);
				//fine acquisizione POSTI del viaggio
				
				
				//inizio acquisizione PAESI del viaggio
				if ( isset($_GET["countries"]) && ($_GET["countries"] != NULL)  ) {
					$countries = $conn -> real_escape_string(htmlspecialchars(strip_tags($_GET["countries"])));
				}
				if ( isset($_GET["Countries"]) && ($_GET["Countries"] != NULL)  ) {
					$countries = $conn -> real_escape_string(htmlspecialchars(strip_tags($_GET["Countries"])));
				}
				$countries = strtolower($countries);
				//fine acquisizione PAESI del viaggio
				
				
				/* inizio acquisizione OPERAZIONE DA ESEGUIRE sul viaggio: 
				Operation 1: insert a new journey
				Operation 2: modify a journey
				Operation 3: delete a journey
				Operation 4: show a journey

				*/
				
				if ( isset($_GET["operation"]) && ($_GET["operation"] != NULL)  ) { 
					if ($_GET["operation"] == 1 ) { $operation = "insertJourney"; }
					if ($_GET["operation"] == 2 ) { 
						if ( isset($_GET["journeyModified"]) && ($_GET["journeyModified"] != NULL)  ) { 
							$journeyModified = $conn -> real_escape_string(htmlspecialchars(strip_tags($_GET["journeyModified"])));
							$journeyModified = strtolower($journeyModified);
						}
						else {
							//notifica errore del nuovo nome paese da aggiornare
							$responseArray = array("status"=>"400");
							$responseArray["note"] = "There was a problem to modify the name of the country";
						}
					$operation = "modifyJourney"; }
					if ($_GET["operation"] == 3 ) { $operation = "deleteJourney"; }
					if ($_GET["operation"] == 4 ) { $operation = "showJourney"; }
				}
				//fine acquisizione OPERAZIONE DA ESEGUIRE sul viaggio
				
				
				
				
				//inizio INSERIMENTO nuovo viaggio
				if ($operation == "insertJourney") {
					
					//stringa di esempio: http://localhost/index.php?key=Orizon&journey=10&operation=1&seat=45&countries=italia,germania
					//id viaggio non necessario (nel DB "journey" è un int autoincrement)
					
					//conversione da stringa ad array dei paesi immessi
					$arraypaesi = explode(",", $countries);
					$var1 = 0;
					$paesi = array();
					$query = "SELECT * FROM countries";
					$response = $conn->query($query);
					for($i = 0; $i <= count($arraypaesi)-1; $i++) {
						$var2 = 0;
						//ciclaggio record del db - tabella Countries
						$query = "SELECT * FROM countries";
						$response = $conn->query($query);
						while ( $record = $response->fetch_assoc() ) {
							//ciclaggio dei paesi immessi dall'utente
							if (strtolower($record["country"]) == strtolower($arraypaesi[$i])) {
								$paesi[] = $record["id"]; //sostituzione nome paese con id paese già memorizzato nel db
								//se il paese del record identificato è presente nell'array dei paesi immessi
								$var2 = 1;
							}
						}
						
						if ($var2 == 0) { //uno dei paesi immessi non è presente nel db
							$var1 = 1;
						}
					}
					
					if ($var1 == 0) { //tutti i paesi immessi sono presenti nel db						
						$stringapaesi = implode('@', $paesi);
						//procediamo all'inserimento del viaggio nel db
						$query = "INSERT INTO dati (countries,seats) VALUES ('$stringapaesi','$seat')";
						$responseArray["status"] = "200";
						$responseArray["note"] = "Journey has been added";
						
					}
					else {
						$responseArray = array("status"=>"426");
						$query = "SELECT * FROM countries";
						$risp = $conn->query($query);
						$a = 0;
						while ($risposta = $risp->fetch_assoc()) {
							$responseArray["country"."$a"] = $risposta["country"];
							$a++;
						}
						$responseArray["status"] = "400";
						$responseArray["note1"] = "There was a problem to insert the journey. Try to use a string like this:";
						$responseArray["exampleString"] = "http://localhost/index.php?key=Orizon&journey=1&operation=1&seat=45&countries=italia,germania";
						$responseArray["note2"] = "If you want to add a country use a string like this:";
						$responseArray["exampleString"] = "http://localhost/index.php?key=Orizon&country=irland&operation=1";
						//almeno 1 paese immesso non è presente nel db
						//seleziona e stampa paesi presenti nel db e notifica errore di inserimento viaggio
						//suggerendo anche la stringa per poter inserire un paese non presente nel DB
					}
				}
				//fine INSERIMENTO nuovo viaggio
				
				//inizio MODIFICA viaggio
				if ($operation == "modifyJourney") {
					
					//stringa di esempio: http://localhost/index.php?key=Orizon&journey=10&operation=2&seats=45&countries=italia,germania,fran
					
					
					$query = "SELECT * FROM dati WHERE journey ='$journey'";
					$risp = $conn->query($query);
					$responseArray = array();
					$paesiconvertiti = array();
					$idjourney = "";
					while ($risposta = $risp->fetch_assoc()) {
						$idjourney = $risposta['journey'];
						$stringcountries = $risposta['countries'];
						$ex = explode('@',$stringcountries);
						$seats = $risposta['seats'];
						
						$query1 = "SELECT * FROM countries";
						$response = $conn->query($query1);
						
						//inizio verifica paesi dei viaggi
						while ($risposta1 = $response -> fetch_assoc()) {
							
							if (in_array(strtolower($risposta1["id"]), $stringcountries)) {
							//if ( strstr($stringcountries, strtolower($risposta1["id"]) ) ) { 
							//se il campo ID della tabella COUNTRIES è CONTENUTO NELLA STRINGA $stringcountries (campo countries della tabella dati)
								$paesiconvertiti[] = $risposta1["country"];
							}
						} 
						//fine verifica paesi dei viaggi
						$stringa = implode(",",$paesiconvertiti);
						$responseArray['journey'][$idjourney] = array('countries'=>"$stringa",'seats'=>$risposta["seats"]);
					}
					
					
					if 
					(
					(
					( ($_GET['seats'] != NULL) && (isset($_GET['seats'])) ) ||
					( ($_GET['Seats'] != NULL) && (isset($_GET['Seats'])) ) 
					)
					&&
					(
					( ($_GET['countries'] != NULL) && (isset($_GET['countries'])) ) ||
					( ($_GET['Countries'] != NULL) && (isset($_GET['Countries'])) ) 
					)
					)
					//se vengono inseriti ENTRAMBI i parametri countries e seats eseguiamo la modifica del viaggio
					{
						
						
						if ( ($_GET['seats'] != NULL) && (isset($_GET['seats'])) ) {
							$seats = $conn -> real_escape_string(htmlspecialchars(strip_tags($_GET["seats"])));
						}
						if ( ($_GET['Seats'] != NULL) && (isset($_GET['Seats'])) ) {
							$seats = $conn -> real_escape_string(htmlspecialchars(strip_tags($_GET["Seats"])));
						}
						if ( ($_GET['countries'] != NULL) && (isset($_GET['countries'])) ) {
							$countries = $conn -> real_escape_string(htmlspecialchars(strip_tags($_GET["countries"])));
						}		
						if ( ($_GET['Countries'] != NULL) && (isset($_GET['Countries'])) ) {
							$countries = $conn -> real_escape_string(htmlspecialchars(strip_tags($_GET["Countries"])));
						}	
						
						
						$explode = explode(",",$countries);
						$lunghezza = count($explode);
						if ($lunghezza == 0) {
							
							//utilizzato un separatore diverso dal simbolo VIRGOLA
						}
						
						
						$varcontroll = 0;
						for ($i = 0; $i <= $lunghezza -1; $i++) {
							
							$paese = strtolower($explode[$i]);
							//controllare i nomi dei paesi immessi nella stringa $countries
							$query = "SELECT * FROM countries WHERE country = '$paese'";
							$risp = $conn->query($query);
							if ( $risp->fetch_assoc() == 0) {
								$varcontroll = 1;
							}
						}
						
						if ($varcontroll == 1) {//se almeno uno non esiste, notifico l'errore con possibilità di inserimento del paese nella tabella countries
							
							//lettura e stampa dei paesi attualmente inseriti 
							$query = "SELECT * FROM countries";
							$risp = $conn->query($query);
							while ($risposta = $risp->fetch_assoc()) {
								$responseArray["country"."$risposta[id]"] = strtolower($risposta["country"]); 
							}
							$responseArray['status'] = '400';
							$responseArray['note'] = "It's not possible to modify the journey because the country doesn't exist. Use a string like this to insert the country:";
							$responseArray['string'] = "http://localhost/index.php?key=Orizon&country=irland&operation=1";
						
							
							//e stringa da utilizzare per inserire un paese non presente
							
							
							
						}
						else {//se esistono tutti, consento l'aggiornamento
							
							$paesi = "";
							$varcontroll = 0;
							for ($i = 0; $i <= $lunghezza -1; $i++) {
								
								$paese = strtolower($explode[$i]);
								//controllare i nomi dei paesi immessi nella stringa $countries
								$query = "SELECT * FROM countries WHERE country = '$paese'";
								$risp = $conn->query($query);
								$risposta = $risp->fetch_assoc();
								$paesi.= $risposta['id'].'@';
								
							}
							
							
							//verifichiamo se il numero del viaggio è già presente nel DB
							$varcontroll = 0;
							$query = "SELECT * FROM dati WHERE journey='$journey'";
							$risp = $conn->query($query);
							if ( $risp->fetch_assoc() == 0) {
								$varcontroll = 1;
							}
							
							if ($varcontroll == 1) { //se il viaggio non è già presente lo INSERIAMO
								$query = "INSERT INTO dati (journey,countries,seats) VALUES ('$journey','$paesi','$seats')";
								$conn->query($query);
								$responseArray['status'] = '200';
								$responseArray['note'] = 'Journey doesn\'t exist. A new journey has been created';
							}
							
							
							else { //se il viaggio è già presente lo AGGIORNIAMO
								$query = "UPDATE dati SET countries = '$paesi',seats='$seats' WHERE journey = '$journey'";
								$conn->query($query);
								
								$responseArray['journey'][$idjourney] = "";
								print_r($responseArray['journey'][$idjourney]);
								
								
								//inizio caricamento dei nuovi paesi del viaggio $journey nell'array $responseArray che viene convertita in json
								$query = "SELECT * FROM dati WHERE journey ='$journey'";
								$risp = $conn->query($query);
								$responseArray = array();
								$paesiconvertiti = array();
								while ($risposta = $risp->fetch_assoc()) {
									
									$idjourney = $risposta['journey'];
									$stringcountries = $risposta['countries'];
									$ex = explode('@',$stringcountries);
									$seats = $risposta['seats'];
									
									$query1 = "SELECT * FROM countries";
									$response = $conn->query($query1);
									
									//inizio verifica paesi dei viaggi
									while ($risposta1 = $response -> fetch_assoc()) {
										if ( in_array(strtolower($risposta1['id']), $ex)) {
										//if ( strstr($stringcountries, strtolower($risposta1["id"]) ) ) { 
										//se il campo ID della tabella COUNTRIES è CONTENUTO NELLA STRINGA $stringcountries (campo countries della tabella dati)
											$paesiconvertiti[] = $risposta1["country"];
											print_r($paesiconvertiti);
										}
									} 
									//fine verifica paesi dei viaggi
									$stringa = implode(",",$paesiconvertiti);
									$responseArray['journey'][$idjourney] = array('countries'=>"$stringa",'seats'=>$risposta["seats"]);
								}
								
								
								$responseArray['status'] = '200';
								$responseArray['note'] = 'Journey has been modified';
							}
							
						}
						//fine verifica paesi esistenti
						
					}
					else { //se almeno uno dei parametri countries e seats non è impostato notifico l'errore con relativa stringa di correzione
						
						$responseArray['status'] = '200';
						$responseArray['note'] = 'use a string like this to modify this journey:';
						$responseArray['string'] = "http://localhost/index.php?key=Orizon&operation=2&journey=$journey&countries=country1,country2,countryN&seats=N";
					}
					
				}
				//fine MODIFICA viaggio
				
				//inizio ELIMINAZIONE viaggio
				if ($operation == "deleteJourney") {
					//stringa di esempio: http://localhost/index.php?key=Orizon&journey=10&operation=3
					
					
					$query = "SELECT * FROM dati";
					$response = $conn->query($query);
					$varcontroll = 0; //variabile di controllo
					
					while ($responseArray = $response -> fetch_assoc()) {
						if (strtolower($responseArray["journey"]) == $journey) {
							/*
							verifico se esiste il nome del viaggio inviato tramite GET $journey
							leggendo record dopo record e
							consentiamo la cancellazione
							*/
							$idjourney = $responseArray["journey"];
							$varcontroll = 1;
						}
					}

					if ($varcontroll == 1) { //viaggio da cancellare TROVATO
						//query di DELETE
						$query = "DELETE FROM dati WHERE dati.journey='$idjourney'";
						$conn->query($query);
						$responseArray['status'] = '200';
						$responseArray['note'] = "Journey n. $idjourney has been deleted";
					}
					else {
						$responseArray['status'] = '426';
						$responseArray['note'] = "Journey n. $idjourney doesn't exist";
					}
				}
				//fine ELIMINAZIONE viaggio

				//inizio VISUALIZZAZIONE dei viaggi

				
				if ($operation == "showJourney") {
					
					if ($seat == NULL) { //filtraggio per PAESE
						
						//stringa di esempio: http://localhost/index.php?key=Orizon&journey=italia&operation=4
						
						$query = "SELECT * FROM countries WHERE country = '$journey'";
						$risp = $conn->query($query);
						$r = $risp->fetch_assoc();
						if ( $r == 0) {
							//ERRORE NON ESISTONO VIAGGI CON il paese $journey
							$responseArray['status'] = '426';
							$responseArray['note'] = "There aren't Journey with country: $journey";
							$query = "SELECT * FROM countries";
							$response = $conn->query($query);
							$varcontroll = 0; //variabile di indice dell'array
							while ($array = $response -> fetch_assoc() ) {
								//ciclo i record della select e li stampo
								$responseArray[$varcontroll] = $array['country'];
								$varcontroll++;
							}
						}
						else { 
							$query = "SELECT * FROM countries WHERE country = '$journey'";
							$risp = $conn->query($query);
							$r = $risp->fetch_assoc();
							$idCountry = $r['id'];
							
							$query = "SELECT * FROM dati";
							$risposta = $conn->query($query); //inoltro della query
							$arrayprov = array(); 
							$a = 1;
							while ($r = $risposta->fetch_assoc()) { //ciclaggio record trasformati in array associativa $r
								
								$arrayprov = explode('@', $r['countries']); //estrapolazione della stringa contenuta nella colonna countries della tabella dati
								
								foreach($arrayprov as $key => $value) {
									if ($idCountry == $value) { //se il valore del paese richiesto è presente nell'array ciclata $arrayprov
										$stringapaesi = "";
										for ($i =0; $i <= ((count($arrayprov)) -2); $i++) { //estrapolo i nomi dei paesi avendo identificato l'id 
										
											$query1 = "SELECT * FROM countries WHERE id = $arrayprov[$i]";
											
											$ris = $conn->query($query1);
											$fine = $ris->fetch_assoc();
											
											//carico nelle variabili tutti i parametri che mi servono
											$journeyID = $r['journey'];
											$seats = $r['seats'];
											$stringapaesi = $stringapaesi . $fine['country'] . ',';
										}
									}
									
								}
								if ($stringapaesi == "") { }
								else {
									$responseArray['viaggio'][$a] = array('journeyID'=>"$journeyID",'countries'=>"$stringapaesi",'seats'=>"$seats" );							
									$stringapaesi = "";
									$a++;
								}
							}
							$a = $a -1;
							$responseArray['status'] = '200';
							$responseArray['note'] = "There are $a Journey that include $journey";	
						}
						
						
					}
					else { //filtraggio per POSTI
						//stringa di esempio: http://localhost/index.php?key=Orizon&journey=italia&operation=4&seats=5
						
						//indipendentemente dal valore di journey, verranno mostrati i viaggi aventi 5 o più posti disponibili
						
						$query = "SELECT * FROM dati WHERE seats >= $seat";
						$risposta = $conn->query($query); //inoltro della query
						$r = $risposta->fetch_assoc();
						if ( $r == 0) {
							//ERRORE NON ESISTONO VIAGGI CON POSTI MAGGIORI O UGUALI A $seat 
							$responseArray['status'] = '426';
							$responseArray['note'] = "There aren't Journey with $seat or more seats";
							
						}
						else {
							
							$query = "SELECT * FROM dati WHERE seats >= $seat";
							$risposta = $conn->query($query); //inoltro della query
							$arrayprov = array(); 
							$a = 1;
							while ($r = $risposta->fetch_assoc()) { //ciclaggio record trasformati in array associativa $r
								
								$arrayprov = explode('@', $r['countries']); //estrapolazione della stringa contenuta nella colonna countries della tabella dati
								
								foreach($arrayprov as $key => $value) {
									
									$stringapaesi = "";
									for ($i =0; $i <= ((count($arrayprov)) -2); $i++) { //estrapolo i nomi dei paesi avendo identificato l'id 
									
										$query1 = "SELECT * FROM countries WHERE id = $arrayprov[$i]";
										
										$ris = $conn->query($query1);
										$fine = $ris->fetch_assoc();
										
										//carico nelle variabili tutti i parametri che mi servono
										$journeyID = $r['journey'];
										$seats = $r['seats'];
										$stringapaesi = $stringapaesi . $fine['country'] . ',';
									}
									
									
								}
								if ($stringapaesi == "") { }
								else {
									$responseArray['viaggio'][$a] = array('journeyID'=>"$journeyID",'countries'=>"$stringapaesi",'seats'=>"$seats" );							
									$stringapaesi = "";
									$a++;
								}
							}
							
							$a = $a -1;
							
							$responseArray['status'] = '200';
							$responseArray['note'] = "There are $a Journey with $seat or more seats";
							
							}
						}
				
				}

				//fine VISUALIZZAZIONE dei viaggi
				
				
				$json = json_encode($responseArray);
				echo "$json";
				$conn -> close();
			}
			
			
			
			
			
			
			
			
			//gestione inserimento - modifica - cancellazione paese
			if ( ( isset($_GET["country"]) && ($_GET["country"] != NULL)  ) ||
				( isset($_GET["Country"]) && ($_GET["Country"] != NULL)  ) ) 
			{ 
		
		
				// Object Oriented connection 
				$host = "localhost";     
				$user = "root";          
				$password = "";
				$dbname = "agenzia";
				$conn = new mysqli("$host","$user","$password","$dbname");
				
				if ($conn -> connect_errno) { //se la connessione al DB riporta un errore
				  echo "Impossibile connettersi a MySQL: " . $conn -> connect_error; //comunico l'errore
				  exit(); //termino l'esecuzione dello script
				}
								
				if ( isset($_GET["country"]) && ($_GET["country"] != NULL)  ) {
					$country = $conn -> real_escape_string(htmlspecialchars(strip_tags($_GET["country"])));
				}
				
				if ( isset($_GET["Country"]) && ($_GET["Country"] != NULL)  ) {
					$country = $conn -> real_escape_string(htmlspecialchars(strip_tags($_GET["Country"])));
				}
				
				$country = strtolower($country);
				
				if ( isset($_GET["operation"]) && ($_GET["operation"] != NULL)  ) { 
					if ($_GET["operation"] == 1 ) { $operation = "insertCountry"; }
					if ($_GET["operation"] == 2 ) { 
						if ( isset($_GET["countryModified"]) && ($_GET["countryModified"] != NULL)  ) { 
							$countryModified = $conn -> real_escape_string(htmlspecialchars(strip_tags($_GET["countryModified"])));
							$countryModified = strtolower($countryModified);
						}
						else {
							//notifica errore del nuovo nome paese da aggiornare
							$responseArray = array("status"=>"426");
							$responseArray["note"] = "there was a problem to modify the name of the country";
						}
					$operation = "modifyCountry"; }
					if ($_GET["operation"] == 3 ) { $operation = "deleteCountry"; }
				}
				
				
				
				//inizio eliminazione paese
				if ($operation == "deleteCountry" ) { //se viene richiesta una modifica di un paese già esistente
					
					$query = "SELECT * FROM countries";
					$response = $conn->query($query);
					$varcontroll = 0; //variabile di controllo
					
					while ($responseArray = $response -> fetch_assoc()) {
						if (strtolower($responseArray["country"]) == $country) {
							/*
							verifico se esiste il nome del paese inviato tramite GET $country
							leggendo record dopo record e
							consentiamo la cancellazione
							*/
							$idcountry = $responseArray["id"];
							
							$varcontroll = 1;
						}
					}

					if ($varcontroll == 1) { //paese da cancellare TROVATO
						//query di DELETE
						
						$query = "DELETE FROM countries WHERE countries.country='$country'";
						$conn->query($query);
						$query = "SELECT * FROM dati";
						$response = $conn->query($query);
						$varcontroll = 0; //variabile di controllo
						
						
					
						
						
						//inizio verifica se il paese rimosso era presente in uno o più viaggi
						while ($responseArray = $response -> fetch_assoc()) {
							$arrayprov = $responseArray["countries"];
							if ( in_array($idcountry, $arrayprov  ) ) { 
							//se nel campo countries trovo il contenuto che corrisponde all'id del paese concatenato con il simbolo @
								$countriestrovati = $responseArray["countries"]; //identifico il contenuto del campo paesi
								$idcountriestrovati = $responseArray["journey"]; //identifico l'id associato al campo paesi
								$array = explode('@', $countriestrovati); //trasformo il contenuto del campo paesi in un array
								$lunghezzaarray = count($array); //calcolo la lunghezza dell'array
								
								for ($i = 0; $i <= $lunghezzaarray; $i++) { //ciclo l'array 
									if ($array[$i] == $idcountry) { //se il contenuto di un cassetto è uguale all'id del paese
										unset($array[$i]); //lo elimino
									}
								}
								
								$stringa = implode('@', $array); //ricreo la stringa dall'array
								$query = "UPDATE dati SET countries='$stringa' WHERE journey='$idcountriestrovati' "; 
								//aggiorno il contenuto del campo dei paesi associati al viaggio
								$conn -> query($query);
							}
						} 
						//fine verifica se il paese rimosso era presente in uno o più viaggi
						
						
						$query = "SELECT * from dati WHERE countries IS NULL or countries = ''";
						$response = $conn->query($query);
						while ($risposta = $response -> fetch_assoc()) {
							$iddarimuovere = $risposta["journey"];					
							$delete = "DELETE FROM dati WHERE dati.journey = '$iddarimuovere'";
							$conn->query($delete);
						}
						
						
						
						
						$responseArray = array("status"=>"200");
						$responseArray["note"] = "The country has been deleted";
					}

					if ($varcontroll == 0) { //paese da cancellare NON TROVATO
						$responseArray = array("status"=>"404");
						$responseArray["note"] = "The country can't be deleted because doens't exists in the DB";
					}
				
				} //fine eliminazione paese
				
				
				//inizio modifica paese
				if ($operation == "modifyCountry" ) { //se viene richiesta una modifica di un paese già esistente
					/*
					esempio di stringa
					http://localhost/index.php?key=Orizon&operation=2&country=irland&countryModified=irlanda
					*/
					
					$query = "SELECT * FROM countries";
					$response = $conn->query($query);
					$varcontroll = 0; //variabile di controllo
					while ($responseArray = $response -> fetch_assoc() ) {
						//echo $responseArray["country"] . "<br/>";
						if ($responseArray["country"] == $country) {
							/*
							verifico SE ESISTE il nome del paese inviato tramite GET $country
							leggendo record dopo record per consentirne dopo la MODIFICA
							*/
							$varcontroll = 1;
						}
					}
					
					if ($varcontroll == 1) { //paese da modificare TROVATO
						//query di UPDATE
						//echo "esiste";
						$query = "UPDATE countries SET country='$countryModified' WHERE countries . country='$country' ";
						$conn->query($query);
						
						//inizio verifica di avvenuta modifica del NOME del paese nel DB (opzionale)
						$query = "SELECT * FROM countries";
						$response = $conn->query($query);
						$varcontroll2 = 0;
						while ($responseArray = $response -> fetch_assoc()) {
							if ($responseArray["country"] == $countryModified) {
								$varcontroll2 = 1;
							}
						}
						if ($varcontroll2 == 0) {
							//echo "NON esiste";
							//errore in fase di inserimento del paese
							$responseArray = array("status"=>"426");
							$responseArray["note"] = "there was a problem to modify the name of the country";
						}
						else {
							//inserimento del paese andato a buon fine
							$responseArray["status"] = "200";
							$responseArray["note"] = "Country has been modified";
						}
						//fine verifica di avvenuta modifica del NOME del paese nel DB (opzionale)
						
						
						
					}
					else { //se il paese da modificare NON è stato trovato
						$query = "SELECT * FROM countries";
						$response = $conn->query($query);
						$varcontroll = 0; //variabile di indice dell'array
						$responseArray["status"] = "426";
						$responseArray["note"] = "Country doesn't exist in the DB, you can modify only this countries";
						while ($array = $response -> fetch_assoc() ) {
							//ciclo i record della select e li stampo
							$responseArray[$varcontroll] = $array;
							$varcontroll++;
						}
					}








					
				} //fine modifica paese
				
				
				//inizio inserimento paese
				if ($operation == "insertCountry" ) { //se viene richiesto un nuovo inserimento
					/*
						esempio di stringa
						http://localhost/index.php?key=Orizon&country=irland&operation=1
					*/
				
				
					$query = "SELECT * FROM countries";
					$response = $conn->query($query);

					$varcontroll = 0;
					
					while ($responseArray = $response -> fetch_assoc()) {
						if ($responseArray["country"] == $country) {
							/*
								verifico se esiste il paese nella tabella
								leggendo record dopo record 
								per abilitare successivamente l'inserimento
							*/
							$varcontroll = 1;
						}
					}
					
					if ($varcontroll == 0) { //il paese NON esiste nel DB quindi lo inserisco
						$query = "INSERT INTO countries (country) VALUES ('$country')";
						$response = $conn->query($query);
						
						
						
						//inizio verifica di avvenuto inserimento del paese nel DB (opzionale)
						$query = "SELECT * FROM countries";
						$response = $conn->query($query);
						$varcontroll2 = 0;
						while ($responseArray = $response -> fetch_assoc()) {
							if ($responseArray["country"] == $country) {
								$varcontroll2 = 1;
							}
						}
						if ($varcontroll2 == 0) { //errore in fase di inserimento del paese
							$responseArray = array("status"=>"426");
							$responseArray["note"] = "Contact the Administrator to solve this problem";
						}
						else { //inserimento del paese andato a buon fine
							$responseArray["status"] = "200";
							$responseArray["note"] = "Country has been added";
						}
						//fine della verifica di avvenuto inserimento del paese nel DB (opzionale)
						
					}
					else { //il paese esiste già nel DB
						$responseArray = array("status"=>"406");
						$responseArray["note"] = "Country already exists";
					}
				} //fine inserimento paese
				
	
				$json = json_encode($responseArray);
				echo "$json";
				$conn -> close();
			}
		}
	}
}
?>
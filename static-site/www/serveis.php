<?php
session_start();
$PaginaActual = "serveis";
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION["Idioma"] ?>">
	<head>
		<meta charset="utf-8" />
		<title>Els nostres serveis</title>
		<meta name="description" content="" />
		<meta name="Author" content="Dorin Grigoras [www.stepofweb.com]" />
		<link rel="icon" type="image/png" href="/web/images/favicon.png">

		<!-- mobile settings -->
		<meta name="viewport" content="width=device-width, maximum-scale=1, initial-scale=1, user-scalable=0" />
		<!--[if IE]><meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'><![endif]-->

		<!-- WEB FONTS : use %7C instead of | (pipe) -->
		<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600%7CRaleway:300,400,500,600,700%7CLato:300,400,400italic,600,700" rel="stylesheet" type="text/css" />

		<!-- CORE CSS -->
		<link href="/web/css/bootstrap.min.css" rel="stylesheet" type="text/css" />

		<!-- THEME CSS -->
		<link href="/web/css/essentials.css" rel="stylesheet" type="text/css" />
		<link href="/web/css/layout.css?v=1.1" rel="stylesheet" type="text/css" />

		<!-- PAGE LEVEL SCRIPTS -->
		<link href="/web/css/header-1.css" rel="stylesheet" type="text/css" />
		<link href="/web/css/orange.css" rel="stylesheet" type="text/css" id="color_scheme" />
	</head>

	<!--
		AVAILABLE BODY CLASSES:
		
		smoothscroll 			= create a browser smooth scroll
		enable-animation		= enable WOW animations

		bg-grey					= grey background
		grain-grey				= grey grain background
		grain-blue				= blue grain background
		grain-green				= green grain background
		grain-blue				= blue grain background
		grain-orange			= orange grain background
		grain-yellow			= yellow grain background
		
		boxed 					= boxed layout
		pattern1 ... patern11	= pattern background
		menu-vertical-hide		= hidden, open on click
		
		BACKGROUND IMAGE [together with .boxed class]
		data-background="assets/images/_smarty/boxed_background/1.jpg"
	-->
	<body class="smoothscroll enable-animation">


		<!-- wrapper -->
		<div id="wrapper">

<?php
include_once("php/cabecera.php");
?>

			<section class="header-solidaritat page-header page-header-xlg b-0 dark" 
				data-background-delay="5000" 
				data-background-fade="1000" 
				data-background="
					demo_files/images/packs/photography/cover/sean-mcauliffe-12854-min.jpg
					,demo_files/images/packs/photography/cover/jorge-rojas-182706-min.jpg
			">

				<span class="overlay dark-4"><!-- dark overlay [1 to 9 opacity] --></span>
				<div class="container">

					<small>Els serveis de la</small>
					<h2 class="fs-40 fw-300">SERRALLERIA</h2>

				</div>
			</section>



			<!-- -->
			<section>
				<div class="container">

					<div class="row">
					
						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/obertures.jpg" alt="Obertures" />
							</figure>

							<h4 class="mb-0">Obertures</h4>
							<small class="font-lato fs-18 mb-30 block">Criteri de NO trencament</small>
							<p class="text-muted fs-14">Obrim qualsevol tipus de tancament ja siguin portes, cadenats, persianes, ballestes, finestres, cotxes, caixes fortes, escuts, armaris, bústies… Seguint un criteri de NO trencament sempre que es pugui.</p>

						</div>

						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/cerraduras-i-cerrojos.jpg" alt="Instal·lació de panys i escuts" />
							</figure>

							<h4 class="mb-0">Instal·lació de panys i escuts</h4>
							<small class="font-lato fs-18 mb-30 block">Utilitzem les principals marques</small>
							<p class="text-muted fs-14">En portes o persianes a escollir entre un ampli ventall de marques i models amb acabats de qualitat. També tancaments de seguretat en persianes domèstiques o fusteria d’alumini, segons panys: INN, KESO, AGA, AMIG, ARCU, AZBE, BTV, CORBIN, DORMA, TESA, DORCAS, EZCURRA, FAC, MOTTURA, ABUS, INCECA, KEYA, LINCE, MERONI, MOIA, ORENGO, PREFER, SECUREMME, SIDESE, KABA, CISA, POTENT, MCM, SERRALLER, ISEO, DISEC, JIS, TECNOLAR, JOANPER, UCEM.</p>
							<p>Tenim material per modificar cilindres INN i KESO a domicili.</p>

						</div>

						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/Canvi-de-combinació.jpeg" alt="Canvi de combinació de panys" />
							</figure>

							<h4 class="mb-0">Canvi de combinació de panys</h4>
							<small class="font-lato fs-18 mb-30 block">Ho fem fàcil</small>
							<p class="text-muted fs-14">Combinacions en panys de gorges i en cilindres, per a gent que no vol canviar el pany però si la clau en cas de robatori o pèrdua de claus. També es canvien panys sencers, i es fan adaptacions.</p>
							
						</div>
					</div> <!-- row -->
					<br>
					<br>
					<div class="row">
						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/Persiana-domestica.jpg" alt="Persianes domèstiques" />
							</figure>

							<h4 class="mb-0">Persianes domèstiques</h4>
							<small class="font-lato fs-18 mb-30 block">Instal·lació i reparació</small>
							<p class="text-muted fs-14">Ja siguin d’alumini o plàstic, a partir d’un buit en la paret o bé substituir per una existent. Reparacions en cas d’avaria i/o un trencament, automatitzar-les... SOMFY, ERREKA, PUJOL MUNTALÀ...</p>

						</div>

						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/Porta-basculant.jpg" alt="Portes basculants i oscil·lo batents" />
							</figure>

							<h4 class="mb-0">Portes basculants i oscil·lo batents</h4>
							<small class="font-lato fs-18 mb-30 block">Instal·lació, reparació i motorització</small>
							<p class="text-muted fs-14">Reparació de portes basculants i oscil·lo batents, canvis de cablejat, motorització (CE), manteniments (CE), reparació de xapa, corrons, coixinets, cadenats…</p>

						</div>

						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/Tancaments_fixos.jpeg" alt="Tancaments fixos" />
							</figure>

							<h4 class="mb-0">Tancaments fixos</h4>
							<small class="font-lato fs-18 mb-30 block">Amb qualitat i garantia</small>
							<p class="text-muted fs-14">Instal·lació de reixes, fixes i accessibles, per a finestres i portes, treballem amb una àmplia gamma de productes i una immillorable garantia en materials i acabats finals. Servei de lacat i galvanitzat.</p>

						</div>

					</div> <!-- row -->
					<br>
					<br>
					<div class="row">
						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/Persiana-comercial.jpg" alt="Persianes comercials" />
							</figure>

							<h4 class="mb-0">Persianes comercials</h4>
							<small class="font-lato fs-18 mb-30 block">Per el teu local</small>
							<p class="text-muted fs-14">Instal·lació de persianes noves. Reparem tot tipus de persianes: alumini, acer, petxines, perforades, inoxidable… i motoritzem amb comandament a distància o polsador (CE).</p>
							<p class="text-muted fs-14">Modifiquem estructures, per canvis o reformes en les persianes. ERREKA, PUJOL MUNTALÀ, FORSA, DEA, V2...</p>

						</div>

						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/Cadenat_de_Persiana.jpg" alt="Cadenats de seguretat" />
							</figure>

							<h4 class="mb-0">Cadenats de seguretat</h4>
							<small class="font-lato fs-18 mb-30 block">Un altre opció de tancament</small>
							<p class="text-muted fs-14">Cadenats de persianes, especialitzats en la marca BILMA, CASAS i TOY, tot i que també treballem LYF, SAMI, YTE, STARK, GILE, RUE, AUMON, KEYMAT, SAG.</p>

						</div>

						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/Portes-corredises.jpg" alt="Portes corredisses" />
							</figure>

							<h4 class="mb-0">Portes corredisses</h4>
							<small class="font-lato fs-18 mb-30 block">Obre i tanca sense esforç</small>
							<p class="text-muted fs-14">Reparació, instal·lació, motorització (CE) i manteniment (CE).</p>

						</div>

					</div> <!-- row -->
					<br>
					<br>
					<div class="row">
					<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/Ballesta-Zincada.jpg" alt="Portes de ballesta" />
							</figure>

							<h4 class="mb-0">Portes de ballesta</h4>
							<small class="font-lato fs-18 mb-30 block">Evita els robatoris i intrusions</small>
							<p class="text-muted fs-14">Instal·lació i reparació de portes de ballestes en totes les seves possibilitats i colors, afegim seguretat dissenyant els nostres propis models per la nostra experiència en evitar robatoris i dificultar la intrusió.</p>

						</div>

						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/Caixes-fortes.jpg" alt="Caixes Fortes" />
							</figure>

							<h4 class="mb-0">Caixes Fortes</h4>
							<small class="font-lato fs-18 mb-30 block">Per els objectes de valor</small>
							<p class="text-muted fs-14">Instal·lació amb seguretat, discreció i camuflatge. INN, FAC, BTV...</p>

						</div>

						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/Control-de-Accesoss.jpg" alt="Control d’accessos" />
							</figure>

							<h4 class="mb-0">Control d’accessos</h4>
							<small class="font-lato fs-18 mb-30 block">Per saber qui hi es i qui no</small>
							<p class="text-muted fs-14">Instal·lació de seguretat, gestió i manteniment.  Material actualitzat a les normatives per fer un control de presència, SUMAT, INN, TESA, NÜO...</p>

						</div>

					</div> <!-- row -->
					<br>
					<br>
					<div class="row">
						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/Porta-comunitat.jpg" alt="Portes de comunitat" />
							</figure>

							<h4 class="mb-0">Portes de comunitat</h4>
							<small class="font-lato fs-18 mb-30 block">La primera barrera a la nostra llar</small>
							<p class="text-muted fs-14">Fabricació, instal·lació, manteniment, canvi de bombins, seguretat activa, mestrejaments comunitaris, tancaments electro-mecànics per comunitats que no volen facilitar l’entrada de gent aliena a la finca. DORCAS, JIS.</p>

						</div>

						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/muelle-cierra-puertas.jpg" alt="Molles tancaportes" />
							</figure>

							<h4 class="mb-0">Molles tancaportes</h4>
							<small class="font-lato fs-18 mb-30 block">La porta de la comunitat ben tancada</small>
							<p class="text-muted fs-14">Instal·lació i manteniment MAB, DORCAS, DORMA, TELESCO, TESA, ELMA, JUSTOR, GEZE.</p>

						</div>

						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/Operadors-portes.jpg" alt="Operadors" />
							</figure>

							<h4 class="mb-0">Operadors</h4>
							<small class="font-lato fs-18 mb-30 block">Comoditat i seguretat</small>
							<p class="text-muted fs-14">Instal·lació i manteniment d’operadors de portes, la porta obre i tanca sense necessitat d’ajuda, especialment indicat per gent gran i persones amb mobilitat reduïda. DORCAS, ERREKA.</p>

						</div>

					</div> <!-- row -->
					<br>
					<br>
					<div class="row">
						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/Mestrejament.jpeg" alt="Mestrejaments" />
							</figure>

							<h4 class="mb-0">Mestrejaments</h4>
							<small class="font-lato fs-18 mb-30 block">Per la vostra comoditat</small>
							<p class="text-muted fs-14">Per reduir el nombre de claus i per comoditat dels clients, realitzem plans de mestrejaments, per a particulars, comunitats, empreses...</p>

						</div>
						
						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/manteniment.jpeg" alt="Manteniments" />
							</figure>

							<h4 class="mb-0">Manteniments</h4>
							<small class="font-lato fs-18 mb-30 block">Sempre tot a punt</small>
							<p class="text-muted fs-14">Tenim diversos contractes de manteniment sigui portes de comunitat com els propis de la certificació CE de portes automàtiques. En breu sorgiran normatives més específiques en el sector de la seguretat.</p>

						</div>
						
						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/traster.jpeg" alt="Portes de traster" />
							</figure>

							<h4 class="mb-0">Portes de traster</h4>
							<small class="font-lato fs-18 mb-30 block">Les teves coses ben tancades</small>
							<p class="text-muted fs-14">Col·locació de portes de traster, reforç amb panys especials, també ens encarreguem de treure les portes existents i col·locar de noves amb certificació i reforçades en cas de robatori.</p>

						</div>

					</div> <!-- row -->
					<br>
					<br>
					<div class="row">
						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/Puerta blindada.jpg" alt="Portes Blindades" />
							</figure>

							<h4 class="mb-0">Portes Blindades</h4>
							<small class="font-lato fs-18 mb-30 block">Els hi posem difícil</small>
							<p class="text-muted fs-14">Tenim un equip de fusters que instal·laran la millor opció per a cada client, sempre amb acabats de qualitat i seguretat.</p>

						</div>
						
						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/Portes-de-seguretat.jpeg" alt="Portes de Seguretat" />
							</figure>

							<h4 class="mb-0">Portes de Seguretat</h4>
							<small class="font-lato fs-18 mb-30 block">Un dels elements més importants</small>
							<p class="text-muted fs-14">Treballem actualment amb la marca INN en les dues opcions Ecofeel y Securefeel. Són portes certificades, de grau 3, 4C, 5C.</p>

						</div>
						
						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/Escuts-de-seguretat.jpg" alt="Escuts de seguretat" />
							</figure>

							<h4 class="mb-0">Escuts de seguretat</h4>
							<small class="font-lato fs-18 mb-30 block">El pany ben protegit</small>
							<p class="text-muted fs-14">La solució óptima pel sistema del tancament d’una porta és la combinació d’un cilindre i un escut de SEGURETAT. Treballem amb DISEC i amb els nous models de INN.</p>

						</div>

					</div> <!-- row -->
					<br>
					<br>
					<div class="row">
						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/Conversio.jpeg" alt="Conversions" />
							</figure>

							<h4 class="mb-0">Conversions</h4>
							<small class="font-lato fs-18 mb-30 block">Una forma senzilla de millorar la seguretat</small>
							<p class="text-muted fs-14">Quan canviar la porta no es una opció podem fer una actualització global de pany+escut+bombí, ho denominem conversió, sempre mantenint la mateixa porta. EZCURRA, ARCU, MOTTURA, MOIA, CISA, ORENGO.</p>

						</div>
						
						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/deteccio-anticipada.jpeg" alt="Detecció anticipada" />
							</figure>

							<h4 class="mb-0">Detecció anticipada</h4>
							<small class="font-lato fs-18 mb-30 block">Quan abans millor</small>
							<p class="text-muted fs-14">Un concepte molt modern, en cas d’intent de robatori, de mantenir al lladre a l’exterior de casa i amb la possibilitat de fer arribar un avis al mòbil del client. (amb el complement del panell)</p>

						</div>
						
						<div class="col-lg-4 col-md-4 col-sm-4">
							<figure class="mb-20 fotoserveis">
								<img class="img-fluid" src="/web/images/serveis/logo-sin-texto.png" alt="Altres" />
							</figure>

							<h4 class="mb-0">Altres</h4>
							<small class="font-lato fs-18 mb-30 block">Molt més que una serralleria</small>
							<p class="text-muted fs-14">Soldadures, làmines de seguretat, mestrejaments, igualacions, modificació de gorges ARCAS SOLER, JANPER, LAR...</p>
							<p class="text-muted fs-14">Tenim els nostres propis professionals de feines relacionades com fuster, electricista, paleta... i col·laborem amb serrallers qualificats i de plena confiança. Més informació a enllaços d’interès.</p>

						</div>

					</div> <!-- row -->

				</div>
			</section>
			<!-- / -->


			



			<!-- BUTTON CALLOUT -->
			<a href="contacte.php" class="btn btn-xlg btn-primary fs-20 fullwidth m-0 rad-0 p-40">
				<span class="font-lato fs-30">
					Explica'ns que necessites 
					<strong>Contacta'ns &raquo;</strong>
				</span>
			</a>
			<!-- /BUTTON CALLOUT -->




			<!-- FOOTER -->
<?php 
include_once("php/pie.php");
?>
			<!-- /FOOTER -->

		</div>
		<!-- /wrapper -->



		<!-- JAVASCRIPT FILES -->
		<script>var plugin_path = 'plugins/';</script>
		<script src="/web/js/jquery-3.3.1.min.js"></script>

		<script src="/web/js/scripts.js"></script>
		
		<!-- PAGE LEVEL SCRIPTS [cart js] -->
		<script>
			jQuery(window).on('load', function() {

				/**
					Chart.js
					http://www.chartjs.org/
				**/
				loadScript('/web/js/chart.chartjs/Chart.min.js', function() {

					// polarAreaChartCanvas
					var polarAreaChartCanvas = [
						{
							value : 62,
							color: "#D97041"
						},
						{
							value : 70,
							color: "#C7604C"
						},
						{
							value : 41,
							color: "#21323D"
						},
						{
							value : 24,
							color: "#9D9B7F"
						},
						{
							value : 55,
							color: "#7D4F6D"
						},
						{
							value : 18,
							color: "#584A5E"
						}
					];

					var ctx = document.getElementById("polarAreaChartCanvas").getContext("2d");
					new Chart(ctx).PolarArea(polarAreaChartCanvas);



					// barChartCanvas
					var barChartCanvas = {
						labels:["January","February","March","April","May","June","July"],
						datasets:[
							{
								fillColor : "rgba(220,220,220,0.5)",
								strokeColor : "rgba(220,220,220,0.8)",
								highlightFill: "rgba(220,220,220,0.75)",
								highlightStroke: "rgba(220,220,220,1)",
								data : [65,59,90,81,56,55,50]
							}
						]
					};

					var ctx = document.getElementById("barChartCanvas").getContext("2d");
					new Chart(ctx).Bar(barChartCanvas);

				});

			});
		</script>

	</body>
</html>
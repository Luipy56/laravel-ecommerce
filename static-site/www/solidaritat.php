<?php
session_start();
$PaginaActual = "solidaritat";
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION["Idioma"] ?>">

<head>
	<meta charset="utf-8" />
	<title>Solidaritat</title>
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
<?php include_once __DIR__ . '/php/analytics.php'; ?>
<?php include_once __DIR__ . '/php/seo-head.php'; ?>
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

		<section class="header-solidaritat page-header page-header-xlg b-0 dark" data-background-delay="5000" data-background-fade="1000" data-background="
					demo_files/images/packs/photography/cover/sean-mcauliffe-12854-min.jpg
					,demo_files/images/packs/photography/cover/jorge-rojas-182706-min.jpg
			">

			<span class="overlay dark-4">
				<!-- dark overlay [1 to 9 opacity] -->
			</span>
			<div class="container">

				<small>Els nostres projectes</small>
				<h2 class="fs-40 fw-300">SOLIDARIS</h2>

			</div>
		</section>
		<!-- /PAGE HEADER -->

		<section">
			<div class="container">

				<div class="row">
					<div class="col-lg-6 col-12">
						<img class="img-fluid" src="/web/images/solidaritat/solidaritat.png" alt="Projectes solidaris">
					</div>
					<div class="col-lg-6 col-12">
						<p class="lead">Aquestos son els nostres projectes solidaris. Cada cop que realitzem un servei aportem a projectes com aquestos un 10% dels nostres beneficis. En el moment que ens encarreguis una feina tú també estaràs col·laborant en els projectes</p>
					</div>
				</div>

				<div class="divider divider-center divider-color">
					<!-- divider -->
					<i class="fa fa-chevron-down"></i>
				</div>


				<article class="row">
					<!-- <div class="col-lg-6 col-12">
						<img class="img-fluid" src="/web/images/solidaritat/solidaritat.png" alt="Projectes solidaris">
					</div> -->
					<div class="col-md-6">
						<h3><span>Nou projecte solidari</span></h3>
						<p>Després d'aquests dies tan estranys, tornem amb més força, potse no amb molts diners, però amb un Gran objectiu, la única cosa que podem dir, és que volem que sigui molt gran, nosaltres som petits, però volem remoure cosetes a la ciutat. El proper projecte es centrarà al barri gòtic, i estem començant a escalfar motors. Al setembre d'aquest any es presentarà el projecte, i es durà a terme a partir de la primavera del 2022.</p>
					</div>
				</article>

				<div class="divider divider-center divider-color">
					<!-- divider -->
					<i class="fa fa-chevron-down"></i>
				</div>


				<article class="row">
					<div class="col-md-6 d-none">
						<div class="owl-carousel-2 controlls-over clearfix" data-plugin-options='{
								"responsiveBaseElement":	"#middle", 
								"loop":						true, 
								"margin":					10, 

								"nav":						false, 
								"dots":						true, 

								"center":					false, 
								"slideBy":					"1", 
								"autoplay":					true, 
								"autoplayTimeout":			5000, 
								"autoWidth":				false,
								"merge":					false,
								"rtl":						false,

								"animateIn":				"flipInX",
								"animateOut":				"flipOutX",

								"items":					1

								}'>

							<div class="owl-item">
								<img class="img-fluid" src="/web/images/solidaritat/Projecte-Solidari-Centre-Obert-Rialles-1.jpg" alt="Projecte Solidari Centre Obert Rialles">
							</div>
							<div class="owl-item">
								<img class="img-fluid" src="/web/images/solidaritat/Projecte-Solidari-Centre-Obert-Rialles-2.jpg" alt="Projecte Solidari Centre Obert Rialles">
							</div>
							<div class="owl-item">
								<img class="img-fluid" src="/web/images/solidaritat/Projecte-Solidari-Centre-Obert-Rialles-3.jpg" alt="Projecte Solidari Centre Obert Rialles">
							</div>
							<div class="owl-item">
								<img class="img-fluid" src="/web/images/solidaritat/Projecte-Solidari-Centre-Obert-Rialles-4.jpg" alt="Projecte Solidari Centre Obert Rialles">
							</div>

						</div>
					</div>
					<div class="col-md-6">
						<h3><span>Projecte DRB 2018-19</span></h3>
						<p>Estem col·laborant en la formació i desenvolupament d’un grup de música de nois d’entre 18 i 20 anys que pertanyen a Centres Oberts per inculcar valors de col·laboració, de treball en equip, de gestió, aprenentatge...</p>

						<p>OBJECTIU: 450,00 €</p>

						<img src="images/solidaritat/assolit.png" alt="Objectiu assolit">
					</div>
				</article>

				<div class="divider divider-center divider-color">
					<!-- divider -->
					<i class="fa fa-chevron-down"></i>
				</div>


				<article class="row">
					<div class="col-md-6">
						<div class="owl-carousel-2 controlls-over clearfix" data-plugin-options='{
								"responsiveBaseElement":	"#middle", 
								"loop":						true, 
								"margin":					10, 

								"nav":						false, 
								"dots":						true, 

								"center":					false, 
								"slideBy":					"1", 
								"autoplay":					true, 
								"autoplayTimeout":			5000, 
								"autoWidth":				false,
								"merge":					false,
								"rtl":						false,

								"animateIn":				"flipInX",
								"animateOut":				"flipOutX",

								"items":					1

								}'>

							<div class="owl-item">
								<img class="img-fluid" src="/web/images/solidaritat/Projecte-Lliga-De-Valors-1.jpg" alt="Projecte-Lliga-De-Valors">
							</div>
							<div class="owl-item">
								<img class="img-fluid" src="/web/images/solidaritat/Projecte-Lliga-De-Valors-2.jpg" alt="Projecte-Lliga-De-Valors">
							</div>
							<div class="owl-item">
								<img class="img-fluid" src="/web/images/solidaritat/Projecte-Lliga-De-Valors-3.jpg" alt="Projecte-Lliga-De-Valors">
							</div>
							<div class="owl-item">
								<img class="img-fluid" src="/web/images/solidaritat/Projecte-Lliga-De-Valors-4.jpg" alt="Projecte-Lliga-De-Valors">
							</div>

						</div>
					</div>
					<div class="col-md-6">
						<h3><span>Projecte Lliga de Valors 2016-17</span></h3>
						<p>A la ciutat de Santa Coloma es va celebrar un campionat de futbol sala molt especial una lliga...però de valors, on el més important no eren els gols sinó una sèrie de valors com l’esportivitat, la igualtat, el companyerisme, la col·laboració, el joc en equip, l’entrenament...</p>

						<p>OBJECTIU: 600,00 €</p>

						<img src="images/solidaritat/assolit.png" alt="Objectiu assolit">
					</div>
				</article>

				<div class="divider divider-center divider-color">
					<!-- divider -->
					<i class="fa fa-chevron-down"></i>
				</div>


				<article class="row">
					<div class="col-md-6">
						<div class="owl-carousel-2 controlls-over clearfix" data-plugin-options='{
								"responsiveBaseElement":	"#middle", 
								"loop":						true, 
								"margin":					10, 

								"nav":						false, 
								"dots":						true, 

								"center":					false, 
								"slideBy":					"1", 
								"autoplay":					true, 
								"autoplayTimeout":			5000, 
								"autoWidth":				false,
								"merge":					false,
								"rtl":						false,

								"animateIn":				"flipInX",
								"animateOut":				"flipOutX",

								"items":					1

								}'>

							<div class="owl-item">
								<img class="img-fluid" src="/web/images/solidaritat/escoladefamiliesaprenem.png" alt="Escola de families Associació Aprenem">
							</div>
							<div class="owl-item">
								<img class="img-fluid" src="/web/images/solidaritat/tags_aprenem.png" alt="Tags Associació Aprenem">
							</div>

						</div>
					</div>
					<div class="col-md-6">
						<h3><span>Projecte Aprenem 2015-16</span></h3>
						<p>Amb aquesta associació portem uns anys i la veritat és que ens agrada molt participar ja que hem aportat diversos col·laboradors de Serralleria Solidària, hem aportat la nostra professionalitat en un hort urbà. <a href="https://www.associacioaprenem.org/inaugurem-espai-trueta-200/" target="_blank">(Espai Trueta 200)</a></p>

						<p>OBJECTIU: 420,00 €</p>

						<img src="images/solidaritat/assolit.png" alt="Objectiu assolit">
					</div>
				</article>

				<div class="divider divider-center divider-color">
					<!-- divider -->
					<i class="fa fa-chevron-down"></i>
				</div>


				<article class="row">
					<div class="col-md-6">
						<div class="owl-carousel-2 controlls-over clearfix" data-plugin-options='{
								"responsiveBaseElement":	"#middle", 
								"loop":						true, 
								"margin":					10, 

								"nav":						false, 
								"dots":						true, 

								"center":					false, 
								"slideBy":					"1", 
								"autoplay":					true, 
								"autoplayTimeout":			5000, 
								"autoWidth":				false,
								"merge":					false,
								"rtl":						false,

								"animateIn":				"flipInX",
								"animateOut":				"flipOutX",

								"items":					1

								}'>

							<div class="owl-item">
								<img class="img-fluid" src="/web/images/solidaritat/Projecte-Solidari-Centre-Obert-Rialles-1.jpg" alt="Projecte Solidari Centre Obert Rialles">
							</div>
							<div class="owl-item">
								<img class="img-fluid" src="/web/images/solidaritat/Projecte-Solidari-Centre-Obert-Rialles-2.jpg" alt="Projecte Solidari Centre Obert Rialles">
							</div>
							<div class="owl-item">
								<img class="img-fluid" src="/web/images/solidaritat/Projecte-Solidari-Centre-Obert-Rialles-3.jpg" alt="Projecte Solidari Centre Obert Rialles">
							</div>
							<div class="owl-item">
								<img class="img-fluid" src="/web/images/solidaritat/Projecte-Solidari-Centre-Obert-Rialles-4.jpg" alt="Projecte Solidari Centre Obert Rialles">
							</div>

						</div>
					</div>
					<div class="col-md-6">
						<h3><span>PROJECTE CENTRE OBERT RIALLES 2014-15</span></h3>
						<p>Al Centre Obert Rialles, hi ha molts joves que hi dediquen els seus esforços a diferents tasques i motivacions, l’equip directiu proporciona eines i recursos per a dur a terme aquestes tasques, i aquí és on entrem nosaltres, durant el primer trimestre es vam fer una campanya de sensibilització del projecte Fratelli, es van subvencionar les polseres que es van repartir al festival de nadal. Durant aquest segon trimestre han preparat la fira solidària que ha tingut lloc a Santa Coloma el 23 de Març.</p>

						<p>Tots treballem per fer d’aquest món un millor lloc per viure-hi.</p>

						<p>Després de reunir-nos amb el personal docent vam voler oferir la possibilitat de treballar un projecte amb nois de 14 a 16 anys per poder desenvolupar les seves actituds i inculcar les responsabilitats de recollir diners per un projecte educatiu al Líban, nosaltres aportàvem els materials necessaris per dur a terme els objectius, la gestió, recollida i distribució dels beneficis van formar part del seu aprenentatge. </p>

						<p>OBJECTIU: 695,20 €</p>

						<img src="images/solidaritat/assolit.png" alt="Objectiu assolit">
					</div>
				</article>

				<div class="divider divider-center divider-color">
					<!-- divider -->
					<i class="fa fa-chevron-down"></i>
				</div>

				<article class="row">
					<div class="col-md-6">
						<div class="owl-carousel-2 controlls-over clearfix" data-plugin-options='{
								"responsiveBaseElement":	"#middle", 
								"loop":						true, 
								"margin":					10, 

								"nav":						false, 
								"dots":						true, 

								"center":					false, 
								"slideBy":					"1", 
								"autoplay":					true, 
								"autoplayTimeout":			5000, 
								"autoWidth":				false,
								"merge":					false,
								"rtl":						false,

								"animateIn":				"bounceIn",
								"animateOut":				"bounceOut",

								"items":					1

								}'>

							<div class="owl-item">
								<img class="img-fluid" src="/web/images/solidaritat/Projecte-Solidari-Club-futbol-sala-Centre-Compartir-1.jpg" alt="Projecte Solidari Club futbol sala Centre Compartir">
							</div>
							<div class="owl-item">
								<img class="img-fluid" src="/web/images/solidaritat/Projecte-Solidari-Club-futbol-sala-Centre-Compartir-2.jpg" alt="Projecte Solidari Club futbol sala Centre Compartir">
							</div>
							<div class="owl-item">
								<img class="img-fluid" src="/web/images/solidaritat/Projecte-Solidari-Club-futbol-sala-Centre-Compartir-3.jpg" alt="Projecte Solidari Club futbol sala Centre Compartir">
							</div>
							<div class="owl-item">
								<img class="img-fluid" src="/web/images/solidaritat/Projecte-Solidari-Club-futbol-sala-Centre-Compartir-4.jpg" alt="Projecte Solidari Club futbol sala Centre Compartir">
							</div>

						</div>
					</div>
					<div class="col-md-6">
						<h3><span>PROJECTE CLUB FUTBOL SALA COMPARTIR CURS 2013-14</span></h3>
						<p>El primer projecte, després de moltes converses amb els monitors i personal del centre es va decidir de aportar una bossa d’esport per a cada nen i un recull de material esportiu, fins arribar a la xifra de donació (938.63 €)</p>
						<p>El Club futbol sala Centre Compartir es un projecte de la Associació Gabella, situada al barri Gòtic de Barcelona que pretèn atendre als nens i joves en risc d’exclusió social.<br>
							Atenem nens i joves de 9 a 21 anys del districte de Ciutat Vella i a través del futbol realitzem un treball socioeducatiu amb l’objectiu d’ afavorir una correcta integració social.</p>

						<p>
							Aquest curs 2013/14 estem atenent 70 jugadors repartits en 7 equips:<br>
						<ul>
							<li>Equip aleví mixte, nens de 9 a 11 anys</li>
							<li>Equip infantil masculí de 12 i 14 anys</li>
							<li>Equip cadet masculí I femení de 15 i 17 anys</li>
							<li>Equip juvenil masculí de 18 i 20 anys</li>
							<li>Equip junior masculí i femení de 20 i 21 anys</li>
						</ul>
						<br>
						Els nostres equips participen a la lliga del Consell escolar de Barcelona, i per mitjà dels entrenaments setmanals i dels partits de cada dissabte, els entrenadors del club potencien el desenvolupament en valors per ajudar als nens i joves en el seu process de creixement personal.
						</p>

						<p>Si vols saber més de nosaltres, fes-te amic nostre al Facebook: <a href="https://www.facebook.com/cfscentrecompartir.gabella?fref=ts" target="_blank">CFS Centre Compartir</a></p>

						<p>OBJECTIU: 938,63</p>

						<img src="images/solidaritat/assolit.png" alt="Objectiu assolit">
					</div>
				</article>


			</div>
			</section>



			<!-- -->
			<section class="alternate">
				<div class="container">
					<div class="row">

						<div class="col-md-8">

							<div class="heading-title heading-border-bottom heading-color">
								<h3>Que és la Economia Solidària</h3>
							</div>


						</div>


					</div>
					<p style="color: #631d76;">La Economia Solidària es un enfocament de la activitat econòmica que te en compte a les persones, el medi ambient i el desenvolupament sostenible y sustentable, com a referencia prioritària per sobre d’altres interessos.</p>
					<p>Sota diverses fórmules, la Economia Social i Solidària, també anomenada Economia Solidària o ESS, es una manera de viure que avarca la integritat de les persones i designa la subordinació de la economia a la seva veritable finalitat: proveir de manera sostenible las bases materials per al desenvolupament personal, social i ambiental de l’ésser humà.</p>
					<p>La referencia de la economia solidària es cada subjecte i les comunitats creades des de les iniciatives socials, pel que no se la identifica segons els beneficis materials d’una iniciativa, sinó que es defineix en funció de la qualitat de vida i el benestar dels seus membres i de tota la societat com a sistema global.</p>
					<p>La Economia Solidaria, en el marc de la tradició de la Economia Social, pretén incorporar a la gestió de l’activitat econòmica, els valors universals que han de regir la societat i les relacions entre tota la ciutadania: equitat, justícia, fraternitat econòmica, solidaritat social i democràcia directa. I, en tant que una nova forma de produir, de consumir i de distribuir, es proposa com una alternativa viable i sostenible per a la satisfacció de les necessitats individuals i globals, aspirant a consolidar-se com un instrument de transformació social.</p>
					<p>Les organitzacions que participem en el moviment de la Economia Solidaria en general compartim, per al desenvolupament de la nostra missió, els següents eixos transversals:</p>

					<ul>
						<li class="mt-4">La autonomia com a principi de llibertat y exercici de la coresponsabilitat.</li>
						<li class="mt-4">La autogestió com a metodologia que respecta, implica, educa, iguala les oportunitats i possibilita l’apoderament.</li>
						<li class="mt-4">La cultura alliberadora com a base de pensaments creatius, científics i alternatius que ens ajuden a buscar, investigar i trobar noves formes de conviure, produir, gaudir, consumir i organitzar la política i la economia al servei de totes les persones.</li>
						<li class="mt-4">El desenvolupament de les persones en totes les seves dimensions i capacitats: físiques, psíquiques, espirituals, estètiques, artístiques, sensibles, relacionals…en harmonia amb la natura, per sobre de qualsevol creixement desequilibrat econòmic, financer, bèl·lic, consumista, transgènic i anòmal com el que s’està propugnant en nom d’un desenvolupament “fictici”.</li>
						<li class="mt-4">La compenetració amb la Natura.</li>
						<li class="mt-4">La solidaritat humana y econòmica com a principi de les nostres relacions locals, nacionals i internacionals.</li>
					</ul>

					<p>Hi ha uns principis a la Carta de la Economia Solidaria i en base a aquest posicionament, objectius i valors, s’ha redactat la Carta de Principis de la Economia Solidaria, espina dorsal i element identitari de la Economia Solidaria. Els principis que recull son els següents:</p>
					<p class="ml-30"><span style="color: #631d76;"><b>PRINCIPI DE EQUITAT</b></span> Introdueix un principi d’igualtat. Es un valor que reconeix a totes les persones com a subjectes de igual dignitat, i protegeix el seu dret a no estar sotmeses a relacions basades en la dominació sigui la que sigui la seva condició social, gènere, edat, ètnia, origen, capacitat, etc.<br>Una societat més justa es aquella en la que totes les persones es reconeixen mútuament com a iguals en drets i possibilitats, i te en compte les diferències existents entre les persones i els grups. Por això s’ha de satisfer de manera equitativa els interessos respectius de totes les persones.<br>La igualtat és un objectiu social essencial allà on la seva absència comporta un dèficit de dignitat. Quan es vincula amb el reconeixement i respecte a la diferència, ho denominem <span style="color: #631d76;"><b>“equitat”</b></span>
					<p class="ml-30"><span style="color: #631d76;"><b>PRINCIPI DE TREBALL</b></span> Considerem que el treball és un element clau en la qualitat de vida de les persones, de la comunitat i de les relacions econòmiques entre la ciutadania, els pobles i els Estats. Per això situem la concepció del treball en un context social i institucional ampli de participació en la economia i en la comunitat.<br>Afirmem la importància de recuperar la dimensió humana, social, política, econòmica i cultural del treball que permeti el desenvolupament de les capacitats de les persones, produint béns i serveis, per a satisfer les veritables necessitats de la població (nostres, de nostre entorn immediat i de la comunitat en general). Per això per a nosaltres el treball és molt més que una feina o una ocupació.</p>
					<p class="ml-30"><span style="color: #631d76;"><b>PRINCIPI DE SOSTENIBILITAT AMBIENTAL</b></span> Considerem que <b>tota la nostra activitat productiva i econòmica està relacionada amb la natura</b>, és per això la nostra aliança amb ella i el reconeixement dels seus drets son el nostre punt de partida.<br>Realment creiem que <b>la nostra bona relació amb la Natura és una font de riquesa econòmica, i de bona salut per a tots</b>. D’aquí la necessitat fonamental de integrar la sostenibilitat ambiental en totes les nostres accions, avaluant el nostre impacte ambiental (petjada ecològica) de manera permanent.<br>Volem <b>reduir significativament l’empremta ecològica humana</b> en totes les nostres activitats, avançant cap a formes sostenibles y equitatives de producció i consum, promovent una ètica de la suficiència i de la austeritat.</p>
					<p class="ml-30"><span style="color: #631d76;"><b>PRINCIPI DE COOPERACIÓ</b></span> Volem <b>afavorir la cooperació enlloc de la competència</b>, dins i fora de les nostres organitzacions, cercant la col·laboració amb altres entitats i organismes públics i privats…<br>Pretenem construir col·lectivament un model de societat basada en el desenvolupament local harmònic, <b>les relacions comercials justes, la igualtat, la confiança, la coresponsabilitat, la transparència, el respecte…</b><br>Partim de que la Economia Solidària està basada en una ètica participativa y democràtica, que vol <b>fomentar l’aprenentatge i el treball cooperatiu entre persones i organitzacions</b>, mitjançant processos de col·laboració, de presa de decisions conjuntes, d’assumpció compartida de responsabilitats i deures, que garanteixin la màxima horitzontalitat possible a la vegada que respecten la autonomia de cada una, sense generar dependències.<br>Entenem que aquests processos de cooperació han de estendre’s a tots els àmbits: local, regional o autonòmic, estatal i internacional i han d’articular-se en xarxes on es visqui i es fomenti aquests valors …</p>
					<p class="ml-30"><i><span style="color: #631d76;"><b>PRINCIPI DE FI “NO-LUCRATIU”</b></span>” El model econòmic que practiquem te com a finalitat el desenvolupament integral, col·lectiu i individual de les persones, i com a medi, la gestió eficient de projectes econòmicament viables, sostenibles i integralment rentables, els beneficis dels quals es reinvertiran i redistribuiran.<br>Aquesta “no–lucrativitat”, està íntimament unida a la nostra forma de mesurar els balanços de resultats, que tenen en compte no només els aspectes econòmics, si no també els humans, socials, mediambientals, culturals i participatius i el resultat final és el benefici integral.<br>Les nostres activitats destinen part dels beneficis a un projecte social i solidari o a iniciatives solidàries d’interès general, participant d’aquesta manera en la construcció d’un model social més humà, solidari i equitatiu.</i></p>
					<p class="ml-30"><span style="color: #631d76;"><b>PRINCIPI DE COMPROMIS AMB L’ENTORN</b></span> El nostre compromís amb l’entorn es concreta en la participació, en el desenvolupament local sostenible i comunitari del territori.<br>La nostra organització està plenament integrada al territori i a l’entorn social en el que desenvolupem la nostra activitat, això exigeix la implicació en xarxes i la cooperació amb altres organitzacions del teixit social y econòmic proper, dins del mateix àmbit geogràfica.<br><br>Entenem aquesta col·laboració com un camí, per a que experiències positives i solidàries concretes puguin generar processos de transformació de les estructures generadores de desigualtat, dominació i exclusió.<br><br>El nostre compromís en l’àmbit local ens aboca a articular-nos en dimensions més àmplies per a buscar solucions més globals, interpretant la necessitat de transitar contínuament entre el més petit i el més gran, el local i el global.</p>

				</div>
			</section>
			<!-- / -->

			<section class="dark-2 b-0">
				<div class="container">

					<h2 class="fw-400">Escull un <strong>Projecte Solidari</strong> per col·laborar</h2>
					<!--
					<div class="row category-grid">

						<div class="col-md-4 col-sm-6 col-12">
							<div class="card">

								<a href="portfolio-single-project.html">
									<img class="card-img-top" src="http://lorempixel.com/400/200/sports/" alt="">
								</a>

								<div class="card-block">
									<a href="portfolio-single-project.html" class="fs-20 mb-20 block">Project Name</a>
									<p class="fs-15 mb-20">We put in 40 hours of research and testing, and the clear, lifelike sound of the Pioneer SP-PK22BS makes it the best inexpensive surround speaker system.</p>
									<a href="portfolio-single-project.html" class="text-muted fs-15">VIEW PROJECT</a>
								</div>

							</div>
						</div>

						<div class="col-md-4 col-sm-6 col-12">
							<div class="card">

								<a href="portfolio-single-project.html">
									<img class="card-img-top" src="http://lorempixel.com/400/200/people/" alt="">
								</a>

								<div class="card-block">
									<a href="portfolio-single-project.html" class="fs-20 mb-20 block">Project Name</a>
									<p class="fs-15 mb-20">We put in 40 hours of research and testing, and the clear, lifelike sound of the Pioneer SP-PK22BS makes it the best inexpensive surround speaker system.</p>
									<a href="portfolio-single-project.html" class="text-muted fs-15">VIEW PROJECT</a>
								</div>

							</div>
						</div>

						<div class="col-md-4 col-sm-6 col-12">
							<div class="card">

								<a href="portfolio-single-project.html">
									<img class="card-img-top" src="http://lorempixel.com/400/200/nature/" alt="">
								</a>

								<div class="card-block">
									<a href="portfolio-single-project.html" class="fs-20 mb-20 block">Project Name</a>
									<p class="fs-15 mb-20">We put in 40 hours of research and testing, and the clear, lifelike sound of the Pioneer SP-PK22BS makes it the best inexpensive surround speaker system.</p>
									<a href="portfolio-single-project.html" class="text-muted fs-15">VIEW PROJECT</a>
								</div>

							</div>
						</div>
					</div>
					-->
				</div>
			</section>






			<!-- FOOTER -->
			<?php
			include_once("php/pie.php");
			?>
			<!-- /FOOTER -->

	</div>
	<!-- /wrapper -->


	<!-- JAVASCRIPT FILES -->
	<script>
		var plugin_path = 'plugins/';
	</script>
	<script src="/web/js/jquery-3.3.1.min.js"></script>

	<script src="/web/js/scripts.js"></script>
</body>

</html>
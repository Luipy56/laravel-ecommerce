<?php
session_start();
$PaginaActual = "blog";
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION["Idioma"] ?>">
	<head>
		<meta charset="utf-8" />
		<title>Parts d'un pany</title>
		<meta name="description" content="" />
		<meta name="Author" content="Dorin Grigoras [www.stepofweb.com]" />
		<link rel="icon" type="image/png" href="/images/favicon.png">

		<!-- mobile settings -->
		<meta name="viewport" content="width=device-width, maximum-scale=1, initial-scale=1, user-scalable=0" />
		<!--[if IE]><meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'><![endif]-->

		<!-- WEB FONTS : use %7C instead of | (pipe) -->
		<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600%7CRaleway:300,400,500,600,700%7CLato:300,400,400italic,600,700" rel="stylesheet" type="text/css" />

		<!-- CORE CSS -->
		<link href="/css/bootstrap.min.css" rel="stylesheet" type="text/css" />

		<!-- THEME CSS -->
		<link href="/css/essentials.css" rel="stylesheet" type="text/css" />
		<link href="/css/layout.css?v=1.1" rel="stylesheet" type="text/css" />

		<!-- PAGE LEVEL SCRIPTS -->
		<link href="/css/header-1.css" rel="stylesheet" type="text/css" />
		<link href="/css/orange.css" rel="stylesheet" type="text/css" id="color_scheme" />
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

			<!-- 
				AVAILABLE HEADER CLASSES

				Default nav height: 96px
				.header-md 		= 70px nav height
				.header-sm 		= 60px nav height

				.b-0 		= remove bottom border (only with transparent use)
				.transparent	= transparent header
				.translucent	= translucent header
				.sticky			= sticky header
				.static			= static header
				.dark			= dark header
				.bottom			= header on bottom
				
				shadow-before-1 = shadow 1 header top
				shadow-after-1 	= shadow 1 header bottom
				shadow-before-2 = shadow 2 header top
				shadow-after-2 	= shadow 2 header bottom
				shadow-before-3 = shadow 3 header top
				shadow-after-3 	= shadow 3 header bottom

				.clearfix		= required for mobile menu, do not remove!

				Example Usage:  class="clearfix sticky header-sm transparent b-0"
			-->
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

					<small>El blog de la Serralleria</small>
					<h2 class="fs-40 fw-300">MESTREJAMENTS</h2>

				</div>
			</section>



			<!-- -->
			<section>
				<div class="container">

					<h1 class="blog-post-title">MESTREJAMENTS</h1>
					<ul class="blog-post-info list-inline">
						<li>
							<a href="#">
								<i class="fa fa-clock-o"></i> 
								<span class="font-lato">juny de 2021</span>
							</a>
						</li>
						<li>
							<i class="fa fa-folder-open-o"></i> 

							<a class="category" href="#">
								<span class="font-lato">Articles</span>
							</a>
							<a class="category" href="#">
								<span class="font-lato">Mestrejaments</span>
							</a>
						</li>
						<li>
							<a href="#">
								<i class="fa fa-user"></i> 
								<span class="font-lato">J.J. Gutiérrez</span>
							</a>
						</li>
					</ul>

					<row>
						<p>El mestrejament és una tècnica per la qual es pot tenir accés a diferents portes amb una mateixa clau, seguint una jerarquia i impedint que una clau obri una porta que no hauria. La famosa “clau mestra” és la base d’aquesta explicació. La reducció de claus és un objectiu molt comú. Quantes claus portes ara mateix a sobre? No guanyaries temps si no haguessis de cercar quina clau obre què?</p>
					</row>
						
					<row>
						<p>Quan pensem en personal de manteniment, neteja, seguretat... i ens fem una imatge mental, veiem que sempre duen un manyac de claus a sobre i sembla que tinguin el poder d’obrir qualsevol porta, i la veritat es que cada vegada que han de fer servir una clau perden molt de temps en trobar quina clau obre que. Ara imagineu que aquella pinya de claus es transforma en UNA SOLA!!!!!!. Això és ben be el que és un mestrejament.</p>
					</row>
						

					<row>
						<p>Una empresa, una comunitat de veïns, una escola, inclús una casa particular es pot beneficiar del mestrejament. Qualsevol persona te una clau per la porteria de casa, una per la bústia, un altre pel pàrquing, una pel traster, una per la porta del pis i un altre diferent pel segon pany (per evitar que qui tingui la clau de casa pugui entrar si cal), més una clau per la caixa de seguretat, en total SET claus. Ara imagineu que la mateixa persona pot obrir totes les portes amb <b>una sola clau!!!!</b></p>
					</row>
						

					<row>
						<p>Tot i que es pugui pensar que el mestrejament no és segur, la veritat és que és tot el contrari, sempre que es faci amb material de qualitat. Si realitzem un mestrejament amb una clau senzilla, podríem trobar-nos que pel desgast, per la erosió o pel pas del temps, una clau obri una porta que no hauria d’obrir. En canvi si realitzem el mestrejament amb un cilindre de qualitat, hi ha una normativa europea (VdS 2344) que impedeix que aquest fet passi, garantint en tot moment que CAP CLAU obre un pany que NO TINGUI AUTORITZAT.</p>
					</row>
						

					<row>
						<p>El més important és realitzar un bon <i>pla de mestrejament</i>, que cobreixi les necessitats del edifici i de les persones, claus comunes, claus de servei, claus de nens, claus de familiars... Determinem una jerarquia i realitzem la feina de planificació. Així el director general de la companyia obrirà TOTES LES PORTES amb una sola clau, i l’encarregat de la piscina només els accessos merament necessaris per realitzar la seva tasca, tots dos poden obrir la porta d’entrada amb una clau, però cada clau té un “PASE” per cada porta.</p>
					</row>
						

					<row>
						<p>Per entendre-ho millor, podríem comparar-ho amb <i>“SIRI”</i> només la veu programada pot donar ordres, si jo soc a casa d’un amic per molt que li doni ordres no em farà cas, només la veu del meu amic està autoritzada per <i>“SIRI”</i>. La clau del veí del 4rt 1ª és igual NOMÉS obre el 4rt 1ª, i no el 4rt 2ª, en canvi la clau de la porteria la pot obrir el 4rt 1ª i el 4rt 2ª.</p>
					</row>
						

					<row>
						<p>Els mestrejaments eviten també l’intercanvi de claus, ja que la meva clau obre una sèrie de panys i la del meu veí obre altres, així que no ens podem intercanviar les claus. També l’estalvi de temps, que quan tens pressa és un mal aliat, i no trobar la clau ens produeix més ansietat. Un conserge amb una sola clau? És una realitat amb els mestrejaments de Serralleria Solidària.</p>
					</row>
						

					<div class="heading-title heading-dotted text-center">
						<h4>Preguntes freqüents <span>FAQS</span></span></h4>
					</div>

					<div class="heading-title heading-border">
						<h4>Cada porta pot tenir una clau independentment del mestrejament?</h4>
						<p class="font-lato fs-16">Si, dins el pla de mestrejament, es pot atorgar la jerarquia a cada porta per que tingui una clau.</p>
					</div>

					<div class="heading-title heading-border">
						<h4>Es pot realitzar un mestrejament a la porta que ja tinc a casa meva?</h4>
						<p class="font-lato fs-16">No, el pla de mestrejament s’ha de fer sempre ABANS, de qualsevol instal·lació, per poder registrar, la combinació i les posicions i assegurar-nos de que aquella clau no existeix.</p>
					</div>

					<div class="heading-title heading-border">
						<h4>Què la mateixa clau que obre la porteria obri casa meva és segur</h4>
						<p class="font-lato fs-16">SI, i molt, la certificació VdS 2344 obliga a que cada clau obri les portes a les que correspon, sense excepcions, per ajudar-nos a entendre-ho, hi ha gent que qualifica el pany de casa com a potent i el de la comunitat menys potent, per això totes les claus de tots els veïns poden obrir la porta de la porteria.</p>
					</div>


					<!-- SHARE POST -->
					<div class="clearfix mt-30">

						<span class="float-left mt-6 bold hidden-xs-down">
							Compartir Post: 
						</span>

						<a href="#" class="social-icon social-icon-sm social-icon-transparent social-instagram float-right" data-toggle="tooltip" data-placement="top" title="Instagram">
							<i class="icon-instagram"></i>
							<i class="icon-instagram"></i>
						</a>

						<a href="#" class="social-icon social-icon-sm social-icon-transparent social-twitter float-right" data-toggle="tooltip" data-placement="top" title="Twitter">
							<i class="icon-twitter"></i>
							<i class="icon-twitter"></i>
						</a>

						<a href="#" class="social-icon social-icon-sm social-icon-transparent social-facebook float-right" data-toggle="tooltip" data-placement="top" title="Facebook">
							<i class="icon-facebook"></i>
							<i class="icon-facebook"></i>
						</a>

					</div>
					<!-- /SHARE POST -->


					<div class="divider"><!-- divider --></div>

					
					<ul class="pager">
						<li class="previous"><a class="b-0" href="blog-tipus-de-claus.php">&larr; Post anterior</a></li>
						<li class="next"><a class="b-0" href="blog-parts-d-un-pany.php">Post següent &rarr;</a></li>
					</ul>


<?php
if (0 == 1) {
?>
					<div class="divider"><!-- divider --></div>

					
					<!-- COMMENTS -->
					<div id="comments" class="comments">

						<h4 class="page-header mb-60 fs-20">
							<span>3</span> COMMENTS
						</h4>

						<!-- comment item -->
						<div class="comment-item">

							<!-- user-avatar -->
							<span class="user-avatar">
								<img class="float-left media-object" src="assets/images/_smarty/avatar.png" width="64" height="64" alt="">
							</span>

							<div class="media-body">
								<a href="#commentForm" class="scrollTo comment-reply">reply</a>
								<h4 class="media-heading bold">John Doe</h4>
								<small class="block">June 29, 2014 - 11:23</small>
								Proin eget tortor risus. Cras ultricies ligula sed magna dictum porta. Pellentesque in ipsum id orci porta dapibus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
							</div>
						</div>

						<!-- comment item -->
						<div class="comment-item">

							<!-- user-avatar -->
							<span class="user-avatar">
								<img class="float-left media-object" src="assets/images/_smarty/avatar.png" width="64" height="64" alt="">
							</span>

							<div class="media-body">
								<a href="#commentForm" class="scrollTo comment-reply">reply</a>
								<h4 class="media-heading bold">Diana Doe</h4>
								<small class="block">June 29, 2014 - 11:23</small>
								Proin eget tortor risus. Cras ultricies ligula sed magna dictum porta. Pellentesque in ipsum id orci porta dapibus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
							</div>
						</div>

						<!-- comment item -->
						<div class="comment-item">

							<!-- user-avatar -->
							<span class="user-avatar">
								<img class="media-object" src="assets/images/_smarty/avatar.png" width="64" height="64" alt="">
							</span>

							<div class="media-body">
								<a href="#commentForm" class="scrollTo comment-reply">reply</a>
								<h4 class="media-heading bold">Melissa Doe</h4>
								<small class="block">June 29, 2014 - 11:23</small>
								Proin eget tortor risus. Cras ultricies ligula sed magna dictum porta. Pellentesque in ipsum id orci porta dapibus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. 

								<!-- comment reply -->
								<div class="media">

									<!-- user-avatar -->
									<span class="user-avatar">
										<img class="media-object" src="assets/images/_smarty/avatar.png" width="64" height="64" alt="">
									</span>

									<div class="media-body">
										<h4 class="media-heading bold">Peter Doe</h4>
										<small class="block">June 29, 2014 - 11:23</small>
										Proin eget tortor risus. Cras ultricies ligula sed magna dictum porta. Pellentesque in ipsum id orci porta dapibus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
									</div>
								</div>

							</div>
						</div>



						<h4 class="page-header fs-20 mb-60 mt-100">
							LEAVE A <span>COMMENT</span>
						</h4>

								<!-- Form -->
								<form action="#" method="post">

									<div class="row">
										<div class="col-md-4">
											<label>NAME</label>
											<input required="required" type="text" value="" maxlength="100" class="form-control form-control-lg" name="author" id="author">
										</div>
										<div class="col-md-4">
											<label>EMAIL</label>
											<input required="required" type="email" value="" maxlength="100" class="form-control form-control-lg" name="contact_email" id="contact_email">
										</div>
										<div class="col-md-4">
											<label>WEBSITE</label>
											<input type="email" value="" maxlength="100" class="form-control form-control-lg" name="url" id="url">
										</div>
									</div>

									<div class="row">
										<div class="col-md-12">
											<label>COMMENT</label>
											<textarea required="required" maxlength="5000" rows="5" class="form-control" name="comment" id="comment"></textarea>
										</div>
									</div>

									<div class="row">
										<div class="col-md-12">

											<button class="btn btn-3d btn-lg btn-reveal btn-black">
												<i class="fa fa-check"></i>
												<span>SUBMIT MESSAGE</span>
											</button>

										</div>
									</div>

								</form>
								<!-- /Form -->

					</div>
					<!-- /COMMENTS -->
<?php
}
?>


				</div>
			</section>
			<!-- / -->

					

			<!-- FOOTER -->
<?php 
include_once("php/pie.php");
?>
			<!-- /FOOTER -->

		</div>
		<!-- JAVASCRIPT FILES -->
		<script>var plugin_path = 'plugins/';</script>
		<script src="/js/jquery-3.3.1.min.js"></script>

		<script src="/js/scripts.js"></script>
	</body>
</html>
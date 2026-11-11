<?php
session_start();
$PaginaActual = "blog";
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION["Idioma"] ?>">
	<head>
		<meta charset="utf-8" />
		<title>Tipus de claus</title>
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
					<h2 class="fs-40 fw-300">TIPUS DE CLAUS</h2>

				</div>
			</section>



			<!-- -->
			<section>
				<div class="container">

					<h1 class="blog-post-title">TIPUS DE CLAUS</h1>
					<ul class="blog-post-info list-inline">
						<li>
							<a href="#">
								<i class="fa fa-clock-o"></i> 
								<span class="font-lato">abril de 2021</span>
							</a>
						</li>
						<li>
							<i class="fa fa-folder-open-o"></i> 

							<a class="category" href="#">
								<span class="font-lato">Articles</span>
							</a>
							<a class="category" href="#">
								<span class="font-lato">Claus</span>
							</a>
						</li>
						<li>
							<a href="#">
								<i class="fa fa-user"></i> 
								<span class="font-lato">J.J. Gutiérrez</span>
							</a>
						</li>
					</ul>

					<p><h3>Claus clàssiques.</h3></p>

					<!-- article content -->

					<div>
						<img style="height:50%; width:50%;" src="/web/images/blog/blog_tipus_de_claus/Claus-classiques.png" alt="Claus classiques" />
					</div>
					<p>Aquest tipus de clau no es fan servir gairebé res, aquests panys son grans i gens còmodes, son claus que es feien servir fa molt de temps, encara es conserven en pisos antics o edificis amb historia.</p>

					<p><h3>Claus de serreta.</h3></p>

					<!-- article content -->

					<div>
						<img style="height:15%; width:15%;" src="/web/images/blog/blog_tipus_de_claus/Clau-serreta.png" alt="Claus serreta" />
					</div>
					<p>També coneguda com a clau de serra. A dia d’avui és una varietat de clau molt comuna, tant a casa com en botigues. Aquesta clau te un costat dentat, amb una combinació simple per poder obrir la porta. No ofereix gaire seguretat, es pot obrir amb rossinyols (ganzúas) amb relativa facilitat o amb el mètode “bumping”.</p>
					
					<p><h3>Claus de seguretat</h3></p>

					<!-- article content -->

					<div>
						<img style="height:15%; width:15%;" src="/web/images/blog/blog_tipus_de_claus/Claus-de-seguretat.png" alt="Claus de seguretat" />
					</div>
					<p>Aquesta opció es fa servir molt en portes blindades, té els costats rectes i petits forats per la combinació al recorregut de la pala de la clau. Son senzilles i comuns, pel que segurament en tinguis una al teu clauer. Són una mica més segures que les de serreta, però no es poden considerar de “seguretat”.</p>
					
					<p><h3>Claus de paleta, paletó o doble pala</h3></p>

					<!-- article content -->

					<div>
						<img style="height:15%; width:15%;" src="/web/images/blog/blog_tipus_de_claus/Claus-de-paleta.png" alt="Claus de paleta, paletó o doble pala" />
					</div>
					<p>Aquesta varietat de claus es fa servir en molts tipus de porta, fa uns anys es van posar a les portes després de un bon resultat a caixes fortes, que encara es fan servir, ja no són tan segures donat que hi ha una eina de manyà que les pot obrir.<br><br>Hi ha una espiga cilíndrica al centre de la clau i una dent que surt pels laterals. A aquestes portes no podràs veure el bombí, ja que funciona amb gorges a l’interior del pany, només podràs veure el forat per on fiquem la clau.</p>
					
					<p><h3>Clau de pompa</h3></p>

					<!-- article content -->

					<div>
						<img style="height:15%; width:15%;" src="/web/images/blog/blog_tipus_de_claus/Clau-de-pompa.png" alt="Clau de pompa" />
					</div>
					<p>Son molt semblants a les claus de paleta, però amb les dents cap a davant i no als costats, aquest tipus de clau destaca por tenir una molla frontal que fa que el pany esculpeixi la clau i calgui una pressió frontal per poder fer-lo servir.</p>
					
					<p><h3>Claus de gorges</h3></p>

					<!-- article content -->

					<div>
						<img style="height:15%; width:15%;" src="/web/images/blog/blog_tipus_de_claus/Claus-de-gorges.png" alt="Claus de gorges" />
					</div>
					<p>Aquestes claus son molt usuals, tenen una tija i al final les dents cap abaix, són els panys preferits pels fusters a l’hora de canviar la porta de casa, hi ha una gran quantitat instal·lats, no són segurs i es recomana fer alguna actuació per assegurar casa nostra. Últimament hi ha molts robatoris a llars amb aquest pany donat que poden entrar sense forçament ni trencar res.</p>
					
					<p><h3>Claus cruciformes</h3></p>

					<!-- article content -->

					<div>
						<img style="height:15%; width:15%;" src="/web/images/blog/blog_tipus_de_claus/Claus-cruciformes.png" alt="Claus cruciformes" />
					</div>
					<p>Són claus difícils de veure, amb una espiga cilíndrica al centre i acostumen a ser llargues. Depenent del model pot tenir més o menys dents, tenen una estructura en forma de creu. Son comunes en negocis i caixes fortes. Te combinació a les quatre parts de la clau, hi ha dificultat en saber quin és l’encarament correcte.</p>
					
					<p><h3>Claus d'alta seguretat</h3></p>

					<!-- article content -->

					<div>
						<img style="height:50%; width:50%;" src="/web/images/blog/blog_tipus_de_claus/Claus-de-alta-seguretat.png" alt="Claus d'alta seguretat" />
					</div>
					<p>Són les claus més segures del mercat, hi ha poques marques que es puguin considerar segures, les podem identificar per que superen normatives i estan sotmeses a proves, això ens dona seguretat, acostumen a tenir molts elements diferencials, elements mòbils, magnètics, moltes opcions de combinatòria, elements anti-bumping, gruixos anti-trepants, reforços anti-trencament.<br><br>Aquest són els tipus de claus més habituals, existeixen altres no tan comuns que si veieu a bé en podem fer un altre entrada més exhaustiva. Hi ha la clau electrònica i els sistemes d’accés biomètrics que existeixen però encara són poc utilitzats al dia a dia.</p>
					

					<div class="heading-title heading-dotted text-center">
						<h4>Preguntes freqüents <span>FAQS</span></span></h4>
					</div>

					<div class="heading-title heading-border">
						<h4>Si tinc una clau que no és segura, he de canviar el pany?</h4>
						<p class="font-lato fs-16">Quan tenim identificada una mancança de seguretat cal tenir coneixement de quin nivell de seguretat volem a casa, és molt recomanable demanar opinió a un professional per mirar quina és la millor solució.</p>
					</div>

					<div class="heading-title heading-border">
						<h4>He vist que la meva clau no és segura, m’entraran a robar?</h4>
						<p class="font-lato fs-16">Quan parlem de que la clau no és segura no vol dir que ens entraran a casa, parlem de possibilitats, de que tenim més o menys números de que ens puguem trobar sorpreses desagradables, cal saber que tenim i que volem en qüestió de seguretat.</p>
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
						<li class="previous"><a class="b-0" href="blog-parts-d-un-pany.php">&larr; Post anterior</a></li>
						<li class="next"><a class="b-0" href="blog-mestrejaments.php">Post següent&rarr;</a></li>
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
		<script src="/web/js/jquery-3.3.1.min.js"></script>

		<script src="/web/js/scripts.js"></script>
	</body>
</html>
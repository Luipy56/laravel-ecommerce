<?php
if (!isset($_SESSION["Idioma"])) {
	$_SESSION["Idioma"] = "ca";
}
?>
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
<div id="header" class="navbar-toggleable-md sticky clearfix">

	<!-- TOP NAV -->
	<header id="topNav">
		<div class="container">

			<!-- Mobile Menu Button -->
			<button class="btn btn-mobile" data-toggle="collapse" data-target=".nav-main-collapse">
				<i class="fa fa-bars"></i>
			</button>

			<!-- BUTTONS -->
			<ul class="float-right nav nav-pills nav-second-main d-none">

				<!-- SEARCH -->
				<li class="search">
					<a href="javascript:;">
						<i class="fa fa-search"></i>
					</a>
					<div class="search-box">
						<form action="page-search-result-1.html" method="get">
							<div class="input-group">
								<input type="text" name="src" placeholder="Buscar" class="form-control" />
								<span class="input-group-btn">
									<button class="btn btn-primary" type="submit">Buscar</button>
								</span>
							</div>
						</form>
					</div>
				</li>
				<!-- /SEARCH -->

			</ul>
			<!-- /BUTTONS -->


			<!-- Logo -->
			<a class="logo float-left" href="index.php">
				<img src="/web/images/logoweb.png" alt="" />
			</a>

			<!-- 
							Top Nav 
							
							AVAILABLE CLASSES:
							submenu-dark = dark sub menu
						-->
			<div class="navbar-collapse collapse float-right nav-main-collapse">
				<nav class="nav-main">

					<!--
									NOTE
									
									For a regular link, remove "dropdown" class from LI tag and "dropdown-toggle" class from the href.
									Direct Link Example: 

									<li>
										<a href="#">HOME</a>
									</li>
								-->
					<ul id="topMain" class="nav nav-pills nav-main">
						<li <?php echo ($PaginaActual == "index") ? 'class="active"' : ""; ?>>
							<a <?php echo ($PaginaActual == "index") ? 'class="active"' : ""; ?> href="index.php">INICI</a>
						</li>
						<li <?php echo ($PaginaActual == "nosaltres") ? 'class="active"' : ""; ?>>
							<a <?php echo ($PaginaActual == "nosaltres") ? 'class="active"' : ""; ?> href="nosaltres.php">QUI SOM</a>
						</li>
						<li <?php echo ($PaginaActual == "serveis") ? 'class="active"' : ""; ?>>
							<a <?php echo ($PaginaActual == "serveis") ? 'class="active"' : ""; ?> href="serveis.php">SERVEIS</a>
						</li>
						<li <?php echo ($PaginaActual == "solidaritat") ? 'class="active"' : ""; ?>>
							<a <?php echo ($PaginaActual == "solidaritat") ? 'class="active"' : ""; ?> href="solidaritat.php">SOLIDARITAT</a>
						</li>
						<li <?php echo ($PaginaActual == "webs") ? 'class="active"' : ""; ?>>
							<a <?php echo ($PaginaActual == "webs") ? 'class="active"' : ""; ?> href="webs.php">WEBS D'INTERÈS</a>
						</li>
						<li <?php echo ($PaginaActual == "contacte") ? 'class="active"' : ""; ?>>
							<a <?php echo ($PaginaActual == "contacte") ? 'class="active"' : ""; ?> href="contacte.php">CONTACTE</a>
						</li>
						<li <?php echo ($PaginaActual == "blog") ? 'class="active"' : ""; ?>>
							<a <?php echo ($PaginaActual == "blog") ? 'class="active"' : ""; ?> href="blog.php">BLOG</a>
						</li>
						<li>
							<a href="/">BOTIGA</a>
						</li>
						<li>
							<a class="navbar-brand" href="https://api.whatsapp.com/send?phone=+34600500517&text=Hola,%20voldria%20informació%20sobre%20els%20vostres%20serveis" target="_blank"><i class="fa fa-whatsapp fa-2x whatsapp-icon" title="Envia'ns un whatsapp"></i></a>
						</li>
					</ul>

				</nav>
			</div>
		</div>
	</header>
	<!-- /Top Nav -->

</div>
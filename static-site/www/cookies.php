<?php
session_start();
$PaginaActual = "cookies";
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION["Idioma"] ?>">

<head>
    <meta charset="utf-8" />
    <title>Cookies</title>
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

    <!-- REVOLUTION SLIDER -->
    <link href="/web/css/slider.revolution/extralayers.css" rel="stylesheet" type="text/css" />
    <link href="/web/css/slider.revolution/settings.css" rel="stylesheet" type="text/css" />

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

        <section class="header-solidaritat page-header page-header-xlg b-0 dark" data-background-delay="5000" data-background-fade="1000" data-background="
					demo_files/images/packs/photography/cover/sean-mcauliffe-12854-min.jpg
					,demo_files/images/packs/photography/cover/jorge-rojas-182706-min.jpg
			">

            <span class="overlay dark-4">
                <!-- dark overlay [1 to 9 opacity] -->
            </span>
            <div class="container">

                <small>Informació l'us de cookies de la</small>
                <h2 class="fs-40 fw-300">SERRALLERIA</h2>

            </div>
        </section>

        <section class="section-sm b-0">
            <div class="container">

                <div class="row">
                    <div class="col-md-12">

                        <b>
                            <h4>Política de Cookies</h4>
                        </b>

                        <p>En esta web se utilizan cookies de terceros y propias para conseguir que tengas una mejor experiencia de navegación, puedas compartir contenido en redes sociales y para que podamos obtener estadísticas de los usuarios.</p>

                        <p>Puedes evitar la descarga de cookies a través de la configuración de tu navegador, evitando que las cookies se almacenen en su dispositivo.</p>

                        <p>Como propietario de este sitio web, te comunico que no utilizamos ninguna información personal procedente de cookies, tan sólo realizamos estadísticas generales de visitas que no suponen ninguna información personal.</p>

                        <p>Es muy importante que leas la presente política de cookies y comprendas que, si continúas navegando, consideraremos que aceptas su uso.</p>

                        <p>Según los términos incluidos en el artículo 22.2 de la Ley 34/2002 de Servicios de la Sociedad de la Información y Comercio Electrónico, si continúas navegando, estarás prestando tu consentimiento para el empleo de los referidos mecanismos.</p>

                        <b>
                            <h4>Entidad Responsable</h4>
                        </b>

                        <p>La entidad responsable de la recogida, procesamiento y utilización de tus datos personales, en el sentido establecido por la Ley de Protección de Datos Personales es la página Serralleria Solidaria, propiedad de Juan José Gutiérrez – Calle Diputació, 426, 4rt 2ª, C.P. 08013 Barcelona.</p>

                        <b>
                            <h4>¿Qué son las cookies?</h4>
                        </b>

                        <p>Las cookies son un conjunto de datos que un servidor deposita en el navegador del usuario para recoger la información de registro estándar de Internet y la información del comportamiento de los visitantes en un sitio web. Es decir, se trata de pequeños archivos de texto que quedan almacenados en el disco duro del ordenador y que sirven para identificar al usuario cuando se conecta nuevamente al sitio web. Su objetivo es registrar la visita del usuario y guardar cierta información. Su uso es común y frecuente en la web ya que permite a las páginas funcionar de manera más eficiente y conseguir una mayor personalización y análisis sobre el comportamiento del usuario.</p>

                        <b>
                            <h4>¿Qué tipos de cookies existen?</h4>
                        </b>

                        <p>Las cookies utilizadas en nuestro sitio web, son de sesión y de terceros, y nos permiten almacenar y acceder a información relativa al idioma, el tipo de navegador utilizado, y otras características generales predefinidas por el usuario, así como, seguir y analizar la actividad que lleva a cabo, con el objeto de introducir mejoras y prestar nuestros servicios de una manera más eficiente y personalizada.</p>

                        <p>Las cookies, en función de su permanencia, pueden dividirse en cookies de sesión o permanentes. Las que expiran cuando el usuario cierra el navegador. Las que expiran en función de cuando se cumpla el objetivo para el que sirven (por ejemplo, para que el usuario se mantenga identificado en los servicios de Juan José Gutiérrez) o bien cuando se borran manualmente.</p>


                        <table border="0" width="100%" cellpadding="15">
                            <tbody>
                                <tr>
                                    <td>Nombre</td>
                                    <td>Tipo</td>
                                    <td>Caducidad</td>
                                    <td>Finalidad</td>
                                    <td>Clase</td>
                                </tr>
                                <tr>
                                    <td>__utma</td>
                                    <td>De Terceros (Google Analytics)</td>
                                    <td>2 años</td>
                                    <td>Se usa para distinguir usuarios y sesiones.</td>
                                    <td>No Exenta</td>
                                </tr>
                                <tr>
                                    <td>__utmb</td>
                                    <td>De Terceros (Google Analytics)</td>
                                    <td>30 minutos</td>
                                    <td>Se usa para determinar nuevas sesiones o visitas</td>
                                    <td>No Exenta</td>
                                </tr>
                                <tr>
                                    <td>__utmc</td>
                                    <td>De Terceros (Google Analytics)</td>
                                    <td>Al finalizar la sesión</td>
                                    <td>Se configura para su uso con Urchin</td>
                                    <td>No Exenta</td>
                                </tr>
                                <tr>
                                    <td>__utmz</td>
                                    <td>De Terceros (Google Analytics)</td>
                                    <td>6 meses</td>
                                    <td>Almacena el origen o la campaña que explica cómo el usuario ha llegado hasta la página web</td>
                                    <td>No Exenta</td>
                                </tr>
                            </tbody>
                        </table>

                        <p>Adicionalmente, en función de su objetivo, las cookies pueden clasificarse de la siguiente forma:</p>

                        <b>
                            <h4>Cookies de rendimiento</h4>
                        </b>

                        <p>Este tipo de Cookie recuerda sus preferencias para las herramientas que se encuentran en los servicios, por lo que no tiene que volver a configurar el servicio cada vez que usted visita. A modo de ejemplo, en esta tipología se incluyen: Ajustes de volumen de reproductores de vídeo o sonido. Las velocidades de transmisión de vídeo que sean compatibles con su navegador. Los objetos guardados en el “carrito de la compra” en los servicios de e-commerce tales como tiendas.</p>

                        <b>
                            <h4>Cookies de geo-localización</h4>
                        </b>

                        <p>Estas cookies son utilizadas para averiguar en qué país se encuentra cuando se solicita un servicio. Esta cookie es totalmente anónima, y sólo se utiliza para ayudar a orientar el contenido a su ubicación.</p>

                        <b>
                            <h4>Cookies de registro</h4>
                        </b>

                        <p>Las cookies de registro se generan una vez que el usuario se ha registrado o posteriormente ha abierto su sesión, y se utilizan para identificarle en los servicios con los siguientes objetivos:</p>

                        <p>Mantener al usuario identificado de forma que, si cierra un servicio, el navegador o el ordenador y en otro momento u otro día vuelve a entrar en dicho servicio, seguirá identificado, facilitando así su navegación sin tener que volver a identificarse. Esta funcionalidad se puede suprimir si el usuario pulsa la funcionalidad [cerrar sesión], de forma que esta cookie se elimina y la próxima vez que entre en el servicio el usuario tendrá que iniciar sesión para estar identificado.</p>

                        <p>Comprobar si el usuario está autorizado para acceder a ciertos servicios, por ejemplo, para participar en un concurso.</p>

                        <p>Adicionalmente, algunos servicios pueden utilizar conectores con redes sociales tales como Facebook o Twitter. Cuando el usuario se registra en un servicio con credenciales de una red social, autoriza a la red social a guardar una Cookie persistente que recuerda su identidad y le garantiza acceso a los servicios hasta que expira. El usuario puede borrar esta Cookie y revocar el acceso a los servicios mediante redes sociales actualizando sus preferencias en la red social que específica.</p>

                        <b>
                            <h4>Cookies de analíticas</h4>
                        </b>

                        <p>Cada vez que un usuario visita un servicio, una herramienta de un proveedor externo genera una cookie analítica en el ordenador del usuario. Esta cookie que sólo se genera en la visita, servirá en próximas visitas a los servicios de Juan José Gutiérrez para identificar de forma anónima al visitante. Los objetivos principales que se persiguen son:</p>

                        <p>Permitir la identificación anónima de los usuarios navegantes a través de la cookie (identifica navegadores y dispositivos, no personas) y por lo tanto la contabilización aproximada del número de visitantes y su tendencia en el tiempo.</p>

                        <p>Identificar de forma anónima los contenidos más visitados y por lo tanto más atractivos para los usuarios Saber si el usuario que está accediendo es nuevo o repite visita.</p>

                        <p>Importante: Salvo que el usuario decida registrarse en un servicio de Juan José Gutiérrez, la cookie nunca irá asociada a ningún dato de carácter personal que pueda identificarle. Dichas cookies sólo serán utilizadas con propósitos estadísticos que ayuden a la optimización de la experiencia de los usuarios en el sitio.</p>

                        <b>
                            <h4>Cookies de publicidad</h4>
                        </b>

                        <p>Este tipo de cookies permiten ampliar la información de los anuncios mostrados a cada usuario anónimo en los servicios de Juan José Gutiérrez. Entre otros, se almacena la duración o frecuencia de visualización de posiciones publicitarias, la interacción con las mismas, o los patrones de navegación y/o comportamientos del usuario ya que ayudan a conformar un perfil de interés publicitario. De este modo, permiten ofrecer publicidad afín a los intereses del usuario.</p>

                        <b>
                            <h4>Cookies publicitarias de terceros</h4>
                        </b>

                        <p>Además de la publicidad gestionada por las webs de Juan José Gutiérrez en sus servicios, las webs de Juan José Gutiérrez ofrecen a sus anunciantes la opción de servir anuncios a través de terceros (“Ad-Servers”). De este modo, estos terceros pueden almacenar cookies enviadas desde los servicios de Juan José Gutiérrez procedentes de los navegadores de los usuarios, así como acceder a los datos que en ellas se guardan.</p>

                        <p>Las empresas que generan estas cookies tienen sus propias políticas de privacidad. En la actualidad, las webs de Juan José Gutiérrez utilizan la plataforma Doubleclick (Google) para gestionar estos servicios. Para más información, acuda a</p>

                        <p><a href="http://www.google.es/policies/privacy/ads/#toc-doubleclick">http://www.google.es/policies/privacy/ads/#toc-doubleclick</a> y a <a href="http://www.google.es/policies/privacy/ads/">http://www.google.es/policies/privacy/ads/</a>.</p>

                        <b>
                            <h4>¿Cómo puedo deshabilitar las cookies en mi navegador?</h4>
                        </b>

                        <p>Se pueden configurar los diferentes navegadores para avisar al usuario de la recepción de cookies y, si se desea, impedir su instalación en el equipo. Asimismo, el usuario puede revisar en su navegador qué cookies tiene instaladas y cuál es el plazo de caducidad de las mismas, pudiendo eliminarlas.</p>

                        <p>Para ampliar esta información consulte las instrucciones y manuales de su navegador:</p>

                        <p>Para más información sobre la administración de las cookies en Google Chrome: <a href="https://support.google.com/chrome/answer/95647?hl=es">https://support.google.com/chrome/answer/95647?hl=es</a></p>

                        <p>Para más información sobre la administración de las cookies en Internet Explorer: <a href="http://windows.microsoft.com/es-es/windows-vista/cookies-frequently-asked-questions">http://windows.microsoft.com/es-es/windows-vista/cookies-frequently-asked-questions</a></p>

                        <p>Para más información sobre la administración de las cookies en Mozilla Firefox: <a href="http://support.mozilla.org/es/kb/habilitar-y-deshabilitar-cookies-que-los-sitios-we">http://support.mozilla.org/es/kb/habilitar-y-deshabilitar-cookies-que-los-sitios-we</a></p>

                        <p>Para más información sobre la administración de las cookies en Safari: <a href="http://www.apple.com/es/privacy/use-of-cookies/">http://www.apple.com/es/privacy/use-of-cookies/</a></p>

                        <p>Para más información sobre la administración de las cookies en Opera: <a href="http://help.opera.com/Windows/11.50/es-ES/cookies.html">http://help.opera.com/Windows/11.50/es-ES/cookies.html</a></p>

                        <p>Si desea dejar de ser seguido por Google Analytics visite: <a href="http://tools.google.com/dlpage/gaoptout">http://tools.google.com/dlpage/gaoptout</a></p>

                        <b>
                            <h4>Para saber más sobre las cookies</h4>
                        </b>

                        <p>Puede obtener más información sobre la publicidad online basada en el comportamiento y la privacidad online en el siguiente enlace:<a href=""> http://www.youronlinechoices.com/es/</a></p>

                        <p>Protección de datos de Google Analytics: <a href="http://www.google.com/analytics/learn/privacy.html">http://www.google.com/analytics/learn/privacy.html</a></p>

                        <p>Cómo usa Google Analytics las cookies: <a href="https://developers.google.com/analytics/devguides/collection/analyticsjs/cookie-usage?hl=es#analyticsjs">https://developers.google.com/analytics/devguides/collection/analyticsjs/cookie-usage?hl=es#analyticsjs</a></p>

                        <b>
                            <h4>Actualizaciones y cambios en la política de privacidad/cookies</h4>
                        </b>

                        <p>Las webs de Juan José Gutiérrez pueden modificar esta Política de Cookies en función de exigencias legislativas, reglamentarias, o con la finalidad de adaptar dicha política a las instrucciones dictadas por la Agencia Española de Protección de Datos, por ello se aconseja a los usuarios que la visiten periódicamente.</p>

                        <p>Cuando se produzcan cambios significativos en esta Política de Cookies, estos se comunicarán a los usuarios bien mediante la web o a través de correo electrónico a los usuarios registrados.</p>

                    </div>
                </div>


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

    <!-- REVOLUTION SLIDER -->
    <script src="/web/js/slider.revolution/jquery.themepunch.tools.min.js"></script>
    <script src="/web/js/slider.revolution/jquery.themepunch.revolution.min.js"></script>
    <script src="/web/js/slider.revolution/demo.revolution_slider.js"></script>

    <!-- SCRIPTS -->
    <script src="/web/js/scripts.js"></script>

</body>

</html>
```
Departament d’Educació
Institut La Pineda
2DAW ABP
```
# Projecte 2

# Botiga Virtual

# Serralleria Solidària



```
Departament d’Educació
Institut La Pineda
2DAW ABP
```
## Índex

Índex 2
Informació bàsica del projecte 3
Objectius 4
Enunciat 5
Annexos 9
Annex 1 9
Annex 2 9
Annex 3 9
Annex 4 10
Sprints 10
Criteris d’avaluació 12
Lliurament 12
Metodologia 13



```
Departament d’Educació
Institut La Pineda
2DAW ABP
```
## Informació bàsica del projecte

```
Títol del cicle: Desenvolupament d’aplicacions web
Nom del projecte: Botiga Virtual
Versió: 1
Professors: Olga Domene
Montse Riu
Marcel Garcia
Requisits
acadèmics:
Es recomanable haver superat les següents mòduls:
Programació
Bases de Dades
Introducció a la programació web
Llenguatge de marques i sistemes de gestió de la informació.
Breu presentació del
projecte:
L’alumnat haurà de desenvolupar una plataforma de comerç
electrònic integrada a la web existent serralleriasolidaria.cat.
La plataforma permetrà la venda de productes de serralleria i
serveis associats, garantint facilitat d’ús, seguretat i manteniment
accessible per personal no tècnic.
Hores: 180h
Data de lliurament: 18/05/2026 fins a les 23:59h
Data de presentacions 19/05/2026 a les 9:00h
Nombre de sprints i
dates:
Sprint 1: 10/03/
Sprint 2: 07/04/
Sprint 3: 28/04/
Sprint 4: 12/05/
Nombre d’integrants: 2

```

```
Departament d’Educació
Institut La Pineda
2DAW ABP
```
## Objectius

```
● Planificar la creació d’una interfície web valorant i aplicant especificacions de
disseny.
● Crear interfícies web homogènies definint i aplicant estils.
● Implementar interfícies web responsives/adaptatives i accessibles.
● Utilitzar esdeveniments en JavaScript.
● Establir comunicació asíncrona Client-Servidor
● Aplicar els coneixements de desenvolupament web complet ( full-stack ), integrant el
front-end (React,Vue,Svelte, Angular,etc.) i el back-end mitjançant una API REST
en Laravel.
● Desenvolupar una aplicació interactiva i funcional amb una arquitectura
diferenciada.
● Implementar un sistema d'autenticació i autorització adequat per gestionar diferents
rols d'usuari.
● Fomentar les bones pràctiques en el desenvolupament d'API REST.
● Millorar la capacitat de treball en equip, organització del codi i ús de sistemes de
control de versions com Git.
● Publicar i desplegar una aplicació en un servidor avaluant i aplicant criteris de
configuració per al seu funcionament segur.
● Verifica l'execució d'aplicacions web comprovant els paràmetres de configuració de
serveis de xarxa.
● Afavorir la creativitat i la resolució de problemes en el desenvolupament d'una
aplicació real.
● Utilitzar la metodologia àgil Scrum per la gestió del projecte. Fer servir, per a tal
efecte, l’eina Trello.
● Planificar les tasques a realitzar a cada sprint utilitzant l’eina Trello, indicant la
persona responsable de la seva realització.
● Utilitzar eines de controls de versions.
● Ser capaç de crear proves unitàries per provar l’aplicació
● Aplicar conceptes de refactorització al codi del projecte.
● Organitzar el temps, i ser capaç de resoldre els problemes que es puguin plantejar i
fer un seguiment mitjançant els models proporcionats. Aquests documents hauran
d’estar al Drive d’un dels components del grup i compartits amb la resta de
companys perquè puguin editar. Els professors han de poder veure els documents
amb mode lectura.
● Tots els components del grup hauran de realitzar tasques tant de back com de
front.
● Presentar la feina realitzada a la resta de companys.

```

```
Departament d’Educació
Institut La Pineda
2DAW ABP
```
## Enunciat

Ens han encarregat fer una botiga virtual que permet la gestió integral de les vendes d’un
serralleria.

## Productes

Dels productes volem guardar, el seu codi, el nom, la descripció, a quina categoria
pertanyen, i les seves característiques. També les seves fotos, el seu preu, l’estoc i si és
destacat. Els productes destacats hauran de sortir a la pàgina principal. Quan es compra
un producte s’ha de poder solicitar la seva instal.lació. El preu d’aquesta instal.lació depen
del producte i del codi postal d’on es farà la instal.lació. També en alguns tipus de
productes, s’ha de poder sol.licitar claus addicionals.

## Categories

De les categories volem guardar el seu codi i el nom. Les categories s’han de crear
mitjançant seeders, però la web ha de deixar gestionar-les, per poder crear noves,
editar-les o deshabilitar-les.
Actualment tenim les següents categories:
● Cilindres
● Escut
● Segon pany.

## Packs

L’administrador ha de por gestionar packs (crear, editar, o deshabilitar packs). Un pack és
un conjunt de productes que tenen el seu propi preu, és a dir, el preu del pack no té

## perquè ser la suma del preu dels productes que conté.

## Característiques de productes

Un producte pot tenir més d’una característica i una característica pot ser de més d’un
producte. De les característiques volem saber el tipus (color, mida, seguretat, codificació,
etc), i la seva descripció (blanc, alta seguretat, etc.).

## Compradors

Dels compradors volem guardar, el seu (DNI o CIF o NIE) email, telèfon, el nom,
cognoms i adreça.



```
Departament d’Educació
Institut La Pineda
2DAW ABP
```
## Compres

De les compres volem registrar el client que les ha realitzat, els productes i/o packs que
en formen part, la quantitat de cada producte o pack, el preu unitari i el preu total de la
comanda. També cal indicar l’estat de la comanda (Pendent o Enviada, Confirmació
d’instal ́lació), la data en què s’ha efectuat i la data d’enviament o d'instal·lació).
En cas que el client sol·liciti el servei d’instal·lació, s’haurà d’especificar també l’adreça
d’instal·lació, si diferent de l’adreça del client.
Els compradors podran pagar les seves comandes amb PayPal, targeta o Bizum.
La vostra botiga tindrà una part pública del web i la secció d’administració. S’haurà
d’adaptar el disseny de manera adaptativa i/o responsiva com a mínim en tres dispositius
diferents (ordinador, tablet i mòbil).

## Solucions personalitzades

Un client que no trobi el producte en concret que busca pot demanar un pressupost d'una
solució personalitzada. Per donar-la d’alta aquesta petició necessitem saber l’email del
client, el telèfon, una descripció del problema i un espai per poder pujar fotografies o
documents, a més de l’estat, per exemple (pendent de revisar, revisada, client contactat,
etc)
**Part compradors**
Sense validar-se es podrà:

1. Navegar per categories i productes i packs.
2. Utilitzar el cercador de productes, que s’actualitzi dinàmicament.
3. Registrar-se com a usuari.
4. Afegir, treure i modificar productes al cistell de la compra. El cistell ha d’estar visible
    a totes les pàgines.
5. Validar-se. Si un usuari no validat té productes al cistell, quan es validi aquest han
    de romandre.
6. Sol.licitar una solució personalitzada incloent fotografies o arxius que facilitin el
    càlcul de pressupost.
Amb un usuari validat es podrà:
1. Tot el que es pot fer sense estar validat.
2. Editar les dades del perfil.
3. Accedir a la pàgina de confirmació de compra i pagar amb targeta de crèdit ,
PayPal i Bizum
4. Consultar les compres realitzades i en quin estat es troben: (pendent, enviada, data
d’enviament o data d’instal·lació).
5. Imprimir factures en PDF.



Departament d’Educació
**Institut La Pineda**
2DAW ABP
**Part administrador**

1. Pàgina principal amb algunes estadístiques útils per l’administrador.
2. Afegir, editar i desactivar productes
3. Afegir, editar i desactivar categories.
4. Afegir, editar i desactivar característiques.
5. Afegir, editar i desactivar packs.
6. Veure un registre de les comandes realitzades de tots els usuaris i poder
    canviar l’estat en el que es troben (pendent/enviada amb data d’enviament).
7. Poder filtrar per facilitar la feina.
8. Imprimir etiquetes d'enviament.
**Cistell de la compra**
En accedir al cistell de la compra, visualitzarem un detall dels productes inclosos. Per a
cada ítem, es proporcionarà la quantitat d'unitats, el preu individual, així com el cost total
obtingut per multiplicar la quantitat d'unitats pel preu per unitat. A més, es mostrarà el preu
total acumulat de tot el cistell.
Es destacarà la possibilitat d'ajustar la quantitat d'unitats per a cada producte de manera
dinàmica, amb l'actualització automàtica de tots els càlculs pertinents, sense la necessitat
de refrescar la pàgina.
**Formularis**
Validació de tots els formularis i utilització d’expressions regulars (per exemple per validar
el codi del producte, telèfon, etc...)
En aquest ordre de prioritat: HTML, JavaScript i Laravel.
**Orientació a Objectes**
Hi ha d’haver una part d’orientació a objectes completa i amb sentit dintre de la web. La
programació del Javascript ha d’estar Orientada a Objectes (cistell, usuari, producte...)
**DOM**
S’ha d’utilitzar DOM per interactuar dinàmicament amb la pàgina web per accedir,
modificar i actualitzar els elements de la pàgina en temps d'execució.
**Cercador**
Cercador de productes dinàmic.



Departament d’Educació
**Institut La Pineda**
2DAW ABP
**DRAG&DROP**
Hi ha d’haver una part de DRAG&DROP amb sentit dintre de la web (arrossegar
productes al pack, o al cistell, etc)
**Canvas**
Haurà de tenir els següents gràfics generats amb Canvas per les estadístiques que es
mostren a l’administrador. Per exemple:
● Gràfic que mostri les vendes per període de temps: Mostra el volum de vendes
mensuals
● Gràfic productes més venuts: Mostra els 10 productes més venuts i el volum de
vendes que hi ha de cada un d’aquests productes.
● Gràfic stock: Mostra un gràfic amb els productes que tenen l’stock més baix i el
nombre d’unitats que hi ha actualment disponibles.
**Accessibilitat i Usabilitat**
Desenvolupament de tècniques especials per optimitzar l'accessibilitat i la usabilitat del
lloc web. Això assegura que el lloc sigui més fàcilment accessible per a tots els usuaris,
mentre es manté una experiència d'usuari fluida i intuïtiva.
Disseny adaptat a ordinador, mòbil i tablet
**Possibles ampliacions a valorar**
● Utilització de cookies per visualitzar els últims productes visitats.
● Millora l’experiència d’usuari afegint un chatbot per afegir la funcionalitat d’atendre
preguntes freqüents, no cal una API REST. Podeu utilitzar eines com Tidio, Tawk.to,
etc.
**A tenir en compte**

1. La pàgina principal ha de contenir els productes destacats.
2. Les contrasenyes han d’estar encriptades a la base de dades.
3. Heu de respectar el RGPD.



```
Departament d’Educació
Institut La Pineda
2DAW ABP
```
## Annexos

## Annex 1

Desplegament de l'aplicació en un servidor Dokku.Configuració de comunicació segura a
través de SSL/TLS y polítiques de seguritat per protegir les dades enmagatzemades.

## Annex 2

Explicació de funcionament del sistemes de nom jeràrquics. Creació de subdominis per
accedir a les diferents parts de l'aplicació web.

## Annex 3

Anàlisi d'Accessibilitat Web
Heu de realitzar un informe detallat sobre l'accessibilitat del vostre lloc web.
● Utilitzant eines com WAVE (Web Accessibility Evaluation Tool), axe DevTools i/o
AChecker, etc... portareu a terme un anàlisi automàtic de l'accessibilitat de la web.
El vostre objectiu és identificar i documentar tots els errors, avisos i altres qüestions
rellevants detectades durant l'avaluació. Haureu d’explicar com heu resolt o intentat
resoldre els problemes detectats durant l'avaluació. En cas que no pugueu resoldre
alguns dels aspectes, haureu de proporcionar una explicació clara del problema i
del motiu pel qual no heu pogut resoldre'l.
Format de l'Informe:
El vostre informe ha d'incloure:
● Metodologia utilitzada per a l'avaluació de l'accessibilitat
● Resultats de l'avaluació, incloent errors, avisos i altres qüestions rellevants



```
Departament d’Educació
Institut La Pineda
2DAW ABP
● Explicació de les solucions o intents de resolució dels problemes detectats
● Conclusions i reflexions finals
● S’haurà d’incloure qualsevol informació addicional rellevant, com captures de
pantalla, resultats de proves, etc.
```
## Annex 4

```
● Diagrames de Casos d’ús i de classes
● Reflexions i conclusions referents a la gestió de temps i tasques del projecte, la
utilització de l’SCRUM i els costos associats al projecte
● Documentació de la creació de proves unitàries.
● Explicació de la refactorització de codi. Aplicació dels conceptes apresos al
projecte. Documentació de la feina realitzada
● Realització del curs d’Openwebinars i aportació de la certificació de l’examen.
https://openwebinars.net/academia/portada/introduccion-testing/
```
## Sprints

```
Sprint 1
En aquest sprint l’alumnat haurà de presentar:
● Creació de la base de dades (Migrations i Seeders)
● Part de l’administrador: Disseny i funcionalitats de:
○ Dashboard.
○ Gestió categories.
○ Gestió productes.
○ Gestió de packs.
○ Gestió de característiques.

```

Departament d’Educació
**Institut La Pineda**
2DAW ABP
**Sprint 2**
En aquest sprint l’alumnat haurà de presentar el disseny i funcionalitats de:
● Pàgina principal que mostri els productes destacats, així com un menú de
navegació que es generi de manera automàtica amb les categories que es troben
donades d’alta a la BD.
● Filtre de productes per característiques, etc...
● Pàgina on mostra tots els productes d’una categoria.
● Pàgina del detall del producte amb opció de comprar.
● Formulari d’alta de solucions personalitzades.
**Sprint 3**
En aquest sprint l’alumnat haurà de presentar:
● Cistell de la compra.
● Drag&drop.
● Disseny implementat en dos dels possibles dispositius (ordinador, tablet o mòbil).
● Canvas (Estadístiques amb gràfics del dashboard de l’administrador).
● Impressió de factures tant de clients com de l’administrador.
● Impressió etiquetes d’enviament.
**Sprint 4**
● Pagament.
● Gestió de comandes per part de l’administrador.
● Gestió de solucions personalitzades per part de l’administrador.
● Tot el que falta de la botiga virtual en quan a funcionalitats.
**Lliurament final**
● Annexos.



```
Departament d’Educació
Institut La Pineda
2DAW ABP
```
## Criteris d’avaluació

Per poder assolir els objectius del projecte, tots els apartats de l’avaluació han de tenir
una puntuació de com a mínim 5 sobre 10 punts.
**% de la
qualificació
Tasques Tipus de
tasca**
5% Coavaluació per part dels membres del teu equip de
com has treballat en el grup.
Individual
5% Autoavaluació de com has treballat en el grup. Individual
10%
Assistència, diari d'incidències, treball setmanal,
planificació i execució dels sprints a la data indicada.
Individual
60% Per avaluar cada part, cadascun dels professors us
proporcionarán una rúbrica en el que s'avaluaran
diferents aspectes de l’aplicació web i la documentació
lliurada. (Front-end / Back-end)
Grupal
20% Examen de validació. (Front-end / Back-end) Individual

## Lliurament

```
L’alumnat haurà de crear un repositori públic a GitHub I haurà de lliurar a Moodle l’enllaç.
Els annexos es lliuraran en un apartat específic del Moodle.

```

```
Departament d’Educació
Institut La Pineda
2DAW ABP
```
## Metodologia

Per portar a terme aquest projecte farem servir la metodologia col·laborativa **ABP** ,
Aprenentatge Basat en Problemes, doncs proporciona una marc de treball i unes pautes
que faciliten el desenvolupament i seguiment d’un projecte.
La metodologia ABP (Aprenentatge Basat en Problemes) o PBL en anglès
(Problem-Based Learning) està centrada en l'aprenentatge i en l'alumnat com vertaders
protagonistes. A través de la investigació i la reflexió, les alumnes i els alumnes arriben a
una solució d'un problema plantejat per part del professorat adquirint, a més a més del
coneixement, una sèrie d'habilitats i capacitats imprescindibles dins del procés educatiu.
Durant aquest procés, l'alumnat segueix un aprenentatge col·laboratiu, on cada equip
gestiona els seus propis mecanismes per la resolució del problema, prenent totes les
decisions necessàries per assolir l'èxit. Aquest tipus de relació entre els i les membres de
l'equip motiven unes interaccions i relacions que acabaran per generar una
interdependència positiva que no implica competència.
Aquesta metodologia està dividida en 3 fases, que es detallen i expliquen posteriorment.
En aquestes fases hi ha una sèrie de fites o tasques a desenvolupar que són les que
donen un sentit global a la resolució del problema o repte.
Pel control i seguiment de les tasques es farà servir la metodologia àgil **Scrum utilizant
l’eina Trello.**
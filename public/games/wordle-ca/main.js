// DEFINIM VARIABLES
const NOMBRE_INTENTS = 6;
let intentsRestants = NOMBRE_INTENTS;
let intentActual = 1;
let numLletra = 1;
let paraulaUsuari = "";

// FUNCIO SELECCIO NUMEROS ALEATORIS PER ESCOLLIR PARAULES
function getRandomArbitrary(max) {
  return Math.floor(Math.random()* max);
}

// FUNCIO SELECCIONAR UNA PARAULA
function seleccionarUnaParaula() {
  let indexParaula = getRandomArbitrary(1000);
  let paraulaEscollida = arrayGlobal2[indexParaula].toUpperCase();
  return paraulaEscollida;
}

// L'ORDINADOR SELECCIONA UNA PARAULA ALEATÒRIAMENT
let paraulaRandom = seleccionarUnaParaula();
console.log(`paraula random: `, paraulaRandom);
console.log(`numLletra és: `, numLletra);

// FUNCIO ENVIAR LLETRA
function enviarLletra(lletra) {
  console.log(`lletra : `, lletra);

  let codi = "F" + intentActual + "C" + numLletra;
  console.log('codi: ', codi);
  document.getElementById(codi).innerHTML = lletra;
  numLletra++;
  numLletra = (numLletra > 5) ? 5 : numLletra;
}

function borrarLletra() {
  let codi = "F" + intentActual + "C" + numLletra;
  let lletra_per_Esborrar = document.getElementById(codi);

  if (lletra_per_Esborrar.innerHTML == "&nbsp;") {
    numLletra--;
    numLletra = (numLletra == 0) ? 1 : numLletra;
    console.log('lletra buida / numLletra =', numLletra);
    codi = "F" + intentActual + "C" + numLletra;
    document.getElementById(codi).innerHTML = "&nbsp;";
  } else {
    lletra_per_Esborrar.innerHTML = "&nbsp;";
    numLletra = (numLletra > 1) ? numLletra-- : 1;
    console.log('lletra emplenada / numLletra', numLletra);
  }
}

function revisarParaula() {
  let paraulaUsuari = "";
  let i, codi, lletra;

  if (numLletra != 5) {
    alert("Falten lletres en la paraula");
  } else {
    codi = "F" + intentActual;
  }

  for (i = 1; i < 6; i++) {
    codi = "F" + intentActual + "C" + i;
    lletra = document.getElementById(codi).innerHTML;
    console.log(`lletra: `, lletra);
    console.log(`paraulaRandom:  revisarParaula`, paraulaRandom);

    if (paraulaRandom.charAt(i - 1) == lletra) {
      document.getElementById(codi).style.background = "#318200";
      document.getElementById(lletra).style.background = "#318200";
    } else if (paraulaRandom.includes(lletra)) {
      document.getElementById(codi).style.background = "#c9b50d";
      document.getElementById(lletra).style.background = "#c9b50d";
    } else {
      document.getElementById(codi).style.background = "#414141";
      document.getElementById(lletra).style.background = "#414141";
    }
    paraulaUsuari += lletra;
  }

  intentActual++;
  numLletra = 1;

  if (paraulaUsuari == paraulaRandom) {
    document.getElementById("teclat-en-pantalla").style.display = "none";
    document.getElementById("resultat").innerHTML =
      "Felicitats! La paraula és: <b>" +
      paraulaRandom +
      "</b><br>" +
      "Has necessitat " +
      (intentActual - 1) +
      " intents!";
  } else if (intentActual == 7) {
    document.getElementById("teclat-en-pantalla").style.display = "none";
    document.getElementById("resultat").innerHTML =
      "La paraula era: <b>" + paraulaRandom + "</b>";
  }
}

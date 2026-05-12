<?php

namespace Database\Seeders;

use App\Models\ShopSetting;
use Illuminate\Database\Seeder;

class ShopSettingSeeder extends Seeder
{
    public function run(): void
    {
        ShopSetting::set(ShopSetting::KEY_TERMS_CA, <<<'TXT'
TERMES I CONDICIONS GENERALS DE COMPRA

1. ÀMBIT D'APLICACIÓ
Aquests termes regulen les compres realitzades a la botiga en línia de Serralleria Solidaria, amb NIF B-67344291302 i domicili social al Carrer Diputació, 426, 4rt 2ª, 08013 Barcelona.

2. PRODUCTES I PREUS
Tots els preus es mostren en euros (€) i inclouen l'IVA aplicable. Serralleria Solidaria es reserva el dret de modificar preus en qualsevol moment, però els canvis no afectaran les comandes ja confirmades.

3. PROCÉS DE COMPRA
Per realitzar una comanda cal registrar-se, afegir productes al carret i confirmar la compra. La confirmació de la comanda per correu electrònic constitueix l'acceptació del contracte de compravenda.

4. FORMES DE PAGAMENT
Acceptem pagament amb targeta de crèdit/dèbit (Visa, Mastercard, American Express) a través de Stripe, i PayPal. El pagament es processa de forma segura i en cap cas emmagatzemem les dades completes de la targeta.

5. ENVIAMENT I LLIURAMENT
Les comandes s'envien a l'adreça indicada pel client durant el procés de compra. Els terminis de lliurament són orientatius i depenen de la disponibilitat del producte i la zona de lliurament. Les despeses d'enviament s'indiquen abans de confirmar la compra.

6. DRET DE DESISTIMENT
El client disposa d'un termini de 14 dies naturals des de la recepció del producte per exercir el dret de desistiment, conforme a la Llei General per a la Defensa dels Consumidors i Usuaris. Els productes han de retornar-se en el seu estat original i embalatge complet.

7. GARANTIES
Tots els productes disposen de la garantia legal de conformitat establerta per la legislació vigent (mínim 3 anys per a productes nous). En cas de producte defectuós, el client pot sol·licitar la reparació, substitució, rebaixa del preu o resolució del contracte.

8. RESPONSABILITAT
Serralleria Solidaria no serà responsable dels danys derivats d'un ús inadequat dels productes adquirits. Les imatges dels productes són orientatives i poden presentar lleugeres variacions respecte al producte real.

9. RESOLUCIÓ DE CONFLICTES
Per a qualsevol reclamació, el client pot contactar-nos a empresa@serralleriasolidaria.cat o al telèfon +34 600 500 517. Estem adherits a la Junta Arbitral de Consum de Catalunya. En cas de litigi, seran competents els jutjats i tribunals de Barcelona.

10. MODIFICACIÓ DELS TERMES
Serralleria Solidaria es reserva el dret de modificar aquests termes. Les modificacions no afectaran les comandes ja confirmades.
TXT);

        ShopSetting::set(ShopSetting::KEY_TERMS_ES, <<<'TXT'
TÉRMINOS Y CONDICIONES GENERALES DE COMPRA

1. ÁMBITO DE APLICACIÓN
Estos términos regulan las compras realizadas en la tienda en línea de Serralleria Solidaria, con NIF B-67344291302 y domicilio social en Calle Diputació, 426, 4rt 2ª, 08013 Barcelona.

2. PRODUCTOS Y PRECIOS
Todos los precios se muestran en euros (€) e incluyen el IVA aplicable. Serralleria Solidaria se reserva el derecho de modificar precios en cualquier momento, pero los cambios no afectarán a los pedidos ya confirmados.

3. PROCESO DE COMPRA
Para realizar un pedido es necesario registrarse, añadir productos al carrito y confirmar la compra. La confirmación del pedido por correo electrónico constituye la aceptación del contrato de compraventa.

4. FORMAS DE PAGO
Aceptamos pago con tarjeta de crédito/débito (Visa, Mastercard, American Express) a través de Stripe, y PayPal. El pago se procesa de forma segura y en ningún caso almacenamos los datos completos de la tarjeta.

5. ENVÍO Y ENTREGA
Los pedidos se envían a la dirección indicada por el cliente durante el proceso de compra. Los plazos de entrega son orientativos y dependen de la disponibilidad del producto y la zona de entrega. Los gastos de envío se indican antes de confirmar la compra.

6. DERECHO DE DESISTIMIENTO
El cliente dispone de un plazo de 14 días naturales desde la recepción del producto para ejercer el derecho de desistimiento, conforme a la Ley General para la Defensa de los Consumidores y Usuarios. Los productos deben devolverse en su estado original y embalaje completo.

7. GARANTÍAS
Todos los productos disponen de la garantía legal de conformidad establecida por la legislación vigente (mínimo 3 años para productos nuevos). En caso de producto defectuoso, el cliente puede solicitar la reparación, sustitución, rebaja del precio o resolución del contrato.

8. RESPONSABILIDAD
Serralleria Solidaria no será responsable de los daños derivados de un uso inadecuado de los productos adquiridos. Las imágenes de los productos son orientativas y pueden presentar ligeras variaciones respecto al producto real.

9. RESOLUCIÓN DE CONFLICTOS
Para cualquier reclamación, el cliente puede contactarnos en empresa@serralleriasolidaria.cat o en el teléfono +34 600 500 517. Estamos adheridos a la Junta Arbitral de Consumo de Cataluña. En caso de litigio, serán competentes los juzgados y tribunales de Barcelona.

10. MODIFICACIÓN DE LOS TÉRMINOS
Serralleria Solidaria se reserva el derecho de modificar estos términos. Las modificaciones no afectarán a los pedidos ya confirmados.
TXT);

        ShopSetting::set(ShopSetting::KEY_TERMS_EN, <<<'TXT'
GENERAL TERMS AND CONDITIONS OF PURCHASE

1. SCOPE
These terms govern purchases made on the Serralleria Solidaria online store, with Tax ID (NIF) B-67344291302, registered at Carrer Diputació, 426, 4th floor 2nd, 08013 Barcelona, Spain.

2. PRODUCTS AND PRICES
All prices are displayed in euros (€) and include applicable VAT. Serralleria Solidaria reserves the right to modify prices at any time, but changes will not affect orders already confirmed.

3. PURCHASING PROCESS
To place an order, customers must register, add products to the cart, and confirm the purchase. Order confirmation by email constitutes acceptance of the sales contract.

4. PAYMENT METHODS
We accept credit/debit card payments (Visa, Mastercard, American Express) via Stripe, and PayPal. Payment is processed securely and we never store full card details.

5. SHIPPING AND DELIVERY
Orders are shipped to the address provided by the customer during the checkout process. Delivery times are approximate and depend on product availability and delivery area. Shipping costs are displayed before confirming the purchase.

6. RIGHT OF WITHDRAWAL
The customer has 14 calendar days from receipt of the product to exercise the right of withdrawal, in accordance with Spanish consumer protection law. Products must be returned in their original condition and complete packaging.

7. WARRANTIES
All products come with the legal guarantee of conformity established by applicable legislation (minimum 3 years for new products). In case of a defective product, the customer may request repair, replacement, price reduction, or contract termination.

8. LIABILITY
Serralleria Solidaria shall not be liable for damages arising from improper use of purchased products. Product images are for reference purposes and may present slight variations from the actual product.

9. DISPUTE RESOLUTION
For any complaint, the customer may contact us at empresa@serralleriasolidaria.cat or by phone at +34 600 500 517. We are members of the Consumer Arbitration Board of Catalonia. In case of dispute, the courts of Barcelona shall have jurisdiction.

10. MODIFICATION OF TERMS
Serralleria Solidaria reserves the right to modify these terms. Modifications will not affect orders already confirmed.
TXT);
    }
}

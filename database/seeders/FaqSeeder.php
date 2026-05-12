<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'sort_order' => 0,
                'is_active' => true,
                'question_ca' => 'Com puc fer una comanda?',
                'question_es' => '¿Cómo puedo hacer un pedido?',
                'question_en' => 'How do I place an order?',
                'answer_ca' => 'Navega pel catàleg, afegeix els productes al carret i segueix el procés de pagament des de la pàgina de checkout. Pots pagar amb targeta de crèdit o altres mètodes disponibles.',
                'answer_es' => 'Navega por el catálogo, añade los productos al carrito y sigue el proceso de pago desde la página de checkout. Puedes pagar con tarjeta de crédito u otros métodos disponibles.',
                'answer_en' => 'Browse the catalogue, add products to your cart and follow the checkout process. You can pay by credit card or other available methods.',
            ],
            [
                'sort_order' => 1,
                'is_active' => true,
                'question_ca' => 'Quins mètodes de pagament accepteu?',
                'question_es' => '¿Qué métodos de pago aceptáis?',
                'question_en' => 'What payment methods do you accept?',
                'answer_ca' => 'Acceptem targeta de crèdit i dèbit (Visa, Mastercard), i altres passarel·les de pagament en línia. Tots els pagaments es processen de forma segura.',
                'answer_es' => 'Aceptamos tarjeta de crédito y débito (Visa, Mastercard) y otras pasarelas de pago en línea. Todos los pagos se procesan de forma segura.',
                'answer_en' => 'We accept credit and debit cards (Visa, Mastercard) and other online payment gateways. All payments are processed securely.',
            ],
            [
                'sort_order' => 2,
                'is_active' => true,
                'question_ca' => 'Quant triga l\'enviament?',
                'question_es' => '¿Cuánto tarda el envío?',
                'question_en' => 'How long does shipping take?',
                'answer_ca' => 'Els enviaments nacionals es lliuren habitualment entre 2 i 5 dies laborables. Rebràs un correu electrònic amb el número de seguiment quan la comanda sigui enviada.',
                'answer_es' => 'Los envíos nacionales se entregan habitualmente entre 2 y 5 días laborables. Recibirás un correo electrónico con el número de seguimiento cuando el pedido sea enviado.',
                'answer_en' => 'Domestic shipments are usually delivered within 2 to 5 business days. You will receive an email with the tracking number once your order has been shipped.',
            ],
            [
                'sort_order' => 3,
                'is_active' => true,
                'question_ca' => 'Puc retornar un producte?',
                'question_es' => '¿Puedo devolver un producto?',
                'question_en' => 'Can I return a product?',
                'answer_ca' => 'Sí, tens 14 dies naturals des de la recepció per sol·licitar una devolució, sempre que el producte estigui en el seu estat original i embalatge. Pots gestionar-ho des del teu compte a la secció de comandes.',
                'answer_es' => 'Sí, tienes 14 días naturales desde la recepción para solicitar una devolución, siempre que el producto esté en su estado original y embalaje. Puedes gestionarlo desde tu cuenta en la sección de pedidos.',
                'answer_en' => 'Yes, you have 14 calendar days from delivery to request a return, provided the product is in its original condition and packaging. You can manage this from your account in the orders section.',
            ],
            [
                'sort_order' => 4,
                'is_active' => true,
                'question_ca' => 'Necessito crear un compte per comprar?',
                'question_es' => '¿Necesito crear una cuenta para comprar?',
                'question_en' => 'Do I need to create an account to buy?',
                'answer_ca' => 'Sí, cal registrar-se per realitzar una compra. El registre et permet consultar l\'historial de comandes, gestionar devolucions i guardar les teves adreces d\'enviament.',
                'answer_es' => 'Sí, es necesario registrarse para realizar una compra. El registro te permite consultar el historial de pedidos, gestionar devoluciones y guardar tus direcciones de envío.',
                'answer_en' => 'Yes, you need to register to make a purchase. Registration lets you view your order history, manage returns and save your shipping addresses.',
            ],
            [
                'sort_order' => 5,
                'is_active' => true,
                'question_ca' => 'Com puc saber quin cilindre necessito?',
                'question_es' => '¿Cómo puedo saber qué cilindro necesito?',
                'question_en' => 'How do I know which cylinder I need?',
                'answer_ca' => 'A la fitxa de cada producte trobaràs les especificacions tècniques i mides. Si tens dubtes, pots sol·licitar una consulta personalitzada mitjançant el formulari de solucions a mida.',
                'answer_es' => 'En la ficha de cada producto encontrarás las especificaciones técnicas y medidas. Si tienes dudas, puedes solicitar una consulta personalizada mediante el formulario de soluciones a medida.',
                'answer_en' => 'Each product page includes technical specifications and dimensions. If you are unsure, you can request a personalised consultation through the custom solutions form.',
            ],
            [
                'sort_order' => 6,
                'is_active' => true,
                'question_ca' => 'Oferiu instal·lació dels productes?',
                'question_es' => '¿Ofrecéis instalación de los productos?',
                'question_en' => 'Do you offer product installation?',
                'answer_ca' => 'El servei d\'instal·lació depèn de la zona geogràfica. Contacta\'ns a través del formulari de contacte per consultar disponibilitat a la teva àrea.',
                'answer_es' => 'El servicio de instalación depende de la zona geográfica. Contáctanos a través del formulario de contacto para consultar disponibilidad en tu área.',
                'answer_en' => 'Installation service depends on the geographic area. Contact us through the contact form to check availability in your area.',
            ],
            [
                'sort_order' => 7,
                'is_active' => true,
                'question_ca' => 'Les meves dades personals estan segures?',
                'question_es' => '¿Mis datos personales están seguros?',
                'question_en' => 'Is my personal data safe?',
                'answer_ca' => 'Sí, complim amb el Reglament General de Protecció de Dades (RGPD). Les teves dades personals es tracten de forma segura i mai es comparteixen amb tercers sense el teu consentiment. Pots consultar la nostra política de privacitat per a més detalls.',
                'answer_es' => 'Sí, cumplimos con el Reglamento General de Protección de Datos (RGPD). Tus datos personales se tratan de forma segura y nunca se comparten con terceros sin tu consentimiento. Puedes consultar nuestra política de privacidad para más detalles.',
                'answer_en' => 'Yes, we comply with the General Data Protection Regulation (GDPR). Your personal data is handled securely and never shared with third parties without your consent. Please refer to our privacy policy for more details.',
            ],
            [
                'sort_order' => 8,
                'is_active' => false,
                'question_ca' => 'Feu enviaments internacionals?',
                'question_es' => '¿Hacéis envíos internacionales?',
                'question_en' => 'Do you ship internationally?',
                'answer_ca' => 'Actualment només servim a Espanya peninsular. Estem treballant per ampliar la cobertura a altres països en el futur.',
                'answer_es' => 'Actualmente solo servimos a España peninsular. Estamos trabajando para ampliar la cobertura a otros países en el futuro.',
                'answer_en' => 'We currently only ship to mainland Spain. We are working on expanding coverage to other countries in the future.',
            ],
        ];

        foreach ($items as $item) {
            Faq::query()->create($item);
        }
    }
}

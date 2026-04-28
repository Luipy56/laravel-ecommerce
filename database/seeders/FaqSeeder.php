<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        Faq::query()->create([
            'sort_order' => 0,
            'is_active' => true,
            'question_ca' => 'Com puc fer una comanda?',
            'question_es' => '¿Cómo puedo hacer un pedido?',
            'question_en' => 'How do I place an order?',
            'answer_ca' => 'Afegeix productes al carret i segueix el procés de pagament des de la pàgina de checkout.',
            'answer_es' => 'Añade productos al carrito y sigue el proceso de pago desde la página de checkout.',
            'answer_en' => 'Add items to your cart and complete checkout from the checkout page.',
        ]);
    }
}

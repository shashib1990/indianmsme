<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FaqTopic;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $topicsData = [
            [
                'name' => 'About IndianMSME',
                'slug' => 'about',
                'icon' => 'hgi-information-circle',
                'sort_order' => 1,
                'faqs' => [
                    [
                        'question' => 'What is IndianMSME.org?',
                        'answer' => 'IndianMSME.org is a dedicated B2B e-commerce platform designed to empower MSMEs across India. We connect Distributors, Dealers, Retailers, Manufacturers, and Service Providers on one unified platform.',
                        'sort_order' => 1,
                    ],
                    [
                        'question' => 'Who can register on IndianMSME.org?',
                        'answer' => 'Any registered Indian business entity including Manufacturers, Wholesalers, Distributors, Retailers, District Development Coordinators (DDC), and Area Development Coordinators (ADC) can register and operate on our platform.',
                        'sort_order' => 2,
                    ],
                ]
            ],
            [
                'name' => 'Buying on IndianMSME',
                'slug' => 'buying',
                'icon' => 'hgi-shopping-bag-01',
                'sort_order' => 2,
                'faqs' => [
                    [
                        'question' => 'How can I place an order?',
                        'answer' => 'Simply browse products by category or brand, select your required quantity, enter your delivery pincode, add items to cart, and proceed to secure online checkout or COD payment.',
                        'sort_order' => 1,
                    ],
                    [
                        'question' => 'Is there a minimum order quantity (MOQ)?',
                        'answer' => 'MOQ depends on individual vendor listings. Bulk wholesale products specify minimum order thresholds directly on product detail pages.',
                        'sort_order' => 2,
                    ],
                ]
            ],
            [
                'name' => 'Selling on IndianMSME',
                'slug' => 'selling',
                'icon' => 'hgi-store-01',
                'sort_order' => 3,
                'faqs' => [
                    [
                        'question' => 'How can I become a seller on IndianMSME.org?',
                        'answer' => 'Click on "Seller Registration" in the navigation bar or footer, submit your business details, GSTIN, and company documents. Once verified by our onboarding team, your seller portal will be active.',
                        'sort_order' => 1,
                    ],
                    [
                        'question' => 'What documents are required for seller registration?',
                        'answer' => 'You will need a valid GSTIN certificate, PAN card, business address proof, bank account details (cancelled cheque), and optional MSME/Udyam registration certificate.',
                        'sort_order' => 2,
                    ],
                ]
            ],
            [
                'name' => 'Orders & Payments',
                'slug' => 'orders',
                'icon' => 'hgi-credit-card',
                'sort_order' => 4,
                'faqs' => [
                    [
                        'question' => 'What payment methods are accepted?',
                        'answer' => 'We support UPI, Credit & Debit Cards, Net Banking, NEFT/RTGS bank transfers, Wallet balance, and Cash on Delivery (COD) for eligible pincodes.',
                        'sort_order' => 1,
                    ],
                    [
                        'question' => 'How do I receive GST tax invoices?',
                        'answer' => 'GST-compliant tax invoices with Input Tax Credit (ITC) details are generated automatically and available for instant download in your order details section upon dispatch.',
                        'sort_order' => 2,
                    ],
                ]
            ],
            [
                'name' => 'Shipping & Delivery',
                'slug' => 'shipping',
                'icon' => 'hgi-delivery-truck-01',
                'sort_order' => 5,
                'faqs' => [
                    [
                        'question' => 'How long does delivery take?',
                        'answer' => 'Standard shipments deliver within 3 to 7 business days depending on destination pincode and seller warehouse location.',
                        'sort_order' => 1,
                    ],
                    [
                        'question' => 'How can I track my order?',
                        'answer' => 'You can track your order status in real time by clicking "Track Your Order" in the header utility bar and entering your Order ID or tracking code.',
                        'sort_order' => 2,
                    ],
                ]
            ],
            [
                'name' => 'Returns & Refunds',
                'slug' => 'returns',
                'icon' => 'hgi-circle-arrow-reload-01',
                'sort_order' => 6,
                'faqs' => [
                    [
                        'question' => 'Can I return or cancel my order?',
                        'answer' => 'Yes, return requests can be submitted within 7 days of delivery for defective or wrong products. Unshipped orders can be cancelled directly from your user dashboard.',
                        'sort_order' => 1,
                    ],
                    [
                        'question' => 'How long does a refund take?',
                        'answer' => 'Approved refunds are credited back to your original payment method or wallet within 3 to 5 business days post item quality inspection.',
                        'sort_order' => 2,
                    ],
                ]
            ],
            [
                'name' => 'Policies & Others',
                'slug' => 'policies',
                'icon' => 'hgi-shield-01',
                'sort_order' => 7,
                'faqs' => [
                    [
                        'question' => 'How does IndianMSME support DDC and ADC networks?',
                        'answer' => 'We empower District Development Coordinators (DDC) and Area Development Coordinators (ADC) with dedicated dashboard portals for local fulfillment, pickup store management, and commission distribution.',
                        'sort_order' => 1,
                    ],
                    [
                        'question' => 'Is my business data and payment info safe?',
                        'answer' => 'Yes, IndianMSME uses 256-bit SSL encryption, PCI-DSS compliant payment gateways, and strict data privacy protocols to secure all business transactions.',
                        'sort_order' => 2,
                    ],
                ]
            ],
        ];

        foreach ($topicsData as $tData) {
            $faqs = $tData['faqs'];
            unset($tData['faqs']);

            $topic = FaqTopic::updateOrCreate(
                ['slug' => $tData['slug']],
                $tData
            );

            foreach ($faqs as $fData) {
                Faq::updateOrCreate(
                    [
                        'faq_topic_id' => $topic->id,
                        'question' => $fData['question'],
                    ],
                    [
                        'answer' => $fData['answer'],
                        'sort_order' => $fData['sort_order'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}

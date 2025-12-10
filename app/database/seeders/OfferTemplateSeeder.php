<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class OfferTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizations = Organization::all();

        foreach ($organizations as $organization) {
            $this->createTemplatesForOrganization($organization);
        }
    }

    /**
     * Create templates for a specific organization
     */
    protected function createTemplatesForOrganization(Organization $organization): void
    {
        $templates = $this->getTemplates();

        foreach ($templates as $template) {
            DocumentTemplate::withoutGlobalScopes()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'name' => $template['name'],
                    'type' => 'offer',
                ],
                [
                    'content' => json_encode($template['blocks']),
                    'is_active' => true,
                    'is_default' => $template['is_default'] ?? false,
                ]
            );
        }
    }

    /**
     * Get predefined offer templates
     */
    protected function getTemplates(): array
    {
        return [
            // Template 1: Standard - Full featured
            [
                'name' => 'Standard',
                'is_default' => true,
                'blocks' => [
                    [
                        'id' => 'header_1',
                        'type' => 'header',
                        'visible' => true,
                        'data' => [
                            'introTitle' => '',
                            'introText' => '',
                        ],
                    ],
                    [
                        'id' => 'services_1',
                        'type' => 'services',
                        'visible' => true,
                        'data' => [],
                    ],
                    [
                        'id' => 'summary_1',
                        'type' => 'summary',
                        'visible' => true,
                        'data' => [],
                    ],
                    [
                        'id' => 'terms_1',
                        'type' => 'terms',
                        'visible' => true,
                        'data' => [
                            'content' => '',
                        ],
                    ],
                    [
                        'id' => 'signature_1',
                        'type' => 'signature',
                        'visible' => true,
                        'data' => [],
                    ],
                ],
            ],

            // Template 2: Simple - Minimal offer
            [
                'name' => 'Simplă',
                'is_default' => false,
                'blocks' => [
                    [
                        'id' => 'header_1',
                        'type' => 'header',
                        'visible' => true,
                        'data' => [
                            'introTitle' => '',
                            'introText' => '',
                        ],
                    ],
                    [
                        'id' => 'services_1',
                        'type' => 'services',
                        'visible' => true,
                        'data' => [],
                    ],
                    [
                        'id' => 'summary_1',
                        'type' => 'summary',
                        'visible' => true,
                        'data' => [],
                    ],
                ],
            ],

            // Template 3: Detailed - With additional sections
            [
                'name' => 'Detaliată',
                'is_default' => false,
                'blocks' => [
                    [
                        'id' => 'header_1',
                        'type' => 'header',
                        'visible' => true,
                        'data' => [
                            'introTitle' => '',
                            'introText' => '',
                        ],
                    ],
                    [
                        'id' => 'text_intro',
                        'type' => 'text',
                        'visible' => true,
                        'data' => [
                            'title' => 'Introducere',
                            'content' => 'Vă mulțumim pentru interesul acordat serviciilor noastre. În urma discuțiilor purtate, vă prezentăm oferta noastră personalizată.',
                        ],
                    ],
                    [
                        'id' => 'services_1',
                        'type' => 'services',
                        'visible' => true,
                        'data' => [],
                    ],
                    [
                        'id' => 'summary_1',
                        'type' => 'summary',
                        'visible' => true,
                        'data' => [],
                    ],
                    [
                        'id' => 'text_timeline',
                        'type' => 'text',
                        'visible' => true,
                        'data' => [
                            'title' => 'Termen de livrare',
                            'content' => 'Termenul de livrare va fi stabilit de comun acord după acceptarea ofertei.',
                        ],
                    ],
                    [
                        'id' => 'terms_1',
                        'type' => 'terms',
                        'visible' => true,
                        'data' => [
                            'content' => "1. Plata se efectuează în termen de 30 de zile de la emiterea facturii.\n2. Prețurile nu includ TVA.\n3. Oferta este valabilă pentru perioada menționată.\n4. Orice modificări necesită acord scris.",
                        ],
                    ],
                    [
                        'id' => 'signature_1',
                        'type' => 'signature',
                        'visible' => true,
                        'data' => [],
                    ],
                ],
            ],

            // Template 4: Web Development specific
            [
                'name' => 'Dezvoltare Web',
                'is_default' => false,
                'blocks' => [
                    [
                        'id' => 'header_1',
                        'type' => 'header',
                        'visible' => true,
                        'data' => [
                            'introTitle' => 'Soluții web personalizate pentru afacerea ta',
                            'introText' => 'Creăm experiențe digitale care convertesc vizitatorii în clienți. Design modern, performanță optimă și funcționalități adaptate nevoilor tale.',
                        ],
                    ],
                    [
                        'id' => 'text_scope',
                        'type' => 'text',
                        'visible' => true,
                        'data' => [
                            'title' => 'Scopul proiectului',
                            'content' => 'Descrierea detaliată a proiectului și obiectivele principale.',
                        ],
                    ],
                    [
                        'id' => 'services_1',
                        'type' => 'services',
                        'visible' => true,
                        'data' => [],
                    ],
                    [
                        'id' => 'columns_features',
                        'type' => 'columns',
                        'visible' => true,
                        'data' => [
                            'leftTitle' => 'Ce este inclus',
                            'leftContent' => "• Design responsive\n• Optimizare SEO de bază\n• Integrare Google Analytics\n• Formular de contact\n• Training utilizare",
                            'rightTitle' => 'Beneficii',
                            'rightContent' => "• Prezență online profesională\n• Creșterea vizibilității\n• Generare lead-uri\n• Suport tehnic 30 zile\n• Actualizări de securitate",
                        ],
                    ],
                    [
                        'id' => 'summary_1',
                        'type' => 'summary',
                        'visible' => true,
                        'data' => [],
                    ],
                    [
                        'id' => 'text_timeline',
                        'type' => 'text',
                        'visible' => true,
                        'data' => [
                            'title' => 'Etape și termene',
                            'content' => "Faza 1: Design (5-7 zile)\nFaza 2: Dezvoltare (10-14 zile)\nFaza 3: Testare și lansare (3-5 zile)",
                        ],
                    ],
                    [
                        'id' => 'terms_1',
                        'type' => 'terms',
                        'visible' => true,
                        'data' => [
                            'content' => "1. Avans 50% la acceptarea ofertei, 50% la finalizare.\n2. Modificările majore după aprobarea design-ului pot implica costuri suplimentare.\n3. Clientul va furniza conținutul (texte, imagini) în format digital.\n4. Termenele sunt estimate și pot varia în funcție de feedback și modificări.",
                        ],
                    ],
                    [
                        'id' => 'signature_1',
                        'type' => 'signature',
                        'visible' => true,
                        'data' => [],
                    ],
                ],
            ],

            // Template 5: Marketing/Branding
            [
                'name' => 'Marketing & Branding',
                'is_default' => false,
                'blocks' => [
                    [
                        'id' => 'header_1',
                        'type' => 'header',
                        'visible' => true,
                        'data' => [
                            'introTitle' => 'Construim branduri memorabile',
                            'introText' => 'Strategii creative care conectează brandul tău cu publicul țintă și generează rezultate măsurabile.',
                        ],
                    ],
                    [
                        'id' => 'services_1',
                        'type' => 'services',
                        'visible' => true,
                        'data' => [],
                    ],
                    [
                        'id' => 'text_deliverables',
                        'type' => 'text',
                        'visible' => true,
                        'data' => [
                            'title' => 'Livrabile',
                            'content' => "• Fișiere sursă (AI, PSD, PDF)\n• Manual de identitate vizuală\n• Versiuni pentru print și digital\n• Fișiere optimizate pentru social media",
                        ],
                    ],
                    [
                        'id' => 'summary_1',
                        'type' => 'summary',
                        'visible' => true,
                        'data' => [],
                    ],
                    [
                        'id' => 'terms_1',
                        'type' => 'terms',
                        'visible' => true,
                        'data' => [
                            'content' => "1. Prețul include 3 runde de revizuiri.\n2. Drepturile de autor sunt transferate integral la finalizarea plății.\n3. Revizuirile suplimentare se facturează separat.",
                        ],
                    ],
                    [
                        'id' => 'signature_1',
                        'type' => 'signature',
                        'visible' => true,
                        'data' => [],
                    ],
                ],
            ],

            // Template 6: Consulting/Services
            [
                'name' => 'Consultanță',
                'is_default' => false,
                'blocks' => [
                    [
                        'id' => 'header_1',
                        'type' => 'header',
                        'visible' => true,
                        'data' => [
                            'introTitle' => 'Expertiză pentru succesul afacerii tale',
                            'introText' => 'Oferim consultanță specializată și soluții personalizate pentru provocările tale de business.',
                        ],
                    ],
                    [
                        'id' => 'text_approach',
                        'type' => 'text',
                        'visible' => true,
                        'data' => [
                            'title' => 'Abordare',
                            'content' => 'Metodologia noastră include analiza situației actuale, identificarea oportunităților și implementarea soluțiilor.',
                        ],
                    ],
                    [
                        'id' => 'services_1',
                        'type' => 'services',
                        'visible' => true,
                        'data' => [],
                    ],
                    [
                        'id' => 'summary_1',
                        'type' => 'summary',
                        'visible' => true,
                        'data' => [],
                    ],
                    [
                        'id' => 'terms_1',
                        'type' => 'terms',
                        'visible' => true,
                        'data' => [
                            'content' => "1. Serviciile de consultanță se facturează pe bază de ore sau pachete.\n2. Informațiile obținute sunt confidențiale.\n3. Reprogramările cu mai puțin de 24h în avans pot fi facturate.",
                        ],
                    ],
                    [
                        'id' => 'signature_1',
                        'type' => 'signature',
                        'visible' => true,
                        'data' => [],
                    ],
                ],
            ],
        ];
    }
}

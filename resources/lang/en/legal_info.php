<?php

/*
 * Mandatory product information and seller details: __('webshop::legal_info.*'). The Hungarian file (../hu/) is the
 * source; missing keys fall back to it.
 */
return [
    // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell
    'profile' => [
        'general' => 'General product safety (GPSR)',
        'food' => 'Food',
        'chemical' => 'Hazardous mixture (CLP)',
        'energy' => 'Energy label',
        'textile' => 'Textile product',
    ],

    // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell
    'profile_description' => [
        'general' => 'For non-food consumer products (Regulation (EU) 2023/988).',
        'food' => 'For food, drinks and food supplements (Regulation (EU) No 1169/2011).',
        'chemical' => 'For cleaning products, paints, adhesives and other mixtures labelled as hazardous (Regulation (EC) No 1272/2008).',
        'energy' => 'For products with an energy label: household appliances, lighting, TVs, displays, etc. (Regulation (EU) 2017/1369).',
        'textile' => 'For clothing and home textiles (Regulation (EU) No 1007/2011).',
    ],

    // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell
    'field' => [
        'manufacturer' => 'Manufacturer',
        'eu_responsible' => 'Responsible person in the EU',
        'identifier' => 'Product identifier',
        'warnings' => 'Warnings and safety information',
        'ingredients' => 'Ingredients',
        'allergens' => 'Allergens',
        'nutrition' => 'Nutrition declaration per 100 g / 100 ml',
        'storage' => 'Storage conditions and conditions of use',
        'origin' => 'Country of origin / place of provenance',
        'alcohol' => 'Alcoholic strength',
        'operator' => 'Food business operator',
        'signal_word' => 'Signal word',
        'pictograms' => 'Hazard pictograms',
        'hazard_statements' => 'Hazard statements (H)',
        'precautionary_statements' => 'Precautionary statements (P)',
        'ufi' => 'UFI code',
        'energy_class' => 'Energy efficiency class',
        'label_url' => 'Energy label (image or PDF link)',
        'sheet_url' => 'Product information sheet link',
        'fibre_composition' => 'Fibre composition',
        'care' => 'Care instructions',
    ],

    'hint' => [
        'manufacturer' => 'Name (or registered trade name), postal address and electronic contact (e-mail or website).',
        'eu_responsible' => 'Only if the manufacturer is not established in the EU: name, postal and electronic address of the responsible economic operator.',
        'identifier' => 'Type, model, batch or serial number – whatever identifies the product.',
        'warnings' => 'The warnings shown on the product/packaging, in Hungarian (e.g. “Not suitable for children under 3 years”).',
        'ingredients' => 'Highlight allergens with double asterisks, e.g. **wheat flour**, **milk** – they appear in bold on the product page.',
        'allergens' => 'E.g. “Contains gluten and milk. May contain traces of nuts.”',
        'alcohol' => 'Mandatory above 1.2% by volume, e.g. “12.5% vol”.',
        'operator' => 'Name and address of the operator under whose name the product is marketed.',
        'hazard_statements' => 'The H statements on the label, e.g. “H319 Causes serious eye irritation.”',
        'sheet_url' => 'E.g. the product page in the EPREL database.',
        'fibre_composition' => 'E.g. “95% cotton, 5% elastane”.',
    ],

    // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell
    'signal_word' => [
        'warning' => 'Warning',
        'danger' => 'Danger',
    ],

    // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell
    'pictogram' => [
        'ghs01' => 'Explosive',
        'ghs02' => 'Flammable',
        'ghs03' => 'Oxidising',
        'ghs04' => 'Gas under pressure',
        'ghs05' => 'Corrosive',
        'ghs06' => 'Acute toxicity',
        'ghs07' => 'Harmful, irritant',
        'ghs08' => 'Serious health hazard',
        'ghs09' => 'Hazardous to the environment',
    ],

    // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell
    'nutrition' => [
        'energy' => 'Energy (kJ / kcal)',
        'fat' => 'Fat',
        'saturates' => '– of which saturates',
        'carbohydrate' => 'Carbohydrate',
        'sugars' => '– of which sugars',
        'fibre' => 'Fibre',
        'protein' => 'Protein',
        'salt' => 'Salt',
    ],

    // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell
    'net_quantity' => 'Net quantity',

    'seller' => [
        'name' => 'Seller name (company name / sole trader name)',
        'seat' => 'Registered office',
        'registration_number' => 'Company registration number / registration number',
        'tax_number' => 'Tax number',
        'email' => 'E-mail address',
        'phone' => 'Phone number',
    ],
];

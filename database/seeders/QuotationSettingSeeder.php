<?php

namespace Database\Seeders;

use App\Models\QuotationSetting;
use Illuminate\Database\Seeder;

class QuotationSettingSeeder extends Seeder
{
    public function run(): void
    {
        QuotationSetting::set('default_notes', '<p>1. The above price includes installation and commissioning.</p><p>2. Warranty period: 2 years from the date of commissioning.</p><p>3. Price validity: 30 days from the date of this quotation.</p>', 'textarea');

        QuotationSetting::set('default_remarks', '<p>1. Payment Terms: 30% deposit upon order confirmation, 70% balance before shipment.</p><p>2. Delivery Time: 30-45 working days after receipt of deposit.</p><p>3. Shipping: FOB / CIF (negotiable).</p>', 'textarea');
    }
}

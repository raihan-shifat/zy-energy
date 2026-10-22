<?php

// Text of the legal pages (resources/views/themes/xylo/legal). Draft pending legal review.
// Lines marked "HTML" are printed unescaped; their :parameters are escaped in the template.

return [
    'draft_notice' => [
        'label' => 'DRAFT - pending legal review.',
        'text' => 'This page is a placeholder structure, not final legal text. Items in [square brackets] must be completed and the whole page reviewed before it is relied on.',
    ],

    'common' => [
        'contact' => 'Contact',
        'cookies' => 'Cookies',
        'data_we_collect' => 'Data we collect',
        'how_we_use_it' => 'How we use it',
        'last_updated' => 'Last updated',
        'address' => 'Address: :address',
        'contact_email_placeholder' => '[contact email]',
        'draft_prepared' => 'Draft prepared :date. [Replace with the date this policy is approved and published.]',
        'privacy_link' => 'Privacy Policy',
        'cookie_link' => 'Cookie Policy',
    ],

    'terms' => [
        'title' => 'Terms of Use',
        'meta_description' => 'Terms of Use of :site (draft pending legal review).',
        'intro' => 'These terms apply to your use of this website, operated by :site ([company legal name], [registered address]).',
        'use_heading' => 'Use of the website',
        'use_text' => '[Permitted use of the site and what is not allowed.]',
        'products_heading' => 'Product information and quotations',
        'products_text' => 'Product pages are for information. Prices are given on request through a quotation. [State whether quotations are binding, how long they are valid, and which terms of sale apply to orders.]',
        'data_text' => 'How we handle personal data is described in the :link.', // HTML
        'cookies_text' => 'See the :link.', // HTML
        'ip_heading' => 'Intellectual property',
        'ip_text' => '[Ownership of the website content, logos and product images, and permitted use.]',
        'liability_heading' => 'Liability',
        'liability_text' => '[Limitation of liability, to be written by your legal adviser.]',
        'law_heading' => 'Governing law',
        'law_text' => '[Applicable law and competent courts.]',
        'draft_prepared' => 'Draft prepared :date. [Replace with the date these terms are approved and published.]',
    ],

    'privacy' => [
        'title' => 'Privacy Policy',
        'meta_description' => 'Privacy Policy of :site (draft pending legal review).',
        'intro' => 'This policy explains how :site ("we") handles personal data collected through this website. Operator: [company legal name], [registered address], [registration number].',
        'collect_intro' => 'Based on how this website currently works, we collect:',
        'collect_enquiry' => '<strong>Enquiry form:</strong> name, company, email address, phone / WhatsApp number, country, the product or category you ask about, your message, and the page the form was sent from.', // HTML
        'collect_account' => '<strong>Customer account</strong> (only if you register): the details you enter in your profile.', // HTML
        'collect_visits' => '<strong>Visit log:</strong> for each visit, a session identifier, the date, your IP address and your browser\'s user-agent string.', // HTML
        'collect_cookies' => '<strong>Cookies:</strong> see the :link.', // HTML
        'collect_other' => '[Add any other data collected offline or through other channels, e.g. email, WhatsApp, WeChat.]',
        'use_enquiry' => 'To answer your enquiry and, if you ask for one, prepare a quotation.',
        'use_security' => 'To run and secure the website (for example, the visit log).',
        'use_other' => '[Other purposes, if any, e.g. sending product updates - only with consent.]',
        'use_basis' => '[Legal basis for each purpose, how long each type of data is kept, who it is shared with (e.g. hosting provider, email provider), and whether it is transferred outside your country.]',
        'cookies_analytics' => 'This website uses the cookies it needs to work (session, security and language choice) and, only if you accept the cookie notice, Google Analytics cookies to measure how the site is used. Analytics events do not include your name, email, phone number or message. Details are in the :link.', // HTML
        'cookies_essential' => 'This website uses only the cookies it needs to work (session, security and language choice). It does not use analytics or advertising cookies at this time. Details are in the :link.', // HTML
        'rights_heading' => 'Your rights',
        'rights_text' => '[Rights available to you under applicable law, e.g. access, correction, deletion, objection, and how to exercise them.]',
        'contact_text' => 'For questions about this policy or your data, contact us at :email.', // HTML
    ],

    'cookies' => [
        'title' => 'Cookie Policy',
        'meta_description' => 'Cookie Policy of :site (draft pending legal review).',
        'intro' => 'This page lists the cookies this website sets and why.',
        'collect_heading' => 'Data we collect with cookies',
        'collect_intro' => 'The website sets these cookies. The first three are needed for the site to work:',
        'col_cookie' => 'Cookie',
        'col_purpose' => 'Purpose',
        'col_duration' => 'Duration',
        'session_purpose' => 'Keeps your session (for example, the enquiry form and login) working between pages.',
        'xsrf_purpose' => 'Security: protects forms against cross-site request forgery.',
        'locale_purpose' => 'Remembers the language you chose.',
        'ga_purpose' => 'Google Analytics: counts visits and how the site is used. <strong>Only set if you accept</strong> the cookie notice.', // HTML
        'duration_2_hours' => '2 hours',
        'duration_1_year' => '1 year',
        'duration_ga' => 'Up to 2 years [confirm in Google Analytics settings]',
        'analytics_text' => 'Analytics cookies are optional. Your choice is stored in your browser (localStorage, key :key). Analytics is not loaded until you accept. Events we measure (for example "WhatsApp button clicked" or "enquiry form sent") do not include your name, email, phone number or message.', // HTML
        'no_analytics_text' => 'We do not use analytics, advertising or social-media tracking cookies at this time. If that changes, this page will be updated and you will be asked for consent first.',
        'use_text' => 'These cookies are used only to provide the website you asked for. They are not used for profiling or advertising.',
        'third_party_heading' => 'Third-party content',
        'third_party_text' => 'To display the site, your browser also loads files (fonts, scripts, icons, flag images) from third-party services such as Google Fonts, jsDelivr, cdnjs, code.jquery.com and flagcdn.com. These services receive your IP address when your browser requests the files. [Confirm whether this needs to be listed under applicable law.]',
        'manage_text' => 'You can block or delete cookies in your browser settings. If you block the essential cookies above, forms and language selection may not work.',
        'contact_text' => 'Questions about cookies: :email. See also the :link.', // HTML
    ],
];

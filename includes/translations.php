<?php

declare(strict_types=1);

/**
 * Static UI strings for the public form only (labels, buttons, validation
 * messages, thank-you/inactive/not-found copy). Admin-entered data — expo
 * name/location, interest option names — is never translated here; those
 * are data-driven per CLAUDE.md's rule that interests must stay a data
 * change, not a code change. See t() in helpers.php.
 */
return [
    'en' => [
        'expo_not_found_title' => "We couldn't find that expo",
        'expo_not_found_body'  => 'Please check the QR code or link and try again.',
        'expo_inactive_body'   => "This expo isn't currently accepting submissions. Thank you for your interest — please check back later or speak to a Waterlift Solar representative directly.",

        'err_csrf'              => 'Your session expired. Please refresh the page and try again.',
        'err_full_name_required' => 'Full name is required.',
        'err_phone_required'     => 'Phone number is required.',
        'err_location_required'  => 'Project location is required.',
        'err_interest_required'  => 'Please select at least one interest.',
        'err_other_required'     => 'Please describe your "Other" interest.',
        'err_followup_required'  => 'Please choose a follow-up method.',
        'err_email_invalid'      => 'Please enter a valid email address.',
        'err_message_too_long'   => 'Message is too long.',
        'err_rate_limited'       => 'You just submitted this form. Please wait a moment before submitting again.',

        'section_your_details'    => 'Your Details',
        'section_your_interests'  => 'Your Interests',
        'section_followup'        => 'Preferred Follow-up Method',
        'section_anything_else'   => 'Anything Else?',
        'optional_label'          => '(optional)',

        'field_full_name'        => 'Full Name',
        'field_phone'            => 'Phone Number',
        'field_project_location' => 'Project Location',
        'field_email'            => 'Email',
        'field_message'          => 'Message',
        'other_placeholder'      => 'Please describe',

        'followup_phone_call' => 'Phone Call',
        'followup_whatsapp'   => 'WhatsApp',
        'followup_email'      => 'Email',

        'btn_submit'      => 'Submit',
        'honeypot_label'  => 'Leave this field blank',

        'success_title'      => 'Thank You!',
        'success_with_expo'  => 'Thanks for stopping by our %s booth. A member of the Waterlift Solar team will be in touch soon.',
        'success_generic'    => 'Thanks for your interest in Waterlift Solar. A member of our team will be in touch soon.',
        'save_contact'       => 'Save Our Contact',
        'download_profile'   => 'Download Company Profile (PDF)',
    ],
    'sw' => [
        'expo_not_found_title' => 'Hatukuweza kupata tukio hilo',
        'expo_not_found_body'  => 'Tafadhali angalia msimbo wa QR au kiungo kisha ujaribu tena.',
        'expo_inactive_body'   => 'Tukio hili halipokei maombi kwa sasa. Asante kwa kupendezwa kwako — tafadhali angalia tena baadaye au zungumza moja kwa moja na mwakilishi wa Waterlift Solar.',

        'err_csrf'                => 'Muda wa kikao chako umeisha. Tafadhali onyesha upya ukurasa kisha ujaribu tena.',
        'err_full_name_required'  => 'Jina kamili linahitajika.',
        'err_phone_required'      => 'Nambari ya simu inahitajika.',
        'err_location_required'   => 'Mahali pa mradi panahitajika.',
        'err_interest_required'   => 'Tafadhali chagua angalau jambo moja unalopendezwa nalo.',
        'err_other_required'      => 'Tafadhali eleza jambo lako "Lingine".',
        'err_followup_required'   => 'Tafadhali chagua njia ya mawasiliano.',
        'err_email_invalid'       => 'Tafadhali weka anwani sahihi ya barua pepe.',
        'err_message_too_long'    => 'Ujumbe ni mrefu mno.',
        'err_rate_limited'        => 'Umeshatuma fomu hii sasa hivi. Tafadhali subiri kidogo kabla ya kutuma tena.',

        'section_your_details'    => 'Maelezo Yako',
        'section_your_interests'  => 'Mambo Unayopendezwa Nayo',
        'section_followup'        => 'Njia Unayopendelea ya Mawasiliano',
        'section_anything_else'   => 'Chochote Kingine?',
        'optional_label'          => '(hiari)',

        'field_full_name'        => 'Jina Kamili',
        'field_phone'            => 'Nambari ya Simu',
        'field_project_location' => 'Mahali pa Mradi',
        'field_email'            => 'Barua Pepe',
        'field_message'          => 'Ujumbe',
        'other_placeholder'      => 'Tafadhali eleza',

        'followup_phone_call' => 'Simu',
        'followup_whatsapp'   => 'WhatsApp',
        'followup_email'      => 'Barua Pepe',

        'btn_submit'      => 'Tuma',
        'honeypot_label'  => 'Acha sehemu hii wazi',

        'success_title'      => 'Asante!',
        'success_with_expo'  => 'Asante kwa kutembelea banda letu la %s. Mwanachama wa timu ya Waterlift Solar atawasiliana nawe hivi karibuni.',
        'success_generic'    => 'Asante kwa kupendezwa kwako na Waterlift Solar. Mwanachama wa timu yetu atawasiliana nawe hivi karibuni.',
        'save_contact'       => 'Hifadhi Anwani Yetu',
        'download_profile'   => 'Pakua Wasifu wa Kampuni (PDF)',
    ],
];

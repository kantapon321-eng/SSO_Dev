<?php
return [
    // เวลาอนุโลมของ st/entry (นาที)
    'max_skew_minutes' => 10,

    // IP allowlist (ปิดไว้ก่อน)
    'ip_allowlist_enabled' => false,
    'ip_allowlist' => [
        // 'sample.ip',
    ],

    // map JuristicType (i-industry) -> juristic_status (ระบบเรา)
    // ถ้าระบบคุณใช้โค้ดอื่น แก้ฝั่งขวาได้เลย
    'status_map' => [
        '1' => '1', // บุคคลธรรมดา
        '2' => '2', // นิติบุคคล
    ],

    // iindustry uid -> pid
    'strict_uid_match_for_juristic' => false, // ถ้าบริษัทอนุญาตหลายคน true/false ได้

    // blocklist 
    'blocklist_tax' => array_filter(array_map('trim', explode(',', env('SSO_BLOCKLIST_TAX', '')))),
];

<?php

// Machine-drafted (2026-10-19) by Claude from en/legal.php. The source text is itself a
// draft pending legal review: have the final Chinese text reviewed by a lawyer and a
// native speaker before relying on it.
// Lines marked "HTML" are printed unescaped; their :parameters are escaped in the template.

return [
    'draft_notice' => [
        'label' => '草稿 - 待法律审核。',
        'text' => '本页面仅为占位结构，并非最终法律文本。方括号 [ ] 中的内容必须补充完整，且整页须经审核后方可作为依据。',
    ],

    'common' => [
        'contact' => '联系方式',
        'cookies' => 'Cookie',
        'data_we_collect' => '我们收集的数据',
        'how_we_use_it' => '我们如何使用这些数据',
        'last_updated' => '最后更新',
        'address' => '地址：:address',
        'contact_email_placeholder' => '[联系邮箱]',
        'draft_prepared' => '草稿编写于 :date。[请替换为本政策获批并发布的日期。]',
        'privacy_link' => '隐私政策',
        'cookie_link' => 'Cookie 政策',
    ],

    'terms' => [
        'title' => '使用条款',
        'meta_description' => ':site 使用条款（草稿，待法律审核）。',
        'intro' => '本条款适用于您对本网站的使用。本网站由 :site（[公司法定名称]，[注册地址]）运营。',
        'use_heading' => '网站的使用',
        'use_text' => '[允许的网站用途以及禁止的行为。]',
        'products_heading' => '产品信息与报价',
        'products_text' => '产品页面仅供参考。价格需通过询价报价获取。[说明报价是否具有约束力、有效期多长，以及订单适用哪些销售条款。]',
        'data_text' => '我们处理个人数据的方式详见 :link。', // HTML
        'cookies_text' => '请参阅 :link。', // HTML
        'ip_heading' => '知识产权',
        'ip_text' => '[网站内容、标志和产品图片的所有权及允许的使用方式。]',
        'liability_heading' => '责任',
        'liability_text' => '[责任限制条款，由您的法律顾问撰写。]',
        'law_heading' => '适用法律',
        'law_text' => '[适用法律及管辖法院。]',
        'draft_prepared' => '草稿编写于 :date。[请替换为本条款获批并发布的日期。]',
    ],

    'privacy' => [
        'title' => '隐私政策',
        'meta_description' => ':site 隐私政策（草稿，待法律审核）。',
        'intro' => '本政策说明 :site（“我们”）如何处理通过本网站收集的个人数据。运营方：[公司法定名称]，[注册地址]，[注册号]。',
        'collect_intro' => '根据本网站目前的运行方式，我们会收集：',
        'collect_enquiry' => '<strong>询价表单：</strong>姓名、公司、邮箱地址、电话 / WhatsApp 号码、国家、您所询问的产品或分类、您的留言，以及提交表单的页面。', // HTML
        'collect_account' => '<strong>客户账户</strong>（仅在您注册时）：您在个人资料中填写的信息。', // HTML
        'collect_visits' => '<strong>访问日志：</strong>每次访问的会话标识、日期、您的 IP 地址以及浏览器的 User-Agent 字符串。', // HTML
        'collect_cookies' => '<strong>Cookie：</strong>请参阅 :link。', // HTML
        'collect_other' => '[补充通过线下或其他渠道收集的数据，例如邮件、WhatsApp、微信。]',
        'use_enquiry' => '用于回复您的询价，并在您需要时编制报价单。',
        'use_security' => '用于运行网站并保障其安全（例如访问日志）。',
        'use_other' => '[其他用途（如有），例如发送产品更新 - 仅在获得同意后。]',
        'use_basis' => '[每项用途的法律依据、各类数据的保存期限、与谁共享（例如主机服务商、邮件服务商），以及是否会传输到您所在国家以外。]',
        'cookies_analytics' => '本网站使用其正常运行所需的 Cookie（会话、安全和语言选择），并且仅在您接受 Cookie 提示后，使用 Google Analytics Cookie 来统计网站的使用情况。分析事件不包含您的姓名、邮箱、电话号码或留言。详情请参阅 :link。', // HTML
        'cookies_essential' => '本网站仅使用其正常运行所需的 Cookie（会话、安全和语言选择），目前不使用分析或广告 Cookie。详情请参阅 :link。', // HTML
        'rights_heading' => '您的权利',
        'rights_text' => '[根据适用法律您享有的权利，例如访问、更正、删除、反对，以及行使这些权利的方式。]',
        'contact_text' => '如对本政策或您的数据有任何疑问，请通过 :email 联系我们。', // HTML
    ],

    'cookies' => [
        'title' => 'Cookie 政策',
        'meta_description' => ':site Cookie 政策（草稿，待法律审核）。',
        'intro' => '本页面列出本网站设置的 Cookie 及其用途。',
        'collect_heading' => '我们通过 Cookie 收集的数据',
        'collect_intro' => '本网站会设置以下 Cookie，其中前三个是网站正常运行所必需的：',
        'col_cookie' => 'Cookie',
        'col_purpose' => '用途',
        'col_duration' => '有效期',
        'session_purpose' => '在页面之间保持您的会话（例如询价表单和登录）正常工作。',
        'xsrf_purpose' => '安全：保护表单免受跨站请求伪造攻击。',
        'locale_purpose' => '记住您选择的语言。',
        'ga_purpose' => 'Google Analytics：统计访问量及网站的使用情况。<strong>仅在您接受</strong> Cookie 提示后设置。', // HTML
        'duration_2_hours' => '2 小时',
        'duration_1_year' => '1 年',
        'duration_ga' => '最长 2 年 [请在 Google Analytics 设置中确认]',
        'analytics_text' => '分析 Cookie 是可选的。您的选择保存在浏览器中（localStorage，键名 :key）。在您接受之前不会加载分析功能。我们统计的事件（例如“点击 WhatsApp 按钮”或“提交询价表单”）不包含您的姓名、邮箱、电话号码或留言。', // HTML
        'no_analytics_text' => '我们目前不使用分析、广告或社交媒体跟踪 Cookie。如有变化，我们将更新本页面，并事先征求您的同意。',
        'use_text' => '这些 Cookie 仅用于提供您所访问的网站服务，不用于用户画像或广告。',
        'third_party_heading' => '第三方内容',
        'third_party_text' => '为显示网站，您的浏览器还会从第三方服务（例如 Google Fonts、jsDelivr、cdnjs、code.jquery.com 和 flagcdn.com）加载文件（字体、脚本、图标、国旗图片）。当您的浏览器请求这些文件时，这些服务会获得您的 IP 地址。[请确认根据适用法律是否需要列出此项。]',
        'manage_text' => '您可以在浏览器设置中阻止或删除 Cookie。如果您阻止上述必要 Cookie，表单和语言选择可能无法正常使用。',
        'contact_text' => '有关 Cookie 的问题：:email。另请参阅 :link。', // HTML
    ],
];

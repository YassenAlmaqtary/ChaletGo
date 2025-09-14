<?php

return [
    'pages' => [
        'auth' => [
            'login' => [
                'heading' => 'تسجيل الدخول',
                'actions' => [
                    'authenticate' => [
                        'label' => 'تسجيل الدخول',
                    ],
                ],
                'fields' => [
                    'email' => [
                        'label' => 'البريد الإلكتروني',
                    ],
                    'password' => [
                        'label' => 'كلمة المرور',
                    ],
                    'remember' => [
                        'label' => 'تذكرني',
                    ],
                ],
                'messages' => [
                    'failed' => 'بيانات الاعتماد هذه لا تتطابق مع سجلاتنا.',
                ],
            ],
        ],
        'dashboard' => [
            'title' => 'لوحة التحكم',
            'heading' => 'لوحة التحكم',
        ],
    ],
    'layout' => [
        'actions' => [
            'logout' => [
                'label' => 'تسجيل الخروج',
            ],
            'open_database_notifications' => [
                'label' => 'فتح الإشعارات',
            ],
            'open_user_menu' => [
                'label' => 'قائمة المستخدم',
            ],
            'sidebar' => [
                'collapse' => [
                    'label' => 'طي الشريط الجانبي',
                ],
                'expand' => [
                    'label' => 'توسيع الشريط الجانبي',
                ],
            ],
            'theme_switcher' => [
                'dark' => [
                    'label' => 'تفعيل الوضع المظلم',
                ],
                'light' => [
                    'label' => 'تفعيل الوضع المضيء',
                ],
                'system' => [
                    'label' => 'تفعيل وضع النظام',
                ],
            ],
        ],
    ],
    'pages' => [
        'health_check_results' => [
            'buttons' => [
                'refresh' => [
                    'label' => 'تحديث',
                ],
            ],
            'heading' => 'صحة التطبيق',
            'navigation' => [
                'label' => 'صحة التطبيق',
            ],
            'notifications' => [
                'check_results' => 'فحص النتائج من',
            ],
        ],
    ],
    'resources' => [
        'pages' => [
            'create_record' => [
                'title' => 'إنشاء :label',
                'breadcrumb' => 'إنشاء',
                'form' => [
                    'actions' => [
                        'cancel' => [
                            'label' => 'إلغاء',
                        ],
                        'create' => [
                            'label' => 'إنشاء',
                        ],
                        'create_another' => [
                            'label' => 'إنشاء وإنشاء آخر',
                        ],
                    ],
                ],
                'notifications' => [
                    'created' => [
                        'title' => 'تم الإنشاء',
                    ],
                ],
            ],
            'edit_record' => [
                'title' => 'تعديل :label',
                'breadcrumb' => 'تعديل',
                'form' => [
                    'actions' => [
                        'cancel' => [
                            'label' => 'إلغاء',
                        ],
                        'save' => [
                            'label' => 'حفظ التغييرات',
                        ],
                    ],
                ],
                'content' => [
                    'tab' => [
                        'label' => 'تعديل',
                    ],
                ],
                'notifications' => [
                    'saved' => [
                        'title' => 'تم الحفظ',
                    ],
                ],
            ],
            'list_records' => [
                'title' => ':label',
                'navigation_label' => ':label',
                'breadcrumb' => ':label',
                'table' => [
                    'heading' => ':label',
                ],
            ],
            'view_record' => [
                'title' => 'عرض :label',
                'breadcrumb' => 'عرض',
            ],
        ],
        'relation_managers' => [
            'title' => ':label',
            'breadcrumb' => ':label',
        ],
    ],
    'unsaved_changes_alert' => [
        'title' => 'تغييرات غير محفوظة',
        'body' => 'هل أنت متأكد من أنك تريد المغادرة؟ تغييراتك لن يتم حفظها.',
        'actions' => [
            'confirm' => [
                'label' => 'المغادرة',
            ],
            'cancel' => [
                'label' => 'البقاء',
            ],
        ],
    ],
];

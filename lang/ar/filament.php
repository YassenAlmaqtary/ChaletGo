<?php

return [
    'actions' => [
        'attach' => 'إرفاق',
        'attach_and_detach' => 'إرفاق وإلغاء إرفاق',
        'attach_another' => 'إرفاق آخر',
        'cancel' => 'إلغاء',
        'create' => 'إنشاء',
        'create_and_create_another' => 'إنشاء وإنشاء آخر',
        'create_another' => 'إنشاء آخر',
        'delete' => 'حذف',
        'detach' => 'إلغاء إرفاق',
        'edit' => 'تعديل',
        'filter' => 'فلترة',
        'open' => 'فتح',
        'replicate' => 'تكرار',
        'save' => 'حفظ',
        'save_and_create_another' => 'حفظ وإنشاء آخر',
        'save_and_edit' => 'حفظ ومتابعة التعديل',
        'view' => 'عرض',
        'close' => 'إغلاق',
        'confirm' => 'تأكيد',
        'export' => 'تصدير',
        'import' => 'استيراد',
        'print' => 'طباعة',
        'refresh' => 'تحديث',
        'reset' => 'إعادة تعيين',
        'search' => 'بحث',
        'select_all' => 'تحديد الكل',
        'submit' => 'إرسال',
        'toggle_navigation' => 'تبديل التنقل',
    ],

    'fields' => [
        'bulk_select_page' => [
            'label' => 'تحديد/إلغاء تحديد جميع العناصر للإجراءات المجمعة.',
        ],
        'bulk_select_record' => [
            'label' => 'تحديد/إلغاء تحديد العنصر :key للإجراءات المجمعة.',
        ],
        'search' => [
            'label' => 'بحث',
            'placeholder' => 'بحث...',
            'indicator' => 'بحث',
        ],
    ],

    'pagination' => [
        'label' => 'التنقل بين الصفحات',
        'overview' => 'عرض :first إلى :last من أصل :total نتيجة',
        'fields' => [
            'records_per_page' => [
                'label' => 'لكل صفحة',
                'options' => [
                    'all' => 'الكل',
                ],
            ],
        ],
        'actions' => [
            'go_to_page' => [
                'label' => 'الذهاب إلى الصفحة :page',
            ],
            'next' => [
                'label' => 'التالي',
            ],
            'previous' => [
                'label' => 'السابق',
            ],
        ],
    ],

    'buttons' => [
        'dark_mode' => 'الوضع المظلم',
        'light_mode' => 'الوضع المضيء',
        'logout' => 'تسجيل الخروج',
        'sidebar' => [
            'collapse' => 'طي الشريط الجانبي',
            'expand' => 'توسيع الشريط الجانبي',
        ],
    ],

    'widgets' => [
        'account' => [
            'heading' => 'مرحباً، :name',
        ],
        'filament_info' => [
            'heading' => 'معلومات Filament',
        ],
    ],

    'modal' => [
        'confirmation' => [
            'actions' => [
                'cancel' => [
                    'label' => 'إلغاء',
                ],
                'confirm' => [
                    'label' => 'تأكيد',
                ],
            ],
        ],
    ],

    'notifications' => [
        'database' => [
            'modal' => [
                'heading' => 'الإشعارات',
                'actions' => [
                    'clear' => [
                        'label' => 'مسح',
                    ],
                    'mark_all_as_read' => [
                        'label' => 'تمييز الكل كمقروء',
                    ],
                ],
                'empty' => [
                    'heading' => 'لا توجد إشعارات',
                    'description' => 'يرجى المراجعة لاحقاً',
                ],
            ],
        ],
    ],

    'tables' => [
        'actions' => [
            'bulk_actions' => [
                'label' => 'الإجراءات المجمعة',
            ],
            'delete' => [
                'label' => 'حذف',
                'modal' => [
                    'heading' => 'حذف :label',
                    'description' => 'هل أنت متأكد من أنك تريد حذف هذا؟ لا يمكن التراجع عن هذا الإجراء.',
                    'actions' => [
                        'delete' => [
                            'label' => 'حذف',
                        ],
                    ],
                ],
            ],
            'edit' => [
                'label' => 'تعديل',
            ],
            'open' => [
                'label' => 'فتح',
            ],
            'replicate' => [
                'label' => 'تكرار',
            ],
            'view' => [
                'label' => 'عرض',
            ],
        ],
        'bulk_actions' => [
            'delete' => [
                'label' => 'حذف المحدد',
                'modal' => [
                    'heading' => 'حذف :label المحدد',
                    'description' => 'هل أنت متأكد من أنك تريد حذف العناصر المحددة؟ لا يمكن التراجع عن هذا الإجراء.',
                    'actions' => [
                        'delete' => [
                            'label' => 'حذف',
                        ],
                    ],
                ],
            ],
        ],
        'columns' => [
            'text' => [
                'more_list_items' => 'و :count أكثر',
            ],
        ],
        'fields' => [
            'bulk_select_page' => [
                'label' => 'تحديد/إلغاء تحديد جميع العناصر للإجراءات المجمعة.',
            ],
            'bulk_select_record' => [
                'label' => 'تحديد/إلغاء تحديد العنصر :key للإجراءات المجمعة.',
            ],
            'search' => [
                'label' => 'بحث',
                'placeholder' => 'بحث...',
                'indicator' => 'بحث',
            ],
        ],
        'filters' => [
            'actions' => [
                'remove' => [
                    'label' => 'إزالة الفلتر',
                ],
                'remove_all' => [
                    'label' => 'إزالة جميع الفلاتر',
                    'tooltip' => 'إزالة جميع الفلاتر',
                ],
                'reset' => [
                    'label' => 'إعادة تعيين',
                ],
            ],
            'heading' => 'الفلاتر',
            'indicator' => 'الفلاتر النشطة',
            'multi_select' => [
                'placeholder' => 'الكل',
            ],
            'select' => [
                'placeholder' => 'الكل',
            ],
            'trinary' => [
                'placeholder' => 'الكل',
            ],
        ],
        'grouping' => [
            'fields' => [
                'group' => [
                    'label' => 'تجميع حسب',
                    'placeholder' => 'تجميع حسب',
                ],
                'direction' => [
                    'label' => 'اتجاه التجميع',
                    'options' => [
                        'asc' => 'تصاعدي',
                        'desc' => 'تنازلي',
                    ],
                ],
            ],
        ],
        'reorder_indicator' => 'اسحب وأفلت السجلات بالترتيب.',
        'selection_indicator' => [
            'selected_count' => 'تم تحديد عنصر واحد|تم تحديد :count عنصر',
            'actions' => [
                'select_all' => [
                    'label' => 'تحديد جميع :count',
                ],
                'deselect_all' => [
                    'label' => 'إلغاء تحديد الكل',
                ],
            ],
        ],
        'sorting' => [
            'fields' => [
                'column' => [
                    'label' => 'ترتيب حسب',
                ],
                'direction' => [
                    'label' => 'اتجاه الترتيب',
                    'options' => [
                        'asc' => 'تصاعدي',
                        'desc' => 'تنازلي',
                    ],
                ],
            ],
        ],
    ],

    'forms' => [
        'are_you_sure' => 'هل أنت متأكد؟',
        'components' => [
            'builder' => [
                'actions' => [
                    'clone' => [
                        'label' => 'استنساخ',
                    ],
                    'add' => [
                        'label' => 'إضافة إلى :label',
                    ],
                    'add_between' => [
                        'label' => 'إدراج',
                    ],
                    'delete' => [
                        'label' => 'حذف',
                    ],
                    'reorder' => [
                        'label' => 'نقل',
                    ],
                    'move_down' => [
                        'label' => 'نقل لأسفل',
                    ],
                    'move_up' => [
                        'label' => 'نقل لأعلى',
                    ],
                    'collapse' => [
                        'label' => 'طي',
                    ],
                    'expand' => [
                        'label' => 'توسيع',
                    ],
                    'collapse_all' => [
                        'label' => 'طي الكل',
                    ],
                    'expand_all' => [
                        'label' => 'توسيع الكل',
                    ],
                ],
            ],
            'file_upload' => [
                'editor' => [
                    'actions' => [
                        'cancel' => [
                            'label' => 'إلغاء',
                        ],
                        'drag_crop' => [
                            'label' => 'وضع السحب "قص"',
                        ],
                        'drag_move' => [
                            'label' => 'وضع السحب "نقل"',
                        ],
                        'flip_horizontal' => [
                            'label' => 'قلب الصورة أفقياً',
                        ],
                        'flip_vertical' => [
                            'label' => 'قلب الصورة عمودياً',
                        ],
                        'move_down' => [
                            'label' => 'نقل الصورة لأسفل',
                        ],
                        'move_left' => [
                            'label' => 'نقل الصورة لليسار',
                        ],
                        'move_right' => [
                            'label' => 'نقل الصورة لليمين',
                        ],
                        'move_up' => [
                            'label' => 'نقل الصورة لأعلى',
                        ],
                        'reset' => [
                            'label' => 'إعادة تعيين',
                        ],
                        'rotate_left' => [
                            'label' => 'تدوير الصورة لليسار',
                        ],
                        'rotate_right' => [
                            'label' => 'تدوير الصورة لليمين',
                        ],
                        'save' => [
                            'label' => 'حفظ',
                        ],
                        'zoom_100' => [
                            'label' => 'تكبير الصورة إلى 100%',
                        ],
                        'zoom_in' => [
                            'label' => 'تكبير',
                        ],
                        'zoom_out' => [
                            'label' => 'تصغير',
                        ],
                    ],
                ],
            ],
            'key_value' => [
                'actions' => [
                    'add' => [
                        'label' => 'إضافة صف',
                    ],
                    'delete' => [
                        'label' => 'حذف صف',
                    ],
                    'reorder' => [
                        'label' => 'إعادة ترتيب صف',
                    ],
                ],
                'fields' => [
                    'key' => [
                        'label' => 'المفتاح',
                    ],
                    'value' => [
                        'label' => 'القيمة',
                    ],
                ],
            ],
            'markdown_editor' => [
                'toolbar_buttons' => [
                    'attach_files' => 'إرفاق ملفات',
                    'blockquote' => 'اقتباس',
                    'bold' => 'عريض',
                    'bullet_list' => 'قائمة نقطية',
                    'code_block' => 'كتلة كود',
                    'heading' => 'عنوان',
                    'italic' => 'مائل',
                    'link' => 'رابط',
                    'ordered_list' => 'قائمة مرقمة',
                    'redo' => 'إعادة',
                    'strike' => 'يتوسطه خط',
                    'table' => 'جدول',
                    'undo' => 'تراجع',
                ],
            ],
            'repeater' => [
                'actions' => [
                    'add' => [
                        'label' => 'إضافة إلى :label',
                    ],
                    'add_between' => [
                        'label' => 'إضافة بين العناصر',
                    ],
                    'delete' => [
                        'label' => 'حذف',
                    ],
                    'clone' => [
                        'label' => 'استنساخ',
                    ],
                    'reorder' => [
                        'label' => 'نقل',
                    ],
                    'move_down' => [
                        'label' => 'نقل لأسفل',
                    ],
                    'move_up' => [
                        'label' => 'نقل لأعلى',
                    ],
                    'collapse' => [
                        'label' => 'طي',
                    ],
                    'expand' => [
                        'label' => 'توسيع',
                    ],
                    'collapse_all' => [
                        'label' => 'طي الكل',
                    ],
                    'expand_all' => [
                        'label' => 'توسيع الكل',
                    ],
                ],
            ],
            'rich_editor' => [
                'dialogs' => [
                    'link' => [
                        'actions' => [
                            'link' => 'رابط',
                            'unlink' => 'إلغاء الرابط',
                        ],
                        'label' => 'URL',
                        'placeholder' => 'أدخل URL',
                    ],
                ],
                'toolbar_buttons' => [
                    'attach_files' => 'إرفاق ملفات',
                    'blockquote' => 'اقتباس',
                    'bold' => 'عريض',
                    'bullet_list' => 'قائمة نقطية',
                    'code_block' => 'كتلة كود',
                    'h1' => 'عنوان',
                    'h2' => 'عنوان',
                    'h3' => 'عنوان',
                    'italic' => 'مائل',
                    'link' => 'رابط',
                    'ordered_list' => 'قائمة مرقمة',
                    'redo' => 'إعادة',
                    'strike' => 'يتوسطه خط',
                    'underline' => 'تحته خط',
                    'undo' => 'تراجع',
                ],
            ],
            'select' => [
                'actions' => [
                    'create_option' => [
                        'modal' => [
                            'heading' => 'إنشاء',
                            'actions' => [
                                'create' => [
                                    'label' => 'إنشاء',
                                ],
                                'create_another' => [
                                    'label' => 'إنشاء وإنشاء آخر',
                                ],
                            ],
                        ],
                    ],
                    'edit_option' => [
                        'modal' => [
                            'heading' => 'تعديل',
                            'actions' => [
                                'save' => [
                                    'label' => 'حفظ',
                                ],
                            ],
                        ],
                    ],
                ],
                'boolean' => [
                    'true' => 'نعم',
                    'false' => 'لا',
                ],
                'loading_message' => 'جاري التحميل...',
                'max_items_message' => 'يمكن تحديد عنصر واحد فقط.|يمكن تحديد :count عنصر فقط.',
                'no_search_results_message' => 'لا توجد خيارات تطابق بحثك.',
                'placeholder' => 'اختر خياراً',
                'searching_message' => 'جاري البحث...',
                'search_prompt' => 'ابدأ الكتابة للبحث...',
            ],
            'tags_input' => [
                'placeholder' => 'علامة جديدة',
            ],
            'text_input' => [
                'actions' => [
                    'hide_password' => [
                        'label' => 'إخفاء كلمة المرور',
                    ],
                    'show_password' => [
                        'label' => 'إظهار كلمة المرور',
                    ],
                ],
            ],
            'toggle_buttons' => [
                'boolean' => [
                    'true' => 'نعم',
                    'false' => 'لا',
                ],
            ],
            'wizard' => [
                'actions' => [
                    'previous_step' => [
                        'label' => 'السابق',
                    ],
                    'next_step' => [
                        'label' => 'التالي',
                    ],
                ],
            ],
        ],
    ],

    'navigation' => [
        'keyBinds' => [
            'open_sidebar' => [
                'label' => 'فتح الشريط الجانبي',
            ],
            'close_sidebar' => [
                'label' => 'إغلاق الشريط الجانبي',
            ],
            'open_user_menu' => [
                'label' => 'فتح قائمة المستخدم',
            ],
            'open_main_menu' => [
                'label' => 'فتح القائمة الرئيسية',
            ],
        ],
    ],

    'global_search' => [
        'field' => [
            'label' => 'البحث العام',
            'placeholder' => 'بحث...',
        ],
        'actions' => [
            'open' => [
                'label' => 'فتح البحث العام',
            ],
            'close' => [
                'label' => 'إغلاق البحث العام',
            ],
        ],
        'no_results' => 'لا توجد نتائج.',
    ],
];

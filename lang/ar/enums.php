<?php

return [
    'user_types' => [
        'admin' => 'مدير',
        'owner' => 'مالك شاليه',
        'customer' => 'عميل',
    ],
    
    'booking_statuses' => [
        'pending' => 'في الانتظار',
        'confirmed' => 'مؤكد',
        'cancelled' => 'ملغي',
        'completed' => 'مكتمل',
    ],
    
    'payment_statuses' => [
        'pending' => 'في الانتظار',
        'completed' => 'مكتمل',
        'failed' => 'فاشل',
        'refunded' => 'مسترد',
    ],
    
    'payment_methods' => [
        'credit_card' => 'بطاقة ائتمان',
        'bank_transfer' => 'تحويل بنكي',
        'cash' => 'نقداً',
        'digital_wallet' => 'محفظة رقمية',
    ],
    
    'amenity_categories' => [
        'general' => 'عام',
        'entertainment' => 'ترفيه',
        'safety' => 'أمان',
        'comfort' => 'راحة',
        'outdoor' => 'خارجي',
    ],
    
    'chalet_features' => [
        'is_active' => [
            'true' => 'نشط',
            'false' => 'غير نشط',
        ],
        'is_featured' => [
            'true' => 'مميز',
            'false' => 'عادي',
        ],
        'is_primary' => [
            'true' => 'رئيسية',
            'false' => 'عادية',
        ],
    ],
    
    'review_statuses' => [
        'approved' => 'موافق عليه',
        'pending' => 'في الانتظار',
        'rejected' => 'مرفوض',
    ],
    
    'ratings' => [
        '1' => 'سيء جداً',
        '2' => 'سيء',
        '3' => 'متوسط',
        '4' => 'جيد',
        '5' => 'ممتاز',
    ],
];

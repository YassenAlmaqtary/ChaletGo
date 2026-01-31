# نظام الصلاحيات - ChaletGo

## نظرة عامة

تم إعداد نظام صلاحيات احترافي يفصل بين ثلاثة أنواع من المستخدمين:
- **Admin (مدير النظام)**: يتحكم في كل شيء من خلال لوحة Filament الإدارية
- **Owner (مالك الشاليه)**: يدير شاليهاته وحجوزاته من خلال لوحة Filament الخاصة به
- **Customer (عميل)**: يستخدم التطبيق المحمول فقط

## البنية الجديدة

### 1. لوحات Filament

#### لوحة الإدارة (Admin Panel)
- **المسار**: `/admin`
- **المستخدمون**: فقط `admin`
- **الصلاحيات**: إدارة كاملة للنظام (مستخدمين، شاليهات، حجوزات، مدفوعات، استردادات)

#### لوحة مالك الشاليه (Owner Panel)
- **المسار**: `/owner`
- **المستخدمون**: فقط `owner`
- **الصلاحيات**: إدارة شاليهاته وحجوزات شاليهاته فقط

### 2. التطبيق المحمول (Flutter)
- **المستخدمون**: فقط `customer`
- **الحماية**: 
  - التحقق من نوع المستخدم عند تسجيل الدخول
  - منع الوصول للـ Admin/Owner مع رسالة توضيحية
  - Middleware في API يمنع الوصول لغير العملاء

### 3. نظام Spatie Permissions

تم إعداد نظام صلاحيات كامل باستخدام Spatie Laravel Permission:

#### الأدوار (Roles)
- `admin` - مدير النظام
- `owner` - مالك شاليه
- `customer` - عميل

#### الصلاحيات (Permissions)
- **Admin**: `view admin panel`, `manage users`, `manage all chalets`, `manage all bookings`, إلخ
- **Owner**: `view owner panel`, `manage own chalets`, `view own bookings`, `update own bookings`, إلخ
- **Customer**: `view own bookings`, `create bookings`, `cancel own bookings`, إلخ

## خطوات الإعداد

### 1. تشغيل Migrations
```bash
php artisan migrate
```

### 2. تشغيل Seeder للأدوار والصلاحيات
```bash
php artisan db:seed --class=RolePermissionSeeder
```

أو لتشغيل كل Seeders:
```bash
php artisan db:seed
```

### 3. التحقق من الإعداد

#### للتحقق من الأدوار:
```bash
php artisan tinker
```
```php
use Spatie\Permission\Models\Role;
Role::all();
```

#### للتحقق من الصلاحيات:
```php
use Spatie\Permission\Models\Permission;
Permission::all();
```

#### لتعيين دور لمستخدم:
```php
use App\Models\User;
$user = User::find(1);
$user->assignRole('admin'); // أو 'owner' أو 'customer'
```

## كيفية الاستخدام

### للمدير (Admin)
1. تسجيل الدخول على `/admin`
2. الوصول الكامل لإدارة النظام

### لمالك الشاليه (Owner)
1. تسجيل الدخول على `/owner`
2. إدارة شاليهاته من قسم "شاليهاتي"
3. متابعة حجوزات شاليهاته من قسم "حجوزاتي"
4. لا يمكنه الوصول لشاليهات أو حجوزات ملاك آخرين

### للعميل (Customer)
1. استخدام التطبيق المحمول فقط
2. لا يمكنه الوصول لأي لوحة Filament
3. إذا حاول تسجيل الدخول في التطبيق كـ Admin/Owner، سيتم منعه مع رسالة توضيحية

## API Routes

### Routes للعملاء فقط (Mobile App)
جميع المسارات التالية محمية بـ `customer.only` middleware:
- `/api/auth/profile`
- `/api/auth/updateProfile`
- `/api/bookings/*`
- `/api/reviews/*`
- `/api/payments/*`

### Routes للمالكين (Owner API)
المسارات التالية محمية بـ `user.type:owner`:
- `/api/owner/bookings`
- `/api/my-chalets`
- `/api/chalets` (POST, PUT, DELETE)
- `/api/chalets/{slug}/images/*`
- `/api/bookings/{id}/status` (PUT)

## الأمان

### Filament Panels
- `User::canAccessPanel()` يتحقق من نوع المستخدم
- Admin فقط يمكنه الوصول لـ `/admin`
- Owner فقط يمكنه الوصول لـ `/owner`
- Customer لا يمكنه الوصول لأي لوحة

### API Protection
- Middleware `EnsureCustomerOnly` يمنع غير العملاء من الوصول لمسارات التطبيق المحمول
- Middleware `CheckUserType` يتحقق من نوع المستخدم للمسارات الخاصة

### Flutter App
- التحقق من `userType` عند تسجيل الدخول
- منع الوصول للـ Admin/Owner مع رسالة توضيحية
- مسح الجلسة تلقائياً للمستخدمين غير المصرح لهم

## ملاحظات مهمة

1. **عند إنشاء مستخدم جديد**: تأكد من تعيين `user_type` الصحيح
2. **عند تغيير نوع المستخدم**: قم بتشغيل Seeder مرة أخرى أو قم بتعيين الدور يدوياً
3. **للاختبار**: استخدم مستخدمين مختلفين لكل نوع للتحقق من الصلاحيات

## استكشاف الأخطاء

### المستخدم لا يستطيع الوصول للوحة
- تحقق من `user_type` في قاعدة البيانات
- تحقق من أن المستخدم لديه الدور الصحيح: `$user->hasRole('admin')`
- تحقق من `canAccessPanel()` في User model

### خطأ 403 في API
- تحقق من نوع المستخدم في الـ Token
- تحقق من أن المسار محمي بالـ Middleware الصحيح
- للعملاء: تأكد من استخدام `customer.only` middleware

### التطبيق المحمول يمنع الوصول
- تحقق من `userType` في UserModel
- تحقق من `canAccessMobileApp` في AuthService
- تأكد من أن المستخدم من نوع `customer`

## التطوير المستقبلي

يمكن إضافة:
- واجهة لإدارة الأدوار والصلاحيات من Filament
- صلاحيات أكثر تفصيلاً (مثل: `view own payments`, `manage own reviews`)
- لوحة منفصلة للعملاء (إذا لزم الأمر)
- نظام إشعارات عند تغيير الصلاحيات






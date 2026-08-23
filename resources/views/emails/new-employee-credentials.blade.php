@component('mail::message')
# مرحباً {{ $user->name }}

تم إنشاء حساب لك بنظام إدارة مطعم الرمال. بيانات الدخول الخاصة بك:

**البريد الإلكتروني:** {{ $user->email }}

**كلمة المرور المؤقتة:** {{ $plainPassword }}

@component('mail::panel')
يرجى تسجيل الدخول وتغيير كلمة المرور فوراً من إعدادات حسابك.
@endcomponent

شكراً لك،<br>
إدارة مطعم الرمال
@endcomponent
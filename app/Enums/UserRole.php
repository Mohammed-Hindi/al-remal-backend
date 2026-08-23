<?php

namespace App\Enums;

enum UserRole: string
{
     case Admin = 'admin';
     case Cashier = 'cashier';
     case Kitchen = 'kitchen';

     public function label(): string
     {
          return match ($this) {
               self::Admin => 'مدير',
               self::Cashier => 'كاشير',
               self::Kitchen => 'مطبخ',
          };
     }
}

<?php

namespace App\Enums;
use App\Enums\UserRole;

enum OrderStatus: string
{
     case Pending = 'pending';
     case Accepted = 'accepted';
     case Preparing = 'preparing';
     case Ready = 'ready';
     case Served = 'served';
     case Paid = 'paid';
     case Cancelled = 'cancelled';

     public function label(): string
     {
          return match ($this) {
               self::Pending => 'قيد الانتظار',
               self::Accepted => 'تم القبول',
               self::Preparing => 'قيد التحضير',
               self::Ready => 'جاهز',
               self::Served => 'تم التقديم',
               self::Paid => 'تم الدفع',
               self::Cancelled => 'ملغي',
          };
     }

     public function canTransitionTo(self $newStatus): bool
     {
          return match ($this) {
               self::Pending => in_array($newStatus, [self::Accepted, self::Cancelled]),
               self::Accepted => in_array($newStatus, [self::Preparing, self::Cancelled]),
               self::Preparing => in_array($newStatus, [self::Ready, self::Cancelled]),
               self::Ready => $newStatus === self::Served,
               self::Served => $newStatus === self::Paid,
               self::Paid, self::Cancelled => false,
          };
     }

     public function allowedRolesForTransitionTo(self $newStatus): array
     {
          return match (true) {
               $newStatus === self::Accepted => [UserRole::Kitchen, UserRole::Admin],
               $newStatus === self::Preparing => [UserRole::Kitchen, UserRole::Admin],
               $newStatus === self::Ready => [UserRole::Kitchen, UserRole::Admin],
               $newStatus === self::Served => [UserRole::Cashier, UserRole::Admin],
               $newStatus === self::Paid => [UserRole::Cashier, UserRole::Admin],
               $newStatus === self::Cancelled => [UserRole::Cashier, UserRole::Admin],
               default => [],
          };
     }
}

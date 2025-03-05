<?php

namespace App\Enums;

enum PermissionsEnum: string
{
    // Admin
    case ApproveVendors = 'ApproveVendors';
    // Vendor
    case SellProducts = 'SellProducts';
    // User
    case BuyProducts = 'BuyProducts';
}
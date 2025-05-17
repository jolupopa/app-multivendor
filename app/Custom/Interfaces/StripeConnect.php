<?php
namespace App\Custom\Interfaces;
use App\Enums\StripeLinkTypeEnum;
use Stripe\Account;
use Stripe\Balance;
use Stripe\Transfer;
interface StripeConnect
{
    public function accounts(); // Debe devolver algo que tenga un método create() y retrieve()
    public function accountLinks(); // Debe devolver algo que tenga un método create()
    public function transfers(); // Debe devolver algo que tenga un método create()
    public function balance(); // Debe devolver algo que tenga un método retrieve()
}
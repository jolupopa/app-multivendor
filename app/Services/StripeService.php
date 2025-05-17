<?php
namespace App\Services;
use App\Custom\Interfaces\StripeConnect;
use Stripe\StripeClient;
class StripeService implements StripeConnect
{
    protected StripeClient $stripeClient;
    public function __construct()
    {
        $this->stripeClient = new StripeClient(config('stripe_connect.stripe.secret'));
    }
    public function accounts()
    {
        return $this->stripeClient->accounts;
    }
    public function accountLinks()
    {
        return $this->stripeClient->accountLinks;
    }
    public function transfers()
    {
        return $this->stripeClient->transfers;
    }
    public function balance()
    {
         return $this->stripeClient->balance;
    }
}
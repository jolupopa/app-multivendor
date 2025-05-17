<?php
namespace App\Custom\Traits;
use App\Services\StripeService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Stripe\Account;
use Stripe\Balance;
use Stripe\Transfer;
trait Payable
{
    protected ?StripeService $stripeService = null;
    public function setStripeService(StripeService $stripeService): void
    {
        $this->stripeService = $stripeService;
    }
    public function createStripeAccount(array $details): self
    {
        $details['type'] = 'express';
        $account = $this->stripeService->accounts()->create($details);
        $this->setStripeAccountId($account->id)->save();
        return $this;
    }
    public function retrieveStripeAccount(): Account
    {
        return $this->stripeService->accounts()->retrieve($this->getStripeAccountId());
    }
    public function getStripeAccountId(): ?string
    {
        return $this->{$this->getStripeAccountIdColumn()};
    }
    public function isStripeAccountActive(): bool
    {
        // Intenta primero la lógica de la API, si falla, usa la columna de la base de datos
        try {
            $account = $this->stripeService->accounts()->retrieve($this->getStripeAccountId());
            return $account->charges_enabled;
        } catch (\Exception $e) {
            return $this->{$this->getStripeAccountStatusColumn()};
        }
    }
    public function getStripeAccountLink(string $type = 'account_onboarding'): string
    {
        $link = $this->stripeService->accountLinks()->create([
            'account' => $this->getStripeAccountId(),
            'refresh_url' => URL::route(Config::get('stripe_connect.routes.account.refresh')),
            'return_url' => URL::route(Config::get('stripe_connect.routes.account.return')),
            'type' => $type,
        ])->url;
        return $link;
    }
    public function transfer(int $amount, string $currency): Transfer
    {
        return $this->stripeService->transfers()->create([
            'amount' => $amount,
            'currency' => $currency,
            'destination' => $this->getStripeAccountId(),
        ]);
    }
    public function getAccountBalance(): Balance
    {
        return $this->stripeService->balance()->retrieve([], [
            'stripe_account' => $this->getStripeAccountId(),
        ]);
    }
    public function setStripeAccountStatus(string $status): self
    {
        $this->{$this->getStripeAccountStatusColumn()} = $status;
        $this->save();
        return $this;
    }
    protected function getStripeAccountIdColumn(): string
    {
        return Config::get('stripe_connect.payable.account_id_column', 'stripe_account_id'); // Default value added
    }
    protected function setStripeAccountId(string $id): self
    {
        $this->{$this->getStripeAccountIdColumn()} = $id;
        return $this;
    }
    protected function getStripeAccountStatusColumn(): string
    {
        return Config::get('stripe_connect.payable.account_status_column', 'stripe_account_active'); // Default value added
    }
}
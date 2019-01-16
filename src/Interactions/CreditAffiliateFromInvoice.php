<?php

namespace KeithBrink\AffiliatesSpark\Interactions;

use KeithBrink\AffiliatesSpark\Models\AffiliateTransaction;
use Laravel\Spark\LocalInvoice;
use KeithBrink\AffiliatesSpark\Models\Affiliate;

class CreditAffiliateFromInvoice
{
    public $invoice;

    public function __construct(LocalInvoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function handle()
    {
        if ($affiliate_id = $this->invoice->user->affiliate_id) {
            $this->addTransaction($affiliate_id);
        }
        if($affiliate_id = $this->invoice->user->team->affiliate_id) {
            $this->addTransaction($affiliate_id);
        }
    }

    public function addTransaction($affiliate_id)
    {
        $affiliate = Affiliate::find($affiliate_id);

        $transaction = new AffiliateTransaction;
        $transaction->affiliate_id = $affiliate_id;
        $transaction->transaction_date = $this->invoice->created_at;
        $transaction->transaction_id = $this->invoice->id;
        $transaction->type = 'referral_payment';
        $transaction->amount = round($affiliate->calculateCommission($this->invoice->total), 1);
        $transaction->save();
    }
}
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\TraitModel;

class ProcessPairing implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    use TraitModel;

    protected $order_id;
    protected $ref_id;
    protected $bv_total;
    protected $bvcv_amount;
    protected $ref1_fee_point_sale;
    protected $ref1_fee_point_upgrade;
    protected $ref2_fee_point_sale;
    protected $ref2_fee_point_upgrade;
    protected $ref1_flush_out;
    protected $ledger_id;
    protected $cba2;
    protected $cbmart;
    protected $points_fee_id;
    protected $points_upg_id;
    protected $ref2_id;
    protected $memo;
    protected $member_get_flush_out;
    protected $package_type;
    protected $ref_fee_lev;
    protected $customer_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order_id, $ref_id, $bv_total, $bvcv_amount, $ref1_fee_point_sale, $ref1_fee_point_upgrade, $ref2_fee_point_sale, $ref2_fee_point_upgrade, $ref1_flush_out, $ledger_id, $cba2, $cbmart, $points_fee_id, $points_upg_id, $ref2_id, $memo, $member_get_flush_out, $package_type, $ref_fee_lev, $customer_id)
    {
        $this->order_id = $order_id;
        $this->ref_id = $ref_id;
        $this->bv_total = $bv_total;
        $this->bvcv_amount = $bvcv_amount;
        $this->ref1_fee_point_sale = $ref1_fee_point_sale;
        $this->ref1_fee_point_upgrade = $ref1_fee_point_upgrade;
        $this->ref2_fee_point_sale = $ref2_fee_point_sale;
        $this->ref2_fee_point_upgrade = $ref2_fee_point_upgrade;
        $this->ref1_flush_out = $ref1_flush_out;
        $this->ledger_id = $ledger_id;
        $this->cba2 = $cba2;
        $this->cbmart = $cbmart;
        $this->points_fee_id = $points_fee_id;
        $this->points_upg_id = $points_upg_id;
        $this->ref2_id = $ref2_id;
        $this->memo = $memo;
        $this->member_get_flush_out = $member_get_flush_out;
        $this->package_type = $package_type;
        $this->ref_fee_lev = $ref_fee_lev;
        $this->customer_id = $customer_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        return $this->fee_pairing($this->order_id, $this->ref_id, $this->bv_total, $this->bvcv_amount, $this->ref1_fee_point_sale, $this->ref1_fee_point_upgrade, $this->ref2_fee_point_sale, $this->ref2_fee_point_upgrade, $this->ref1_flush_out, $this->ledger_id, $this->cba2, $this->cbmart, $this->points_fee_id, $this->points_upg_id, $this->ref2_id, $this->memo, $this->member_get_flush_out, $this->package_type, $this->ref_fee_lev, $this->customer_id);
    }
}

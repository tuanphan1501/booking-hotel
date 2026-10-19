<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Payment transaction of a booking (`payments`).
 *
 * @property int $id
 * @property string $payment_code
 * @property int $booking_id
 * @property string $method VNPAY, MOMO, CARD, CASH or BANK_TRANSFER.
 * @property int $amount
 * @property string $status PENDING, SUCCESS or FAILED.
 * @property Carbon|null $paid_at
 *
 * @author PH.Tuan
 * @created 2026-10-19
 */
class Payment extends Model
{
    /** Methods offered in the app (MB-08). */
    public const APP_METHODS = ['VNPAY', 'MOMO', 'CARD'];

    /** Methods recorded at the front desk (AD-04). */
    public const COUNTER_METHODS = ['CASH', 'CARD', 'BANK_TRANSFER'];

    /**
     * Attributes that cannot be mass assigned.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    /**
     * Get the attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['amount' => 'integer', 'paid_at' => 'datetime', 'raw_response' => 'array'];
    }
}

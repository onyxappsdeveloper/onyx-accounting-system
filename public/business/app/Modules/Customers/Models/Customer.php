<?php
/**
 * Customers Module - Model
 */

namespace App\Modules\Customers\Models;

use App\Core\Model;

class Customer extends Model
{
    protected $table = 'customers';
    protected $fillable = [
        'customer_code',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'tin_number',
        'customer_group',
        'credit_limit',
        'is_active'
    ];

    /**
     * Get customer invoices
     */
    public function invoices()
    {
        $db = $this->connection;
        return $db->fetchAll(
            "SELECT * FROM invoices WHERE customer_id = ? ORDER BY invoice_date DESC",
            [$this->attributes['id']]
        );
    }

    /**
     * Get customer balance
     */
    public function balance()
    {
        $db = $this->connection;
        $result = $db->fetch(
            "SELECT COALESCE(SUM(total_amount - paid_amount), 0) as balance FROM invoices WHERE customer_id = ?",
            [$this->attributes['id']]
        );
        return $result['balance'] ?? 0;
    }
}

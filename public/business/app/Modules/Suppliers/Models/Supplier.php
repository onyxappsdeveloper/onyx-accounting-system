<?php
/**
 * Suppliers Module - Model
 */

namespace App\Modules\Suppliers\Models;

use App\Core\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';
    protected $fillable = [
        'supplier_code',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'tin_number',
        'payment_terms',
        'is_active'
    ];

    /**
     * Get supplier bills
     */
    public function bills()
    {
        $db = $this->connection;
        return $db->fetchAll(
            "SELECT * FROM supplier_bills WHERE supplier_id = ? ORDER BY bill_date DESC",
            [$this->attributes['id']]
        );
    }
}

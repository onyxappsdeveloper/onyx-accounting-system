<?php
/**
 * Products Module - Model
 */

namespace App\Modules\Products\Models;

use App\Core\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'description',
        'category_id',
        'income_category_id',
        'expense_category_id',
        'supplier_id',
        'cost_price',
        'selling_price',
        'tax_rate',
        'min_stock',
        'max_stock',
        'current_stock',
        'unit_of_measure',
        'is_active'
    ];

    /**
     * Calculate profit margin
     */
    public function profitMargin()
    {
        if ($this->attributes['cost_price'] > 0) {
            return (($this->attributes['selling_price'] - $this->attributes['cost_price']) / $this->attributes['cost_price']) * 100;
        }
        return 0;
    }

    /**
     * Check if low stock
     */
    public function isLowStock()
    {
        return $this->attributes['current_stock'] <= $this->attributes['min_stock'];
    }
}

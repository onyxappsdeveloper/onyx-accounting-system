<?php
/**
 * ONYX Accounting System - Base Model Class
 * All models inherit from this class
 */

namespace App\Core;

class Model
{
    protected $table;
    protected $connection;
    protected $attributes = [];
    protected $original = [];
    protected $guarded = ['tenant_id', 'created_at', 'updated_at'];
    protected $fillable = [];
    protected static $cached = [];

    /**
     * Constructor
     */
    public function __construct($attributes = [])
    {
        $this->connection = Database::getInstance();
        $this->fill($attributes);
    }

    /**
     * Fill model with attributes
     */
    public function fill($attributes)
    {
        foreach ($attributes as $key => $value) {
            if (empty($this->fillable) || in_array($key, $this->fillable)) {
                if (!in_array($key, $this->guarded)) {
                    $this->attributes[$key] = $value;
                }
            }
        }
        return $this;
    }

    /**
     * Get attribute
     */
    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Set attribute
     */
    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Check if attribute exists
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Convert to array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * Find by ID
     */
    public static function find($id)
    {
        $model = new static();
        $result = $model->connection->fetch(
            "SELECT * FROM {$model->table} WHERE id = ? AND tenant_id = ?",
            [$id, Tenant::getInstance()->id()]
        );
        
        if ($result) {
            return $model->fill($result);
        }
        return null;
    }

    /**
     * Get all records
     */
    public static function all()
    {
        $model = new static();
        $results = $model->connection->fetchAll(
            "SELECT * FROM {$model->table} WHERE tenant_id = ?",
            [Tenant::getInstance()->id()]
        );
        
        $instances = [];
        foreach ($results as $result) {
            $instances[] = (new static())->fill($result);
        }
        
        return $instances;
    }

    /**
     * Save model
     */
    public function save()
    {
        if (isset($this->attributes['id'])) {
            return $this->update();
        } else {
            return $this->create();
        }
    }

    /**
     * Create new record
     */
    private function create()
    {
        $this->attributes['tenant_id'] = Tenant::getInstance()->id();
        $this->attributes['created_at'] = date('Y-m-d H:i:s');
        $this->attributes['updated_at'] = date('Y-m-d H:i:s');

        $id = $this->connection->insert($this->table, $this->attributes);
        $this->attributes['id'] = $id;
        
        return $id;
    }

    /**
     * Update existing record
     */
    private function update()
    {
        $this->attributes['updated_at'] = date('Y-m-d H:i:s');
        
        $id = $this->attributes['id'];
        unset($this->attributes['id']);
        unset($this->attributes['tenant_id']);
        
        $this->connection->update($this->table, $this->attributes, [
            'id' => $id,
            'tenant_id' => Tenant::getInstance()->id()
        ]);
        
        $this->attributes['id'] = $id;
        
        return true;
    }

    /**
     * Delete record
     */
    public function delete()
    {
        if (!isset($this->attributes['id'])) {
            return false;
        }
        
        return $this->connection->delete($this->table, [
            'id' => $this->attributes['id'],
            'tenant_id' => Tenant::getInstance()->id()
        ]);
    }

    /**
     * Query builder
     */
    public static function query()
    {
        $model = new static();
        return new QueryBuilder($model);
    }
}

<?php
/**
 * ONYX Accounting System - Validator Class
 * Handles form validation
 */

namespace App\Core;

class Validator
{
    private $data = [];
    private $rules = [];
    private $errors = [];

    /**
     * Constructor
     */
    public function __construct($data = [], $rules = [])
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    /**
     * Static validation method
     */
    public static function validate($data, $rules)
    {
        $validator = new self($data, $rules);
        return $validator->validate();
    }

    /**
     * Validate data against rules
     */
    public function validate()
    {
        foreach ($this->rules as $field => $rule) {
            $this->validateField($field, $rule);
        }

        return empty($this->errors);
    }

    /**
     * Validate single field
     */
    private function validateField($field, $rule)
    {
        $rules = is_string($rule) ? explode('|', $rule) : $rule;
        $value = $this->data[$field] ?? null;

        foreach ($rules as $r) {
            $this->validateRule($field, $r, $value);
        }
    }

    /**
     * Validate single rule
     */
    private function validateRule($field, $rule, $value)
    {
        $ruleName = $rule;
        $params = [];

        if (strpos($rule, ':') !== false) {
            list($ruleName, $paramsStr) = explode(':', $rule, 2);
            $params = explode(',', $paramsStr);
        }

        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    $this->addError($field, "$field is required");
                }
                break;

            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "$field must be a valid email");
                }
                break;

            case 'min':
                if (strlen($value) < $params[0]) {
                    $this->addError($field, "$field must be at least {$params[0]} characters");
                }
                break;

            case 'max':
                if (strlen($value) > $params[0]) {
                    $this->addError($field, "$field must not exceed {$params[0]} characters");
                }
                break;

            case 'numeric':
                if (!is_numeric($value)) {
                    $this->addError($field, "$field must be numeric");
                }
                break;

            case 'unique':
                // TODO: Implement unique validation
                break;
        }
    }

    /**
     * Add error
     */
    private function addError($field, $message)
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Get all errors
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Get field errors
     */
    public function getErrors($field = null)
    {
        if ($field === null) {
            return $this->errors;
        }
        return $this->errors[$field] ?? [];
    }
}

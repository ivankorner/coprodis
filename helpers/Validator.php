<?php

namespace App\Helpers;

class Validator
{
    private array $errors = [];
    private array $data;
    private array $rules;
    private array $messages;

    private array $customMessages = [
        'required' => 'El campo :field es obligatorio.',
        'email' => 'El campo :field debe ser un correo electrónico válido.',
        'min' => 'El campo :field debe tener al menos :param caracteres.',
        'max' => 'El campo :field no debe exceder :param caracteres.',
        'numeric' => 'El campo :field debe ser un número.',
        'integer' => 'El campo :field debe ser un número entero.',
        'alpha' => 'El campo :field solo debe contener letras.',
        'alpha_space' => 'El campo :field solo debe contener letras y espacios.',
        'alpha_num' => 'El campo :field solo debe contener letras y números.',
        'phone' => 'El campo :field debe ser un número telefónico válido.',
        'dni' => 'El campo :field debe ser un DNI válido (7 u 8 dígitos).',
        'in' => 'El campo :field debe ser uno de: :param.',
        'confirmed' => 'La confirmación de :field no coincide.',
        'unique' => 'El valor del campo :field ya está registrado.',
        'exists' => 'El valor del campo :field no existe.',
        'date' => 'El campo :field debe ser una fecha válida.',
        'url' => 'El campo :field debe ser una URL válida.',
        'file' => 'El campo :field debe ser un archivo.',
        'image' => 'El campo :field debe ser una imagen.',
        'size' => 'El campo :field no debe exceder :param KB.',
        'array' => 'El campo :field debe ser un arreglo.',
        'boolean' => 'El campo :field debe ser verdadero o falso.',
        'after' => 'El campo :field debe ser una fecha posterior a :param.',
        'before' => 'El campo :field debe ser una fecha anterior a :param.',
    ];

    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = array_merge($this->customMessages, $messages);
    }

    public static function validate(array $data, array $rules, array $messages = []): self
    {
        $instance = new self($data, $rules, $messages);
        $instance->run();
        return $instance;
    }

    public function run(): void
    {
        foreach ($this->rules as $field => $rulesStr) {
            $rulesList = is_array($rulesStr) ? $rulesStr : explode('|', $rulesStr);
            $value = $this->data[$field] ?? null;

            foreach ($rulesList as $rule) {
                $params = [];

                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $methodName = "rule{$rule}";
                if (method_exists($this, $methodName)) {
                    $this->$methodName($field, $value, $params);
                }
            }
        }
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        $first = reset($this->errors);
        return $first[0] ?? null;
    }

    private function addError(string $field, string $rule, array $params = []): void
    {
        $message = $this->messages[$rule] ?? "El campo {$field} no es válido.";
        $message = str_replace(':field', $field, $message);
        if (!empty($params)) {
            $message = str_replace(':param', implode(', ', $params), $message);
        }
        $this->errors[$field][] = $message;
    }

    private function ruleRequired(string $field, $value, array $params): void
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, 'required');
        }
    }

    private function ruleEmail(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'email');
        }
    }

    private function ruleMin(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && mb_strlen((string)$value) < (int)$params[0]) {
            $this->addError($field, 'min', $params);
        }
    }

    private function ruleMax(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && mb_strlen((string)$value) > (int)$params[0]) {
            $this->addError($field, 'max', $params);
        }
    }

    private function ruleNumeric(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->addError($field, 'numeric');
        }
    }

    private function ruleInteger(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, 'integer');
        }
    }

    private function ruleAlpha(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !ctype_alpha(str_replace(' ', '', $value))) {
            $this->addError($field, 'alpha');
        }
    }

    private function ruleAlphaSpace(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !preg_match('/^[a-zA-Z\s]+$/', $value)) {
            $this->addError($field, 'alpha_space');
        }
    }

    private function ruleAlphaNum(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !ctype_alnum(str_replace(' ', '', $value))) {
            $this->addError($field, 'alpha_num');
        }
    }

    private function rulePhone(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !preg_match('/^[\d\s\-\+\(\)]{7,20}$/', $value)) {
            $this->addError($field, 'phone');
        }
    }

    private function ruleDni(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !preg_match('/^\d{7,8}$/', $value)) {
            $this->addError($field, 'dni');
        }
    }

    private function ruleIn(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !in_array($value, $params)) {
            $this->addError($field, 'in', $params);
        }
    }

    private function ruleConfirmed(string $field, $value, array $params): void
    {
        $confirmationField = $field . '_confirmation';
        $confirmValue = $this->data[$confirmationField] ?? null;
        if ($value !== null && $value !== '' && $value !== $confirmValue) {
            $this->addError($field, 'confirmed');
        }
    }

    private function ruleUnique(string $field, $value, array $params): void
    {
        if ($value === null || $value === '') return;

        $table = $params[0];
        $column = $params[1] ?? $field;
        $excludeId = $params[2] ?? null;
        $excludeColumn = $params[3] ?? 'id';

        $db = \App\Core\Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = :value";
        $bindings = ['value' => $value];

        if ($excludeId) {
            $sql .= " AND {$excludeColumn} != :exclude_id";
            $bindings['exclude_id'] = $excludeId;
        }

        $result = $db->fetch($sql, $bindings);
        if ($result && $result->count > 0) {
            $this->addError($field, 'unique');
        }
    }

    private function ruleExists(string $field, $value, array $params): void
    {
        if ($value === null || $value === '') return;

        $table = $params[0];
        $column = $params[1] ?? $field;

        $db = \App\Core\Database::getInstance();
        $result = $db->fetch(
            "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = :value",
            ['value' => $value]
        );
        if ($result && $result->count === 0) {
            $this->addError($field, 'exists');
        }
    }

    private function ruleDate(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !strtotime($value)) {
            $this->addError($field, 'date');
        }
    }

    private function ruleUrl(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, 'url');
        }
    }

    private function ruleBoolean(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false'], true)) {
            $this->addError($field, 'boolean');
        }
    }

    private function ruleBefore(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '') {
            $date = strtotime($value);
            $compare = strtotime($params[0]);
            if ($date && $compare && $date >= $compare) {
                $this->addError($field, 'before', $params);
            }
        }
    }

    private function ruleAfter(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '') {
            $date = strtotime($value);
            $compare = strtotime($params[0]);
            if ($date && $compare && $date <= $compare) {
                $this->addError($field, 'after', $params);
            }
        }
    }
}

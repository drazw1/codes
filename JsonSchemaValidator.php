<?php
// ============================================================
//  JsonSchemaValidator.php
//  Lightweight JSON Schema (draft-07 subset) validator.
//  Covers: type, required, properties, enum, minimum, maximum,
//          minLength, maxLength, pattern, additionalProperties,
//          multipleOf, items (array validation).
//  No Composer / external library required.
// ============================================================

class JsonSchemaValidator
{
    /** @var array  Collected validation errors */
    private array $errors = [];

    /** @var string  Base directory for $ref resolution */
    private string $schemaDir;

    public function __construct(string $schemaDir = '')
    {
        $this->schemaDir = $schemaDir ?: __DIR__;
    }

    // ----------------------------------------------------------
    //  Public API
    // ----------------------------------------------------------

    /**
     * Load a schema file and validate $data against it.
     *
     * @param  mixed  $data    Decoded PHP value (array/scalar)
     * @param  string $schemaFile  Path to the .json schema file
     * @return bool
     */
    public function validateFile(mixed $data, string $schemaFile): bool
    {
        $this->errors = [];

        if (!file_exists($schemaFile)) {
            $this->errors[] = "Schema file not found: $schemaFile";
            return false;
        }

        $schema = json_decode(file_get_contents($schemaFile), true);
        if ($schema === null) {
            $this->errors[] = "Schema file is not valid JSON.";
            return false;
        }

        $this->validate($data, $schema, '$');
        return empty($this->errors);
    }

    /**
     * Validate $data against an already-decoded $schema array.
     *
     * @param  mixed  $data
     * @param  array  $schema
     * @return bool
     */
    public function validateSchema(mixed $data, array $schema): bool
    {
        $this->errors = [];
        $this->validate($data, $schema, '$');
        return empty($this->errors);
    }

    /** Return all collected error messages. */
    public function getErrors(): array
    {
        return $this->errors;
    }

    // ----------------------------------------------------------
    //  Internal recursive validation
    // ----------------------------------------------------------

    private function validate(mixed $data, array $schema, string $path): void
    {
        // ── $ref resolution ──────────────────────────────────
        if (isset($schema['$ref'])) {
            $refFile = $this->schemaDir . DIRECTORY_SEPARATOR . basename($schema['$ref']);
            if (file_exists($refFile)) {
                $refSchema = json_decode(file_get_contents($refFile), true);
                if ($refSchema) {
                    $this->validate($data, $refSchema, $path);
                }
            }
            return;
        }

        // ── type ─────────────────────────────────────────────
        if (isset($schema['type'])) {
            $this->checkType($data, $schema['type'], $path);
        }

        // ── enum ─────────────────────────────────────────────
        if (isset($schema['enum'])) {
            if (!in_array($data, $schema['enum'], true)) {
                $allowed = implode(', ', array_map(fn($v) => json_encode($v), $schema['enum']));
                $this->errors[] = "$path: value " . json_encode($data) . " must be one of [$allowed]";
            }
        }

        // ── String keywords ───────────────────────────────────
        if (is_string($data)) {
            if (isset($schema['minLength']) && mb_strlen($data) < $schema['minLength']) {
                $this->errors[] = "$path: string length " . mb_strlen($data)
                    . " is less than minLength {$schema['minLength']}";
            }
            if (isset($schema['maxLength']) && mb_strlen($data) > $schema['maxLength']) {
                $this->errors[] = "$path: string length " . mb_strlen($data)
                    . " exceeds maxLength {$schema['maxLength']}";
            }
            if (isset($schema['pattern'])) {
                if (!preg_match('/' . $schema['pattern'] . '/', $data)) {
                    $this->errors[] = "$path: value does not match pattern /{$schema['pattern']}/";
                }
            }
        }

        // ── Numeric keywords ──────────────────────────────────
        if (is_numeric($data) && !is_string($data)) {
            if (isset($schema['minimum']) && $data < $schema['minimum']) {
                $this->errors[] = "$path: $data is less than minimum {$schema['minimum']}";
            }
            if (isset($schema['maximum']) && $data > $schema['maximum']) {
                $this->errors[] = "$path: $data exceeds maximum {$schema['maximum']}";
            }
            if (isset($schema['multipleOf']) && fmod((float)$data, (float)$schema['multipleOf']) !== 0.0) {
                // Use tolerance for floats
                $remainder = fmod(abs((float)$data), (float)$schema['multipleOf']);
                if ($remainder > 1e-10 && ($schema['multipleOf'] - $remainder) > 1e-10) {
                    $this->errors[] = "$path: $data is not a multiple of {$schema['multipleOf']}";
                }
            }
        }

        // ── Object keywords ───────────────────────────────────
        if (is_array($data) && !$this->isSequentialArray($data)) {
            // required
            if (isset($schema['required'])) {
                foreach ($schema['required'] as $field) {
                    if (!array_key_exists($field, $data)) {
                        $this->errors[] = "$path: required field '$field' is missing";
                    }
                }
            }
            // properties
            if (isset($schema['properties'])) {
                foreach ($schema['properties'] as $key => $propSchema) {
                    if (array_key_exists($key, $data)) {
                        $this->validate($data[$key], $propSchema, "$path.$key");
                    }
                }
            }
            // additionalProperties = false
            if (isset($schema['additionalProperties']) && $schema['additionalProperties'] === false) {
                $allowed = array_keys($schema['properties'] ?? []);
                foreach (array_keys($data) as $key) {
                    if (!in_array($key, $allowed, true)) {
                        $this->errors[] = "$path: additional property '$key' is not allowed";
                    }
                }
            }
        }

        // ── Array keywords ────────────────────────────────────
        if (is_array($data) && $this->isSequentialArray($data)) {
            if (isset($schema['items'])) {
                foreach ($data as $i => $item) {
                    $this->validate($item, $schema['items'], "{$path}[$i]");
                }
            }
        }
    }

    // ----------------------------------------------------------
    //  Type checker (supports union types as array)
    // ----------------------------------------------------------

    private function checkType(mixed $data, array|string $types, string $path): void
    {
        $types = (array)$types;
        foreach ($types as $type) {
            if ($this->matchesType($data, $type)) {
                return; // passes
            }
        }
        $typeStr = implode('|', $types);
        $actual  = $this->getType($data);
        $this->errors[] = "$path: expected type '$typeStr', got '$actual'";
    }

    private function matchesType(mixed $data, string $type): bool
    {
        return match ($type) {
            'null'    => $data === null,
            'boolean' => is_bool($data),
            'integer' => is_int($data),
            'number'  => is_int($data) || is_float($data),
            'string'  => is_string($data),
            'array'   => is_array($data) && $this->isSequentialArray($data),
            'object'  => is_array($data) && !$this->isSequentialArray($data),
            default   => false,
        };
    }

    private function getType(mixed $data): string
    {
        if ($data === null)  return 'null';
        if (is_bool($data))  return 'boolean';
        if (is_int($data))   return 'integer';
        if (is_float($data)) return 'number';
        if (is_string($data)) return 'string';
        if (is_array($data)) return $this->isSequentialArray($data) ? 'array' : 'object';
        return gettype($data);
    }

    private function isSequentialArray(mixed $val): bool
    {
        if (!is_array($val) || empty($val)) return is_array($val);
        return array_keys($val) === range(0, count($val) - 1);
    }
}

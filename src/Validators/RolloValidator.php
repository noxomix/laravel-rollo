<?php

namespace Noxomix\LaravelRollo\Validators;

use Noxomix\LaravelRollo\Exceptions\RolloValidationException;

class RolloValidator
{
    /**
     * Pattern for valid permission/role names.
     * Allows: lowercase letters, numbers, dots, dashes, underscores
     * Examples: view-posts, posts.create, manage_users, admin.users.delete
     */
    const NAME_PATTERN = '/^[a-z0-9\.\-_]+$/';
    
    /**
     * Maximum length for names
     */
    const MAX_NAME_LENGTH = 255;
    
    /**
     * Reserved names that cannot be used
     */
    const RESERVED_NAMES = [
        'null', 'undefined', 'true', 'false', 'yes', 'no', 'all', 'none', 'any'
    ];

    /**
     * Validate a permission name.
     *
     * @param string $name
     * @throws RolloValidationException
     * @return void
     */
    public static function validatePermissionName(string $name): void
    {
        self::validateName($name, 'Permission');
    }

    /**
     * Validate a role name.
     *
     * @param string $name
     * @throws RolloValidationException
     * @return void
     */
    public static function validateRoleName(string $name): void
    {
        self::validateName($name, 'Role');
    }

    /**
     * Validate a name (permission or role).
     *
     * @param string $name
     * @param string $type
     * @throws RolloValidationException
     * @return void
     */
    protected static function validateName(string $name, string $type): void
    {
        // Check if empty
        if (empty($name)) {
            throw new RolloValidationException("{$type} name cannot be empty.");
        }

        // Check length
        if (strlen($name) > self::MAX_NAME_LENGTH) {
            throw new RolloValidationException(
                "{$type} name cannot exceed " . self::MAX_NAME_LENGTH . " characters."
            );
        }

        // Check pattern
        if (!preg_match(self::NAME_PATTERN, $name)) {
            throw new RolloValidationException(
                "{$type} name can only contain lowercase letters, numbers, dots, dashes, and underscores. Given: '{$name}'"
            );
        }

        // Check reserved names
        if (in_array(strtolower($name), self::RESERVED_NAMES)) {
            throw new RolloValidationException(
                "{$type} name '{$name}' is reserved and cannot be used."
            );
        }

        // Check for consecutive special characters
        if (preg_match('/[\.\-_]{2,}/', $name)) {
            throw new RolloValidationException(
                "{$type} name cannot contain consecutive special characters."
            );
        }

        // Check start/end with special characters
        if (preg_match('/^[\.\-_]|[\.\-_]$/', $name)) {
            throw new RolloValidationException(
                "{$type} name cannot start or end with special characters."
            );
        }
    }

    /**
     * Validate JSON configuration.
     *
     * @param mixed $config
     * @param array|null $schema Optional schema to validate against
     * @throws RolloValidationException
     * @return void
     */
    public static function validateConfig($config, ?array $schema = null): void
    {
        // Ensure it's an array or null
        if ($config !== null && !is_array($config)) {
            throw new RolloValidationException(
                "Configuration must be an array or null."
            );
        }

        // If null or empty, it's valid
        if (empty($config)) {
            return;
        }

        // Check for dangerous keys
        $dangerousKeys = ['__proto__', 'constructor', 'prototype'];
        foreach ($config as $key => $value) {
            if (in_array($key, $dangerousKeys)) {
                throw new RolloValidationException(
                    "Configuration contains dangerous key: '{$key}'"
                );
            }
        }

        // Validate against schema if provided
        if ($schema !== null) {
            self::validateAgainstSchema($config, $schema);
        }

        // Check maximum depth (prevent deeply nested objects)
        if (self::getArrayDepth($config) > 5) {
            throw new RolloValidationException(
                "Configuration depth cannot exceed 5 levels."
            );
        }

        // Check size (prevent huge configs)
        $jsonSize = strlen(json_encode($config));
        if ($jsonSize > 65535) { // 64KB limit
            throw new RolloValidationException(
                "Configuration size cannot exceed 64KB."
            );
        }
    }

    /**
     * Get the depth of an array.
     *
     * @param array $array
     * @return int
     */
    protected static function getArrayDepth(array $array): int
    {
        $maxDepth = 1;
        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = self::getArrayDepth($value) + 1;
                if ($depth > $maxDepth) {
                    $maxDepth = $depth;
                }
            }
        }
        return $maxDepth;
    }

    /**
     * Validate config against a schema.
     *
     * @param array $config
     * @param array $schema
     * @throws RolloValidationException
     * @return void
     */
    protected static function validateAgainstSchema(array $config, array $schema): void
    {
        // This is a simple schema validation
        // In production, consider using a proper JSON Schema validator
        
        // Check required fields
        if (isset($schema['required'])) {
            foreach ($schema['required'] as $field) {
                if (!isset($config[$field])) {
                    throw new RolloValidationException(
                        "Configuration missing required field: '{$field}'"
                    );
                }
            }
        }

        // Check field types
        if (isset($schema['properties'])) {
            foreach ($config as $key => $value) {
                if (isset($schema['properties'][$key]['type'])) {
                    $expectedType = $schema['properties'][$key]['type'];
                    $actualType = gettype($value);
                    
                    // Map PHP types to JSON Schema types
                    $typeMap = [
                        'integer' => ['integer', 'double'],
                        'number' => ['integer', 'double'],
                        'string' => ['string'],
                        'boolean' => ['boolean'],
                        'array' => ['array'],
                        'object' => ['array']
                    ];

                    if (isset($typeMap[$expectedType]) && !in_array($actualType, $typeMap[$expectedType])) {
                        throw new RolloValidationException(
                            "Configuration field '{$key}' must be of type '{$expectedType}', got '{$actualType}'"
                        );
                    }
                }
            }
        }
    }

    /**
     * Sanitize a name for safe use.
     *
     * @param string $name
     * @return string
     */
    public static function sanitizeName(string $name): string
    {
        // Convert to lowercase
        $name = strtolower($name);
        
        // Replace spaces with dashes
        $name = str_replace(' ', '-', $name);
        
        // Remove any character that's not allowed
        $name = preg_replace('/[^a-z0-9\.\-_]/', '', $name);
        
        // Remove consecutive special characters
        $name = preg_replace('/[\.\-_]{2,}/', '-', $name);
        
        // Trim special characters from start/end
        $name = trim($name, '.-_');
        
        return $name;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Document_Map;
use App\Models\Document_Permission_Map;
use Illuminate\Support\Facades\Schema;

class AuthPermission extends Model
{
    // Static properties to hold permission IDs
    private static int $USER_ACCOUNTS;
    private static int $LIBRARIES;
    private static int $CUSTOMERS;
    private static int $CUSTOMER_GROUPS;
    private static int $EMPLOYEES;
    private static int $FACTOR_RATES;
    private static int $PAYMENT_DURATIONS;
    private static int $PAYMENT_FREQUENCIES;
    private static int $PERSONALITIES;
    private static int $DOCUMENT_PERMISSIONS;
    private static int $DOCUMENT_MAPS;
    private static int $DOCUMENT_MAP_PERMISSIONS;
    private static int $LOAN_COUNTS;
    private static int $FEES;

    private static int $LOAN_APPLICATIONS;

    //unique
    private static int $BUTTON_AUTHORIZATION;

    private static int $CREATE;
    private static int $DELETE;
    private static int $UPDATE;
    private static int $VIEW;

    public static function initialize(): bool
    {
        // Check if the 'document_map' table exists
        if (!Schema::hasTable('document_map') || !Schema::hasTable('document_permission_map')) {
            // The table does not exist, so return or skip initialization
            return false;
        }

        self::$USER_ACCOUNTS = self::getPermissionId('USER_ACCOUNTS', Document_Map::class);
        self::$LIBRARIES = self::getPermissionId('LIBRARIES', Document_Map::class);
        self::$CUSTOMERS = self::getPermissionId('CUSTOMERS', Document_Map::class);
        self::$CUSTOMER_GROUPS = self::getPermissionId('CUSTOMER_GROUPS', Document_Map::class);
        self::$EMPLOYEES = self::getPermissionId('EMPLOYEES', Document_Map::class);
        self::$FACTOR_RATES = self::getPermissionId('FACTOR_RATES', Document_Map::class);
        self::$PAYMENT_DURATIONS = self::getPermissionId('PAYMENT_DURATIONS', Document_Map::class);
        self::$PAYMENT_FREQUENCIES = self::getPermissionId('PAYMENT_FREQUENCIES', Document_Map::class);
        self::$PERSONALITIES = self::getPermissionId('PERSONALITIES', Document_Map::class);
        self::$DOCUMENT_PERMISSIONS = self::getPermissionId('DOCUMENT_PERMISSIONS', Document_Map::class); // Corrected
        self::$DOCUMENT_MAPS = self::getPermissionId('DOCUMENT_MAPS', Document_Map::class); // Corrected
        self::$DOCUMENT_MAP_PERMISSIONS = self::getPermissionId('DOCUMENT_MAP_PERMISSIONS', Document_Map::class); // Corrected
        self::$LOAN_COUNTS = self::getPermissionId('LOAN_COUNTS', Document_Map::class); // Corrected
        self::$FEES = self::getPermissionId('FEES', Document_Map::class); // Corrected
        self::$BUTTON_AUTHORIZATION = self::getPermissionId('BUTTON_AUTHORIZATIONS', DOCUMENT_MAP::class);
        self::$LOAN_APPLICATIONS = self::getPermissionId('LOAN_APPLICATIONS', Document_Map::class);

        self::$CREATE = self::getPermissionId('CREATE', Document_Permission_Map::class);
        self::$DELETE = self::getPermissionId('DELETE', Document_Permission_Map::class);
        self::$UPDATE = self::getPermissionId('UPDATE', Document_Permission_Map::class);
        self::$VIEW = self::getPermissionId('VIEW', Document_Permission_Map::class);

        //all is true
        return true;
    }

    /**
     * Retrieve the ID for a given permission description.
     *
     * @param string $description
     * @param string|null $modelClass
     * @return int
     */

    private static function getPermissionId(string $description, string $model = null): int
    {
        return $model::where('description', $description)->first()?->id ?? 0; // Return 0 if not found
    }

    // Getters for the permission IDs
    public static function USER_ACCOUNTS(): int
    {
        return self::$USER_ACCOUNTS;
    }

    public static function BUTTON_AUTHORIZATIONS(): int
    {
        return self::$BUTTON_AUTHORIZATION;
    }

    public static function LIBRARIES(): int
    {
        return self::$LIBRARIES;
    }

    public static function CUSTOMERS(): int
    {
        return self::$CUSTOMERS;
    }

    public static function CUSTOMER_GROUPS(): int
    {
        return self::$CUSTOMER_GROUPS;
    }

    public static function EMPLOYEES(): int
    {
        return self::$EMPLOYEES;
    }

    public static function FACTOR_RATES(): int
    {
        return self::$FACTOR_RATES;
    }

    public static function PAYMENT_DURATIONS(): int
    {
        return self::$PAYMENT_DURATIONS;
    }

    public static function PAYMENT_FREQUENCIES(): int
    {
        return self::$PAYMENT_FREQUENCIES;
    }

    public static function PERSONALITIES(): int
    {
        return self::$PERSONALITIES;
    }

    public static function DOCUMENT_PERMISSIONS(): int
    {
        return self::$DOCUMENT_PERMISSIONS;
    }

    public static function DOCUMENT_MAPS(): int
    {
        return self::$DOCUMENT_MAPS;
    }

    public static function DOCUMENT_MAP_PERMISSIONS(): int
    {
        return self::$DOCUMENT_MAP_PERMISSIONS;
    }

    public static function LOAN_COUNTS(): int
    {
        return self::$LOAN_COUNTS;
    }

    public static function FEES(): int
    {
        return self::$FEES;
    }

    public static function LOAN_APPLICATIONS(): int
    {
        return self::$LOAN_APPLICATIONS;
    }

    public static function CREATE_PERM(): int
    {
        return self::$CREATE;
    }

    public static function DELETE_PERM(): int
    {
        return self::$DELETE;
    }

    public static function UPDATE_PERM(): int
    {
        return self::$UPDATE;
    }

    public static function VIEW_PERM(): int
    {
        return self::$VIEW;
    }
}

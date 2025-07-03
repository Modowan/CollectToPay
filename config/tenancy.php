<?php

use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Database\Models\Tenant;

return [
    'tenant_model' => \App\Models\Tenant::class,
    'domain_model' => Domain::class,

    /**
     * Les événements qui doivent être diffusés.
     * 
     * @var array
     */
    'events' => [
        'tenancy.tenant.created' => [
            \Stancl\Tenancy\Events\Handlers\CreateDatabase::class,
            \Stancl\Tenancy\Events\Handlers\MigrateDatabase::class,
        ],
        'tenancy.tenant.updated' => [],
        'tenancy.tenant.deleted' => [
            \Stancl\Tenancy\Events\Handlers\DeleteDatabase::class,
        ],
    ],

    /**
     * Paramètres liés à la base de données.
     */
    'database' => [
        'central_connection' => env('DB_CONNECTION', 'mysql'),

        /**
         * Connexion utilisée par les tenants.
         */
        'tenant_connection' => 'tenant',
        
        /**
         * Préfixe pour les noms de base de données des tenants.
         */
        'prefix' => env('TENANCY_DATABASE_PREFIX', 'tenant_'),
        
        /**
         * Suffixe pour les noms de base de données des tenants.
         */
        'suffix' => '',

        /**
         * Définit si les migrations doivent être exécutées par défaut pour les nouveaux tenants.
         */
        'auto_migrate' => true,

        /**
         * Définit si les seeders doivent être exécutés par défaut pour les nouveaux tenants.
         */
        'auto_seed' => false,

        /**
         * Classe de seeders à exécuter lors de la création d'un tenant.
         */
        'tenancy_seeder_class' => null,
    ],

    /**
     * Paramètres liés aux domaines.
     */
    'domain' => [
        /**
         * Domaine central où l'application principale est hébergée.
         */
        'central_domains' => [
            env('APP_URL') ? parse_url(env('APP_URL'), PHP_URL_HOST) : 'localhost',
        ],

        /**
         * Middleware à appliquer pour identifier le tenant par le domaine.
         */
        'identification_middleware' => \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
    ],

    /**
     * Paramètres liés au routage.
     */
    'routing' => [
        /**
         * Préfixe pour les routes des tenants.
         */
        'path_identification_prefix' => 'tenant',

        /**
         * Middleware à appliquer pour identifier le tenant par le chemin.
         */
        'path_identification_middleware' => \Stancl\Tenancy\Middleware\InitializeTenancyByPath::class,

        /**
         * Middleware à appliquer pour les routes des tenants.
         */
        'tenant_middleware' => [
            'web' => [
                \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
                \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            ],
            'api' => [
                \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
                \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            ],
        ],
    ],

    /**
     * Paramètres liés au cache.
     */
    'cache' => [
        /**
         * Définit si le cache doit être séparé par tenant.
         */
        'tag_base' => 'tenant',
    ],

    /**
     * Paramètres liés au stockage.
     */
    'filesystem' => [
        /**
         * Disques qui doivent être suffixés par l'ID du tenant.
         */
        'disks' => [
            'local',
            'public',
        ],

        /**
         * Préfixe de chemin pour les fichiers des tenants.
         */
        'root_override' => [
            'local' => '%storage_path%/app/tenants/%tenant_id%',
            'public' => '%storage_path%/app/public/tenants/%tenant_id%',
        ],

        /**
         * Suffixe pour les URL des assets.
         */
        'url_override' => [
            'public' => '/storage/tenants/%tenant_id%',
        ],
    ],

    /**
     * Redis est utilisé pour stocker des données temporaires comme les sessions.
     * Ici, nous définissons le préfixe utilisé pour les clés Redis des tenants.
     */
    'redis' => [
        'prefix_base' => 'tenant',
        'prefixed_connections' => [
            'default',
            'cache',
        ],
    ],

    /**
     * Fonctionnalités à activer/désactiver.
     */
    'features' => [
        /**
         * Définit si les tenants peuvent être créés via l'interface utilisateur.
         */
        'tenant_creation' => true,

        /**
         * Définit si les tenants peuvent être supprimés via l'interface utilisateur.
         */
        'tenant_deletion' => true,

        /**
         * Définit si les utilisateurs peuvent s'inscrire en tant que tenants.
         */
        'user_registration' => false,
    ],

    /**
     * Paramètres liés à la sécurité.
     */
    'security' => [
        /**
         * Définit si les tenants peuvent accéder aux données d'autres tenants.
         */
        'prevent_cross_tenant_access' => true,
    ],

    /**
     * Paramètres liés à la migration.
     */
    'migration' => [
        /**
         * Chemin vers les migrations des tenants.
         */
        'path' => database_path('migrations/tenant'),

        /**
         * Définit si les migrations des tenants doivent être exécutées lors de la mise à jour.
         */
        'auto_migrate' => true,
    ],

    /**
     * Paramètres liés à la journalisation.
     */
    'logging' => [
        /**
         * Définit si les journaux doivent être séparés par tenant.
         */
        'separate_logs' => true,

        /**
         * Chemin vers les journaux des tenants.
         */
        'log_path' => storage_path('logs/tenants/%tenant_id%'),
    ],
];

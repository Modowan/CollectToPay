<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain as BaseTenancyMiddleware;

class InitializeTenancyByDomain extends BaseTenancyMiddleware
{
    /**
     * Indique si le middleware doit être exécuté pour chaque requête.
     *
     * @var bool
     */
    protected $tenancyBootstrapped = false;

    /**
     * Gère une requête entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        // Éviter d'initialiser le tenant plusieurs fois
        if ($this->tenancyBootstrapped) {
            return $next($request);
        }

        // Récupérer le domaine de la requête
        $domain = $request->getHost();

        // Vérifier si le domaine est un domaine central
        if (in_array($domain, config('tenancy.domain.central_domains'))) {
            return $next($request);
        }

        // Trouver le tenant correspondant au domaine
        $tenant = $this->findTenant($domain);

        // Si aucun tenant n'est trouvé, continuer sans initialiser le tenant
        if (! $tenant) {
            return $next($request);
        }

        // Initialiser le tenant
        $tenant->initialize();

        // Marquer le tenant comme initialisé
        $this->tenancyBootstrapped = true;

        // Continuer avec la requête
        return $next($request);
    }

    /**
     * Trouve le tenant correspondant au domaine.
     *
     * @param  string  $domain
     * @return \App\Models\Tenant|null
     */
    protected function findTenant(string $domain)
    {
        // Utiliser le modèle de domaine pour trouver le tenant
        $domainModel = config('tenancy.domain_model');
        $domain = $domainModel::where('domain', $domain)->first();

        if (! $domain) {
            return null;
        }

        return $domain->tenant;
    }
}

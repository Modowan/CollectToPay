@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Connexion') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}" id="loginForm">
                        @csrf

                        <div class="row mb-3">
                            <label for="role" class="col-md-4 col-form-label text-md-end">{{ __('Type de compte') }}</label>

                            <div class="col-md-6">
                                <select id="role" class="form-select @error('role') is-invalid @enderror" name="role" required onchange="toggleDomainField()">
                                    <option value="" disabled selected>{{ __('Sélectionnez votre type de compte') }}</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>{{ __('Administrateur du site') }}</option>
                                    <option value="hotel_manager" {{ old('role') == 'hotel_manager' ? 'selected' : '' }}>{{ __('Gestionnaire d\'hôtel') }}</option>
                                    <option value="branch_manager" {{ old('role') == 'branch_manager' ? 'selected' : '' }}>{{ __('Gestionnaire de filiale') }}</option>
                                    <option value="customer" {{ old('role') == 'customer' ? 'selected' : '' }}>{{ __('Client') }}</option>
                                </select>

                                @error('role')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="domainField" style="display: none;">
                            <label for="domain" class="col-md-4 col-form-label text-md-end">{{ __('Nom de l\'hôtel') }}</label>

                            <div class="col-md-6">
                                <input id="domain" type="text" class="form-control @error('domain') is-invalid @enderror" name="domain" value="{{ old('domain') }}" placeholder="ex: royalpalace, grandhotel, seasideresort">

                                @error('domain')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <small class="form-text text-muted">{{ __('Entrez le nom de l\'hôtel ou de l\'entreprise touristique (sans .localhost:8000)') }}</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Adresse e-mail') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Mot de passe') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="remember">
                                        {{ __('Se souvenir de moi') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Connexion') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Mot de passe oublié ?') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Fonction pour afficher/masquer le champ domaine en fonction du rôle sélectionné
    function toggleDomainField() {
        const roleSelect = document.getElementById('role');
        const domainField = document.getElementById('domainField');
        const domainInput = document.getElementById('domain');
        
        // Si le rôle est admin, masquer le champ domaine et le rendre non requis
        if (roleSelect.value === 'admin') {
            domainField.style.display = 'none';
            domainInput.required = false;
        } else {
            // Sinon, afficher le champ domaine et le rendre requis
            domainField.style.display = 'flex';
            domainInput.required = true;
        }
    }

    // Exécuter la fonction au chargement de la page pour initialiser l'état
    document.addEventListener('DOMContentLoaded', function() {
        toggleDomainField();
    });

    // Validation du formulaire avant soumission
    document.getElementById('loginForm').addEventListener('submit', function(event) {
        const roleSelect = document.getElementById('role');
        const domainInput = document.getElementById('domain');
        
        // Si un rôle autre qu'admin est sélectionné mais que le domaine est vide
        if (roleSelect.value !== 'admin' && !domainInput.value.trim()) {
            event.preventDefault();
            alert('Veuillez entrer le nom de l\'hôtel ou de l\'entreprise touristique.');
            domainInput.focus();
        }
    });
</script>
@endsection

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><?php echo e(__('Connexion')); ?></div>

                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('login')); ?>" id="loginForm">
                        <?php echo csrf_field(); ?>

                        <div class="row mb-3">
                            <label for="role" class="col-md-4 col-form-label text-md-end"><?php echo e(__('Type de compte')); ?></label>

                            <div class="col-md-6">
                                <select id="role" class="form-select <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="role" required onchange="toggleDomainField()">
                                    <option value="" disabled selected><?php echo e(__('Sélectionnez votre type de compte')); ?></option>
                                    <option value="admin" <?php echo e(old('role') == 'admin' ? 'selected' : ''); ?>><?php echo e(__('Administrateur du site')); ?></option>
                                    <option value="hotel_manager" <?php echo e(old('role') == 'hotel_manager' ? 'selected' : ''); ?>><?php echo e(__('Gestionnaire d\'hôtel')); ?></option>
                                    <option value="branch_manager" <?php echo e(old('role') == 'branch_manager' ? 'selected' : ''); ?>><?php echo e(__('Gestionnaire de filiale')); ?></option>
                                    <option value="customer" <?php echo e(old('role') == 'customer' ? 'selected' : ''); ?>><?php echo e(__('Client')); ?></option>
                                </select>

                                <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="invalid-feedback" role="alert">
                                        <strong><?php echo e($message); ?></strong>
                                    </span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="row mb-3" id="domainField" style="display: none;">
                            <label for="domain" class="col-md-4 col-form-label text-md-end"><?php echo e(__('Nom de l\'hôtel')); ?></label>

                            <div class="col-md-6">
                                <input id="domain" type="text" class="form-control <?php $__errorArgs = ['domain'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="domain" value="<?php echo e(old('domain')); ?>" placeholder="ex: royalpalace, grandhotel, seasideresort">

                                <?php $__errorArgs = ['domain'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="invalid-feedback" role="alert">
                                        <strong><?php echo e($message); ?></strong>
                                    </span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <small class="form-text text-muted"><?php echo e(__('Entrez le nom de l\'hôtel ou de l\'entreprise touristique (sans .localhost:8000)')); ?></small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end"><?php echo e(__('Adresse e-mail')); ?></label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="email" value="<?php echo e(old('email')); ?>" required autocomplete="email" autofocus>

                                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="invalid-feedback" role="alert">
                                        <strong><?php echo e($message); ?></strong>
                                    </span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end"><?php echo e(__('Mot de passe')); ?></label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="password" required autocomplete="current-password">

                                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="invalid-feedback" role="alert">
                                        <strong><?php echo e($message); ?></strong>
                                    </span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" <?php echo e(old('remember') ? 'checked' : ''); ?>>

                                    <label class="form-check-label" for="remember">
                                        <?php echo e(__('Se souvenir de moi')); ?>

                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <?php echo e(__('Connexion')); ?>

                                </button>

                                <?php if(Route::has('password.request')): ?>
                                    <a class="btn btn-link" href="<?php echo e(route('password.request')); ?>">
                                        <?php echo e(__('Mot de passe oublié ?')); ?>

                                    </a>
                                <?php endif; ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\CollectToPay\resources\views/auth/login.blade.php ENDPATH**/ ?>
/**
 * app.js
 * 
 * Point d'entrée principal pour l'application JavaScript
 * Ce fichier initialise Vue.js et monte l'application sur l'élément DOM approprié
 */

import { createApp } from 'vue';
import { createI18n } from 'vue-i18n';
import axios from 'axios';
import Toasted from 'vue-toasted';

// Importer le composant principal Dashboard
import Dashboard from './components/Dashboard.vue';

// Configuration d'Axios
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;

// Récupérer le token CSRF depuis la balise meta
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found');
}

// Messages de traduction
const messages = {
    fr: {
        dashboard: {
            title: 'Tableau de bord',
            search_placeholder: 'Rechercher...'
        },
        navigation: {
            back: 'Retour'
        },
        status: {
            active: 'Actif',
            inactive: 'Inactif',
            completed: 'Complété',
            pending: 'En attente',
            failed: 'Échoué',
            refunded: 'Remboursé',
            revoked: 'Révoqué',
            expired: 'Expiré'
        },
        customer: {
            name: 'Nom',
            email: 'Email',
            phone: 'Téléphone',
            address: 'Adresse',
            status: 'Statut',
            actions: 'Actions'
        },
        payment_token: {
            card_type: 'Type de carte',
            last_four: 'Derniers chiffres',
            expiry: 'Expiration',
            status: 'Statut',
            default: 'Par défaut',
            actions: 'Actions'
        },
        transaction: {
            date: 'Date',
            amount: 'Montant',
            card: 'Carte',
            status: 'Statut',
            reference: 'Référence',
            actions: 'Actions',
            description: 'Description',
            currency: 'Devise',
            select_card: 'Sélectionner une carte'
        },
        tabs: {
            payment_tokens: 'Cartes enregistrées',
            transactions: 'Transactions'
        },
        actions: {
            view: 'Voir',
            edit: 'Modifier',
            delete: 'Supprimer',
            add_payment_token: 'Ajouter une carte',
            set_default: 'Définir par défaut',
            revoke: 'Révoquer',
            new_transaction: 'Nouvelle transaction',
            refund: 'Rembourser',
            details: 'Détails',
            close: 'Fermer',
            copy_link: 'Copier le lien',
            cancel: 'Annuler',
            process_payment: 'Traiter le paiement'
        },
        modals: {
            tokenization_title: 'Ajouter une nouvelle carte',
            tokenization_message: 'Utilisez le lien ci-dessous pour ajouter une nouvelle carte de paiement en toute sécurité.',
            generating_link: 'Génération du lien en cours...',
            transaction_title: 'Nouvelle transaction'
        },
        success: {
            link_copied: 'Lien copié dans le presse-papier',
            default_token_set: 'Carte définie comme par défaut',
            token_revoked: 'Carte révoquée avec succès',
            transaction_created: 'Transaction créée avec succès',
            transaction_refunded: 'Transaction remboursée avec succès'
        },
        errors: {
            fetch_tenants: 'Erreur lors de la récupération des hôtels',
            fetch_branches: 'Erreur lors de la récupération des branches',
            fetch_customers: 'Erreur lors de la récupération des clients',
            fetch_tokens: 'Erreur lors de la récupération des cartes',
            fetch_transactions: 'Erreur lors de la récupération des transactions',
            generate_link: 'Erreur lors de la génération du lien',
            copy_link: 'Erreur lors de la copie du lien',
            set_default_token: 'Erreur lors de la définition de la carte par défaut',
            revoke_token: 'Erreur lors de la révocation de la carte',
            process_transaction: 'Erreur lors du traitement de la transaction',
            refund_transaction: 'Erreur lors du remboursement de la transaction',
            missing_fields: 'Veuillez remplir tous les champs obligatoires'
        },
        confirm: {
            revoke_token: 'Êtes-vous sûr de vouloir révoquer cette carte ?',
            refund_transaction: 'Êtes-vous sûr de vouloir rembourser cette transaction ?'
        },
        info: {
            transaction_details_coming_soon: 'Les détails de transaction seront disponibles prochainement'
        }
    },
    en: {
        // English translations would go here
    }
};

// Créer l'instance i18n
const i18n = createI18n({
    locale: 'fr', // Langue par défaut
    fallbackLocale: 'en',
    messages
});

// Créer et monter l'application Vue
const app = createApp({
    components: {
        Dashboard
    }
});

// Utiliser les plugins
app.use(i18n);
app.use(Toasted, {
    position: 'bottom-right',
    duration: 3000
});

// Monter l'application sur l'élément DOM
app.mount('#app');


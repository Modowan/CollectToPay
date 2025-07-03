// resources/js/components/Dashboard.vue

<template>
  <div class="dashboard">
    <header class="dashboard-header">
      <div class="container">
        <h1>{{ $t('dashboard.title') }}</h1>
        <div class="search-container">
          <input 
            type="text" 
            v-model="searchQuery" 
            :placeholder="$t('dashboard.search_placeholder')" 
            @input="handleSearch"
          />
          <button class="search-button">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </div>
    </header>

    <div class="container">
      <div class="dashboard-content">
        <!-- Affichage des tenants (hôtels/entreprises) -->
        <div v-if="currentView === 'tenants'" class="grid-container">
          <div 
            v-for="tenant in filteredItems" 
            :key="tenant.id" 
            class="grid-item tenant-card"
            @click="selectTenant(tenant)"
          >
            <div class="tenant-logo">
              <img :src="tenant.logo || '/images/default-logo.png'" :alt="tenant.name">
            </div>
            <div class="tenant-info">
              <h3>{{ tenant.name }}</h3>
              <p>{{ tenant.status === 'active' ? $t('status.active') : $t('status.inactive') }}</p>
            </div>
          </div>
        </div>

        <!-- Affichage des branches d'un tenant -->
        <div v-else-if="currentView === 'branches'" class="grid-container">
          <div class="breadcrumb">
            <a href="#" @click.prevent="goBack">{{ $t('navigation.back') }}</a> / 
            <span>{{ selectedTenant.name }}</span>
          </div>
          
          <div 
            v-for="branch in filteredItems" 
            :key="branch.id" 
            class="grid-item branch-card"
            @click="selectBranch(branch)"
          >
            <div class="branch-icon">
              <i class="fas fa-building"></i>
            </div>
            <div class="branch-info">
              <h3>{{ branch.name }}</h3>
              <p>{{ branch.city }}, {{ branch.country }}</p>
              <p>{{ branch.status === 'active' ? $t('status.active') : $t('status.inactive') }}</p>
            </div>
          </div>
        </div>

        <!-- Affichage des clients d'une branche -->
        <div v-else-if="currentView === 'customers'" class="customer-container">
          <div class="breadcrumb">
            <a href="#" @click.prevent="goBack">{{ $t('navigation.back') }}</a> / 
            <span>{{ selectedTenant.name }}</span> / 
            <span>{{ selectedBranch.name }}</span>
          </div>
          
          <table class="customer-table">
            <thead>
              <tr>
                <th>{{ $t('customer.name') }}</th>
                <th>{{ $t('customer.email') }}</th>
                <th>{{ $t('customer.phone') }}</th>
                <th>{{ $t('customer.status') }}</th>
                <th>{{ $t('customer.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="customer in filteredItems" :key="customer.id">
                <td>{{ customer.name }}</td>
                <td>{{ customer.email }}</td>
                <td>{{ customer.phone || '-' }}</td>
                <td>
                  <span :class="'status-badge ' + customer.status">
                    {{ customer.status === 'active' ? $t('status.active') : $t('status.inactive') }}
                  </span>
                </td>
                <td>
                  <button class="btn btn-primary btn-sm" @click="viewCustomer(customer)">
                    {{ $t('actions.view') }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Affichage des détails d'un client -->
        <div v-else-if="currentView === 'customer-details'" class="customer-details">
          <div class="breadcrumb">
            <a href="#" @click.prevent="goBack">{{ $t('navigation.back') }}</a> / 
            <span>{{ selectedTenant.name }}</span> / 
            <span>{{ selectedBranch.name }}</span> / 
            <span>{{ selectedCustomer.name }}</span>
          </div>
          
          <div class="customer-profile">
            <h2>{{ selectedCustomer.name }}</h2>
            <div class="profile-info">
              <div class="info-group">
                <label>{{ $t('customer.email') }}:</label>
                <span>{{ selectedCustomer.email }}</span>
              </div>
              <div class="info-group">
                <label>{{ $t('customer.phone') }}:</label>
                <span>{{ selectedCustomer.phone || '-' }}</span>
              </div>
              <div class="info-group">
                <label>{{ $t('customer.address') }}:</label>
                <span>{{ selectedCustomer.address || '-' }}</span>
              </div>
              <div class="info-group">
                <label>{{ $t('customer.status') }}:</label>
                <span :class="'status-badge ' + selectedCustomer.status">
                  {{ selectedCustomer.status === 'active' ? $t('status.active') : $t('status.inactive') }}
                </span>
              </div>
            </div>
          </div>
          
          <div class="tabs">
            <div 
              :class="['tab', { active: activeTab === 'tokens' }]" 
              @click="activeTab = 'tokens'"
            >
              {{ $t('tabs.payment_tokens') }}
            </div>
            <div 
              :class="['tab', { active: activeTab === 'transactions' }]" 
              @click="activeTab = 'transactions'"
            >
              {{ $t('tabs.transactions') }}
            </div>
          </div>
          
          <!-- Onglet des tokens de paiement -->
          <div v-if="activeTab === 'tokens'" class="tab-content">
            <div class="action-bar">
              <button class="btn btn-primary" @click="generateTokenizationLink">
                {{ $t('actions.add_payment_token') }}
              </button>
            </div>
            
            <table class="data-table">
              <thead>
                <tr>
                  <th>{{ $t('payment_token.card_type') }}</th>
                  <th>{{ $t('payment_token.last_four') }}</th>
                  <th>{{ $t('payment_token.expiry') }}</th>
                  <th>{{ $t('payment_token.status') }}</th>
                  <th>{{ $t('payment_token.default') }}</th>
                  <th>{{ $t('payment_token.actions') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="token in paymentTokens" :key="token.id">
                  <td>{{ token.card_type || '-' }}</td>
                  <td>{{ token.last_four ? '****' + token.last_four : '-' }}</td>
                  <td>{{ token.expiry_month && token.expiry_year ? token.expiry_month + '/' + token.expiry_year.substr(-2) : '-' }}</td>
                  <td>
                    <span :class="'status-badge ' + token.status">
                      {{ $t('status.' + token.status) }}
                    </span>
                  </td>
                  <td>
                    <i v-if="token.is_default" class="fas fa-check text-success"></i>
                    <button 
                      v-else 
                      class="btn btn-sm btn-outline-primary"
                      @click="setDefaultToken(token.id)"
                    >
                      {{ $t('actions.set_default') }}
                    </button>
                  </td>
                  <td>
                    <button 
                      v-if="token.status === 'active'"
                      class="btn btn-sm btn-danger"
                      @click="revokeToken(token.id)"
                    >
                      {{ $t('actions.revoke') }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Onglet des transactions -->
          <div v-if="activeTab === 'transactions'" class="tab-content">
            <div class="action-bar">
              <button 
                class="btn btn-primary" 
                @click="showNewTransactionModal"
                :disabled="!hasActiveTokens"
              >
                {{ $t('actions.new_transaction') }}
              </button>
            </div>
            
            <table class="data-table">
              <thead>
                <tr>
                  <th>{{ $t('transaction.date') }}</th>
                  <th>{{ $t('transaction.amount') }}</th>
                  <th>{{ $t('transaction.card') }}</th>
                  <th>{{ $t('transaction.status') }}</th>
                  <th>{{ $t('transaction.reference') }}</th>
                  <th>{{ $t('transaction.actions') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="transaction in paymentTransactions" :key="transaction.id">
                  <td>{{ formatDate(transaction.created_at) }}</td>
                  <td>{{ formatAmount(transaction.amount, transaction.currency) }}</td>
                  <td>{{ transaction.card_info }}</td>
                  <td>
                    <span :class="'status-badge ' + transaction.status">
                      {{ $t('status.' + transaction.status) }}
                    </span>
                  </td>
                  <td>{{ transaction.reference || '-' }}</td>
                  <td>
                    <button 
                      v-if="transaction.status === 'completed'"
                      class="btn btn-sm btn-warning"
                      @click="refundTransaction(transaction.id)"
                    >
                      {{ $t('actions.refund') }}
                    </button>
                    <button 
                      class="btn btn-sm btn-info"
                      @click="viewTransaction(transaction.id)"
                    >
                      {{ $t('actions.details') }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal pour la génération de lien de tokenisation -->
    <div v-if="showTokenizationModal" class="modal-overlay">
      <div class="modal-container">
        <div class="modal-header">
          <h3>{{ $t('modals.tokenization_title') }}</h3>
          <button class="close-button" @click="showTokenizationModal = false">&times;</button>
        </div>
        <div class="modal-body">
          <p>{{ $t('modals.tokenization_message') }}</p>
          <div v-if="tokenizationUrl" class="tokenization-link">
            <a :href="tokenizationUrl" target="_blank">{{ tokenizationUrl }}</a>
          </div>
          <div v-else class="loading">
            <i class="fas fa-spinner fa-spin"></i> {{ $t('modals.generating_link') }}
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showTokenizationModal = false">
            {{ $t('actions.close') }}
          </button>
          <button 
            v-if="tokenizationUrl"
            class="btn btn-primary" 
            @click="copyTokenizationLink"
          >
            {{ $t('actions.copy_link') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal pour nouvelle transaction -->
    <div v-if="showTransactionModal" class="modal-overlay">
      <div class="modal-container">
        <div class="modal-header">
          <h3>{{ $t('modals.transaction_title') }}</h3>
          <button class="close-button" @click="showTransactionModal = false">&times;</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="processTransaction">
            <div class="form-group">
              <label for="token">{{ $t('transaction.select_card') }}</label>
              <select id="token" v-model="newTransaction.token_id" class="form-control" required>
                <option v-for="token in activeTokens" :key="token.id" :value="token.id">
                  {{ token.card_type }} - **** {{ token.last_four }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label for="amount">{{ $t('transaction.amount') }}</label>
              <input 
                type="number" 
                id="amount" 
                v-model="newTransaction.amount" 
                class="form-control" 
                step="0.01" 
                min="0.01" 
                required
              />
            </div>
            <div class="form-group">
              <label for="currency">{{ $t('transaction.currency') }}</label>
              <select id="currency" v-model="newTransaction.currency" class="form-control" required>
                <option value="EUR">EUR</option>
                <option value="USD">USD</option>
                <option value="GBP">GBP</option>
                <option value="CHF">CHF</option>
              </select>
            </div>
            <div class="form-group">
              <label for="description">{{ $t('transaction.description') }}</label>
              <textarea 
                id="description" 
                v-model="newTransaction.description" 
                class="form-control"
              ></textarea>
            </div>
            <div class="form-group">
              <label for="reference">{{ $t('transaction.reference') }}</label>
              <input 
                type="text" 
                id="reference" 
                v-model="newTransaction.reference" 
                class="form-control"
              />
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showTransactionModal = false">
            {{ $t('actions.cancel') }}
          </button>
          <button 
            class="btn btn-primary" 
            @click="processTransaction"
            :disabled="isProcessingTransaction"
          >
            <i v-if="isProcessingTransaction" class="fas fa-spinner fa-spin"></i>
            {{ $t('actions.process_payment') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Dashboard',
  data() {
    return {
      currentView: 'tenants',
      searchQuery: '',
      selectedTenant: null,
      selectedBranch: null,
      selectedCustomer: null,
      tenants: [],
      branches: [],
      customers: [],
      paymentTokens: [],
      paymentTransactions: [],
      activeTab: 'tokens',
      showTokenizationModal: false,
      tokenizationUrl: null,
      showTransactionModal: false,
      newTransaction: {
        token_id: null,
        amount: null,
        currency: 'EUR',
        description: '',
        reference: ''
      },
      isProcessingTransaction: false
    };
  },
  computed: {
    filteredItems() {
      const query = this.searchQuery.toLowerCase();
      
      if (this.currentView === 'tenants') {
        return this.tenants.filter(tenant => 
          tenant.name.toLowerCase().includes(query)
        );
      } else if (this.currentView === 'branches') {
        return this.branches.filter(branch => 
          branch.name.toLowerCase().includes(query) || 
          (branch.city && branch.city.toLowerCase().includes(query))
        );
      } else if (this.currentView === 'customers') {
        return this.customers.filter(customer => 
          customer.name.toLowerCase().includes(query) || 
          customer.email.toLowerCase().includes(query) ||
          (customer.phone && customer.phone.toLowerCase().includes(query))
        );
      }
      
      return [];
    },
    activeTokens() {
      return this.paymentTokens.filter(token => token.status === 'active');
    },
    hasActiveTokens() {
      return this.activeTokens.length > 0;
    }
  },
  created() {
    this.fetchTenants();
  },
  methods: {
    // Navigation et sélection
    selectTenant(tenant) {
      this.selectedTenant = tenant;
      this.currentView = 'branches';
      this.searchQuery = '';
      this.fetchBranches(tenant.id);
    },
    selectBranch(branch) {
      this.selectedBranch = branch;
      this.currentView = 'customers';
      this.searchQuery = '';
      this.fetchCustomers(branch.id);
    },
    viewCustomer(customer) {
      this.selectedCustomer = customer;
      this.currentView = 'customer-details';
      this.activeTab = 'tokens';
      this.fetchPaymentTokens(customer.id);
      this.fetchPaymentTransactions(customer.id);
    },
    goBack() {
      if (this.currentView === 'branches') {
        this.currentView = 'tenants';
        this.selectedTenant = null;
      } else if (this.currentView === 'customers') {
        this.currentView = 'branches';
        this.selectedBranch = null;
      } else if (this.currentView === 'customer-details') {
        this.currentView = 'customers';
        this.selectedCustomer = null;
      }
      this.searchQuery = '';
    },
    
    // Recherche
    handleSearch() {
      // La recherche est gérée automatiquement via le computed property filteredItems
    },
    
    // Récupération des données
    async fetchTenants() {
      try {
        const response = await axios.get('/api/tenants');
        this.tenants = response.data;
      } catch (error) {
        console.error('Erreur lors de la récupération des tenants:', error);
        this.$toasted.error(this.$t('errors.fetch_tenants'));
      }
    },
    async fetchBranches(tenantId) {
      try {
        const response = await axios.get(`/api/tenants/${tenantId}/branches`);
        this.branches = response.data;
      } catch (error) {
        console.error('Erreur lors de la récupération des branches:', error);
        this.$toasted.error(this.$t('errors.fetch_branches'));
      }
    },
    async fetchCustomers(branchId) {
      try {
        const response = await axios.get(`/api/branches/${branchId}/customers`);
        this.customers = response.data;
      } catch (error) {
        console.error('Erreur lors de la récupération des clients:', error);
        this.$toasted.error(this.$t('errors.fetch_customers'));
      }
    },
    async fetchPaymentTokens(customerId) {
      try {
        const response = await axios.get(`/api/customers/${customerId}/payment-tokens`);
        this.paymentTokens = response.data;
      } catch (error) {
        console.error('Erreur lors de la récupération des tokens de paiement:', error);
        this.$toasted.error(this.$t('errors.fetch_tokens'));
      }
    },
    async fetchPaymentTransactions(customerId) {
      try {
        const response = await axios.get(`/api/customers/${customerId}/payment-transactions`);
        this.paymentTransactions = response.data;
      } catch (error) {
        console.error('Erreur lors de la récupération des transactions:', error);
        this.$toasted.error(this.$t('errors.fetch_transactions'));
      }
    },
    
    // Gestion des tokens de paiement
    async generateTokenizationLink() {
      this.showTokenizationModal = true;
      this.tokenizationUrl = null;
      
      try {
        const response = await axios.post('/api/ixopay/tokenization-link', {
          customer_id: this.selectedCustomer.id,
          return_url: `${window.location.origin}/tokenization-callback`
        });
        
        if (response.data.success) {
          this.tokenizationUrl = response.data.tokenization_url;
        } else {
          this.$toasted.error(this.$t('errors.generate_link'));
        }
      } catch (error) {
        console.error('Erreur lors de la génération du lien de tokenisation:', error);
        this.$toasted.error(this.$t('errors.generate_link'));
      }
    },
    copyTokenizationLink() {
      navigator.clipboard.writeText(this.tokenizationUrl)
        .then(() => {
          this.$toasted.success(this.$t('success.link_copied'));
        })
        .catch(() => {
          this.$toasted.error(this.$t('errors.copy_link'));
        });
    },
    async setDefaultToken(tokenId) {
      try {
        await axios.post(`/api/payment-tokens/${tokenId}/set-default`);
        this.$toasted.success(this.$t('success.default_token_set'));
        this.fetchPaymentTokens(this.selectedCustomer.id);
      } catch (error) {
        console.error('Erreur lors de la définition du token par défaut:', error);
        this.$toasted.error(this.$t('errors.set_default_token'));
      }
    },
    async revokeToken(tokenId) {
      if (confirm(this.$t('confirm.revoke_token'))) {
        try {
          await axios.post(`/api/payment-tokens/${tokenId}/revoke`);
          this.$toasted.success(this.$t('success.token_revoked'));
          this.fetchPaymentTokens(this.selectedCustomer.id);
        } catch (error) {
          console.error('Erreur lors de la révocation du token:', error);
          this.$toasted.error(this.$t('errors.revoke_token'));
        }
      }
    },
    
    // Gestion des transactions
    showNewTransactionModal() {
      this.showTransactionModal = true;
      this.newTransaction = {
        token_id: this.activeTokens.length > 0 ? this.activeTokens[0].id : null,
        amount: null,
        currency: 'EUR',
        description: '',
        reference: ''
      };
    },
    async processTransaction() {
      if (!this.newTransaction.token_id || !this.newTransaction.amount) {
        this.$toasted.error(this.$t('errors.missing_fields'));
        return;
      }
      
      this.isProcessingTransaction = true;
      
      try {
        const response = await axios.post(`/api/customers/${this.selectedCustomer.id}/payment-transactions`, this.newTransaction);
        
        this.showTransactionModal = false;
        this.$toasted.success(this.$t('success.transaction_created'));
        this.fetchPaymentTransactions(this.selectedCustomer.id);
        this.activeTab = 'transactions';
      } catch (error) {
        console.error('Erreur lors du traitement de la transaction:', error);
        this.$toasted.error(this.$t('errors.process_transaction'));
      } finally {
        this.isProcessingTransaction = false;
      }
    },
    async refundTransaction(transactionId) {
      if (confirm(this.$t('confirm.refund_transaction'))) {
        try {
          await axios.post(`/api/payment-transactions/${transactionId}/refund`);
          this.$toasted.success(this.$t('success.transaction_refunded'));
          this.fetchPaymentTransactions(this.selectedCustomer.id);
        } catch (error) {
          console.error('Erreur lors du remboursement de la transaction:', error);
          this.$toasted.error(this.$t('errors.refund_transaction'));
        }
      }
    },
    viewTransaction(transactionId) {
      // Rediriger vers la page de détails de la transaction ou afficher un modal
      // Pour simplifier, on pourrait implémenter cette fonctionnalité plus tard
      this.$toasted.info(this.$t('info.transaction_details_coming_soon'));
    },
    
    // Formatage
    formatDate(dateString) {
      const date = new Date(dateString);
      return new Intl.DateTimeFormat(this.$i18n.locale, {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      }).format(date);
    },
    formatAmount(amount, currency) {
      return new Intl.NumberFormat(this.$i18n.locale, {
        style: 'currency',
        currency: currency
      }).format(amount);
    }
  }
};
</script>

<style scoped>
.dashboard {
  min-height: 100vh;
  background-color: #f8f9fa;
}

.dashboard-header {
  background-color: #fff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  padding: 1.5rem 0;
  margin-bottom: 2rem;
}

.dashboard-header h1 {
  margin: 0;
  color: #333;
  font-size: 1.8rem;
}

.search-container {
  position: relative;
  max-width: 400px;
  margin-top: 1rem;
}

.search-container input {
  width: 100%;
  padding: 0.75rem 1rem;
  padding-right: 3rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

.search-button {
  position: absolute;
  right: 0;
  top: 0;
  height: 100%;
  width: 3rem;
  background: none;
  border: none;
  color: #666;
  cursor: pointer;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.dashboard-content {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  padding: 2rem;
}

.breadcrumb {
  margin-bottom: 1.5rem;
  font-size: 0.9rem;
  color: #666;
}

.breadcrumb a {
  color: #3490dc;
  text-decoration: none;
}

.breadcrumb a:hover {
  text-decoration: underline;
}

/* Grid layout pour les tenants et branches */
.grid-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.5rem;
}

.grid-item {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 1.5rem;
  transition: transform 0.2s, box-shadow 0.2s;
  cursor: pointer;
}

.grid-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.tenant-logo, .branch-icon {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 80px;
  margin-bottom: 1rem;
}

.tenant-logo img {
  max-height: 100%;
  max-width: 100%;
  object-fit: contain;
}

.branch-icon i {
  font-size: 3rem;
  color: #3490dc;
}

.tenant-info, .branch-info {
  text-align: center;
}

.tenant-info h3, .branch-info h3 {
  margin: 0 0 0.5rem;
  font-size: 1.2rem;
}

.tenant-info p, .branch-info p {
  margin: 0;
  color: #666;
  font-size: 0.9rem;
}

/* Table pour les clients */
.customer-table {
  width: 100%;
  border-collapse: collapse;
}

.customer-table th, .customer-table td {
  padding: 0.75rem 1rem;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.customer-table th {
  background-color: #f8f9fa;
  font-weight: 600;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 500;
}

.status-badge.active {
  background-color: #d4edda;
  color: #155724;
}

.status-badge.inactive {
  background-color: #f8d7da;
  color: #721c24;
}

.status-badge.completed {
  background-color: #d4edda;
  color: #155724;
}

.status-badge.pending {
  background-color: #fff3cd;
  color: #856404;
}

.status-badge.failed {
  background-color: #f8d7da;
  color: #721c24;
}

.status-badge.refunded {
  background-color: #d1ecf1;
  color: #0c5460;
}

.status-badge.expired, .status-badge.revoked {
  background-color: #e2e3e5;
  color: #383d41;
}

/* Détails du client */
.customer-profile {
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #eee;
}

.profile-info {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.info-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 0.25rem;
  color: #666;
  font-size: 0.9rem;
}

/* Tabs */
.tabs {
  display: flex;
  border-bottom: 1px solid #eee;
  margin-bottom: 1.5rem;
}

.tab {
  padding: 0.75rem 1.5rem;
  cursor: pointer;
  font-weight: 500;
  color: #666;
  border-bottom: 2px solid transparent;
}

.tab.active {
  color: #3490dc;
  border-bottom-color: #3490dc;
}

.tab-content {
  min-height: 300px;
}

.action-bar {
  margin-bottom: 1rem;
  display: flex;
  justify-content: flex-end;
}

/* Tables de données */
.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th, .data-table td {
  padding: 0.75rem 1rem;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.data-table th {
  background-color: #f8f9fa;
  font-weight: 600;
}

/* Modals */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-container {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #eee;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
}

.close-button {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #eee;
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}

.tokenization-link {
  margin-top: 1rem;
  padding: 0.75rem;
  background-color: #f8f9fa;
  border-radius: 4px;
  word-break: break-all;
}

.loading {
  text-align: center;
  padding: 1rem 0;
}

/* Formulaires */
.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

.form-control {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

textarea.form-control {
  min-height: 100px;
}

/* Boutons */
.btn {
  display: inline-block;
  font-weight: 500;
  text-align: center;
  white-space: nowrap;
  vertical-align: middle;
  user-select: none;
  border: 1px solid transparent;
  padding: 0.5rem 1rem;
  font-size: 1rem;
  line-height: 1.5;
  border-radius: 0.25rem;
  transition: color 0.15s, background-color 0.15s, border-color 0.15s;
  cursor: pointer;
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
  border-radius: 0.2rem;
}

.btn-primary {
  color: #fff;
  background-color: #3490dc;
  border-color: #3490dc;
}

.btn-primary:hover {
  background-color: #2779bd;
  border-color: #2779bd;
}

.btn-secondary {
  color: #fff;
  background-color: #6c757d;
  border-color: #6c757d;
}

.btn-secondary:hover {
  background-color: #5a6268;
  border-color: #5a6268;
}

.btn-danger {
  color: #fff;
  background-color: #e3342f;
  border-color: #e3342f;
}

.btn-danger:hover {
  background-color: #c82333;
  border-color: #c82333;
}

.btn-warning {
  color: #212529;
  background-color: #ffed4a;
  border-color: #ffed4a;
}

.btn-warning:hover {
  background-color: #ffe924;
  border-color: #ffe924;
}

.btn-info {
  color: #fff;
  background-color: #6cb2eb;
  border-color: #6cb2eb;
}

.btn-info:hover {
  background-color: #4aa0e6;
  border-color: #4aa0e6;
}

.btn-outline-primary {
  color: #3490dc;
  background-color: transparent;
  border-color: #3490dc;
}

.btn-outline-primary:hover {
  color: #fff;
  background-color: #3490dc;
  border-color: #3490dc;
}

.btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

/* Responsive */
@media (max-width: 768px) {
  .grid-container {
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  }
  
  .profile-info {
    grid-template-columns: 1fr;
  }
  
  .customer-table, .data-table {
    display: block;
    overflow-x: auto;
  }
}
</style>

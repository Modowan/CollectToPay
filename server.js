const express = require('express');
const bodyParser = require('body-parser');

const app = express();
app.use(bodyParser.json());

// Route pour la vérification 3DS
app.post('/verify-3ds', async (req, res) => {
    try {
        const { token_id, amount, currency, return_url, merchant_info } = req.body;
        
        // Logique de vérification 3DS ici
        const result = {
            success: true,
            transaction_id: 'txn_' + Date.now(),
            auth_url: 'https://3ds-auth.example.com',
            challenge_required: true
        };
        
        res.json(result );
    } catch (error) {
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Route de santé
app.get('/health', (req, res) => {
    res.json({ status: 'healthy' });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, '0.0.0.0', () => {
    console.log(`Enclave running on port ${PORT}`);
});

exports.handler = async (data, context) => {
    try {
        const { token_id, amount, currency, return_url, merchant_info } = data;
        const cardData = await context.decrypt(token_id);
        
        return {
            success: true,
            transaction_id: 'txn_123',
            auth_url: 'https://3ds-auth.example.com',
            challenge_required: true
        };
    } catch (error ) {
        return {
            success: false,
            error: error.message
        };
    }
};


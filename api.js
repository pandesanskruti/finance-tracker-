// API object to handle all API calls
const API = {
    // Authentication endpoints
    auth: {
        register: async (data) => {
            try {
                console.log('Sending registration request to:', '/MP1/api/auth.php?action=register');
                const response = await fetch('/MP1/api/auth.php?action=register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                console.log('Register response:', result);
                return result;
            } catch (error) {
                console.error('Registration error:', error);
                return { success: false, message: 'Failed to register: ' + error.message };
            }
        },

        login: async (data) => {
            try {
                console.log('Sending login request to:', '/MP1/api/auth.php?action=login');
                const response = await fetch('/MP1/api/auth.php?action=login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                console.log('Login response:', result);
                return result;
            } catch (error) {
                console.error('Login error:', error);
                return { success: false, message: 'Failed to login: ' + error.message };
            }
        },

        logout: async () => {
            try {
                console.log('Sending logout request to:', '/MP1/api/auth.php?action=logout');
                const response = await fetch('/MP1/api/auth.php?action=logout');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                console.log('Logout response:', result);
                return result;
            } catch (error) {
                console.error('Logout error:', error);
                return { success: false, message: 'Failed to logout: ' + error.message };
            }
        },

        check: async () => {
            try {
                console.log('Sending auth check request to:', '/MP1/api/auth.php?action=check');
                const response = await fetch('/MP1/api/auth.php?action=check');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                console.log('Auth check response:', result);
                return result;
            } catch (error) {
                console.error('Auth check error:', error);
                return { success: false, message: 'Failed to check authentication: ' + error.message };
            }
        }
    },

    // Transactions endpoints
    transactions: {
        add: async (data) => {
            try {
                const response = await fetch('./api/transactions.php?action=add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                return await response.json();
            } catch (error) {
                console.error('Add transaction error:', error);
                return { success: false, message: 'Failed to add transaction' };
            }
        },

        get: async (filters = {}) => {
            try {
                const response = await fetch('./api/transactions.php?action=get', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(filters)
                });
                return await response.json();
            } catch (error) {
                console.error('Get transactions error:', error);
                return { success: false, message: 'Failed to get transactions' };
            }
        },

        delete: async (id) => {
            try {
                const response = await fetch('./api/transactions.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
                });
                return await response.json();
            } catch (error) {
                console.error('Delete transaction error:', error);
                return { success: false, message: 'Failed to delete transaction' };
            }
        },

        stats: async () => {
            try {
                const response = await fetch('./api/transactions.php?action=stats');
                return await response.json();
            } catch (error) {
                console.error('Get transaction stats error:', error);
                return { success: false, message: 'Failed to get transaction stats' };
            }
        }
    },

    // Budgets endpoints
    budgets: {
        set: async (data) => {
            try {
                const response = await fetch('./api/budgets.php?action=set', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                return await response.json();
            } catch (error) {
                console.error('Set budget error:', error);
                return { success: false, message: 'Failed to set budget' };
            }
        },

        get: async (month = null, year = null) => {
            try {
                const response = await fetch('./api/budgets.php?action=get', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ month, year })
                });
                return await response.json();
            } catch (error) {
                console.error('Get budgets error:', error);
                return { success: false, message: 'Failed to get budgets' };
            }
        },

        stats: async (month, year) => {
            try {
                const response = await fetch('./api/budgets.php?action=stats', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ month, year })
                });
                return await response.json();
            } catch (error) {
                console.error('Get budget stats error:', error);
                return { success: false, message: 'Failed to get budget stats' };
            }
        }
    },

    // Goals endpoints
    goals: {
        add: async (data) => {
            try {
                const response = await fetch('./api/goals.php?action=add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                return await response.json();
            } catch (error) {
                console.error('Add goal error:', error);
                return { success: false, message: 'Failed to add goal' };
            }
        },

        get: async () => {
            try {
                const response = await fetch('./api/goals.php?action=get');
                return await response.json();
            } catch (error) {
                console.error('Get goals error:', error);
                return { success: false, message: 'Failed to get goals' };
            }
        },

        update: async (data) => {
            try {
                const response = await fetch('./api/goals.php?action=update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                return await response.json();
            } catch (error) {
                console.error('Update goal error:', error);
                return { success: false, message: 'Failed to update goal' };
            }
        },

        delete: async (id) => {
            try {
                const response = await fetch('./api/goals.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
                });
                return await response.json();
            } catch (error) {
                console.error('Delete goal error:', error);
                return { success: false, message: 'Failed to delete goal' };
            }
        },

        stats: async () => {
            try {
                const response = await fetch('./api/goals.php?action=stats');
                return await response.json();
            } catch (error) {
                console.error('Get goal stats error:', error);
                return { success: false, message: 'Failed to get goal stats' };
            }
        }
    },

    // Investments
    investments: {
        add: async (type, amount, return_rate, start_date, end_date = null) => {
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('type', type);
            formData.append('amount', amount);
            formData.append('return_rate', return_rate);
            formData.append('start_date', start_date);
            if (end_date) formData.append('end_date', end_date);
            
            const response = await fetch('./api/investment.php', {
                method: 'POST',
                body: formData
            });
            return await response.json();
        },
        
        get: async () => {
            const formData = new FormData();
            formData.append('action', 'get');
            
            const response = await fetch('./api/investment.php', {
                method: 'POST',
                body: formData
            });
            return await response.json();
        },
        
        update: async (investment_id, data) => {
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('investment_id', investment_id);
            Object.entries(data).forEach(([key, value]) => {
                formData.append(key, value);
            });
            
            const response = await fetch('./api/investment.php', {
                method: 'POST',
                body: formData
            });
            return await response.json();
        },
        
        delete: async (investment_id) => {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('investment_id', investment_id);
            
            const response = await fetch('./api/investment.php', {
                method: 'POST',
                body: formData
            });
            return await response.json();
        },
        
        getStats: async () => {
            const formData = new FormData();
            formData.append('action', 'stats');
            
            const response = await fetch('./api/investment.php', {
                method: 'POST',
                body: formData
            });
            return await response.json();
        }
    },
    
    // Loans
    loans: {
        add: async (type, amount, interest_rate, start_date, end_date) => {
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('type', type);
            formData.append('amount', amount);
            formData.append('interest_rate', interest_rate);
            formData.append('start_date', start_date);
            formData.append('end_date', end_date);
            
            const response = await fetch('./api/loans.php', {
                method: 'POST',
                body: formData
            });
            return await response.json();
        },
        
        get: async () => {
            const formData = new FormData();
            formData.append('action', 'get');
            
            const response = await fetch('./api/loans.php', {
                method: 'POST',
                body: formData
            });
            return await response.json();
        },
        
        update: async (loan_id, data) => {
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('loan_id', loan_id);
            Object.entries(data).forEach(([key, value]) => {
                formData.append(key, value);
            });
            
            const response = await fetch('./api/loans.php', {
                method: 'POST',
                body: formData
            });
            return await response.json();
        },
        
        delete: async (loan_id) => {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('loan_id', loan_id);
            
            const response = await fetch('./api/loans.php', {
                method: 'POST',
                body: formData
            });
            return await response.json();
        },
        
        getStats: async () => {
            const formData = new FormData();
            formData.append('action', 'stats');
            
            const response = await fetch('./api/loans.php', {
                method: 'POST',
                body: formData
            });
            return await response.json();
        }
    }
}; 
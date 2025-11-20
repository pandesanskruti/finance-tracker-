// Main application JavaScript file
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeComponents();
    
    // Add global event listeners
    setupGlobalEventListeners();
});

// Initialize all components
function initializeComponents() {
    // Initialize charts if on dashboard
    if (document.getElementById('incomeExpenseChart')) {
        initializeCharts();
        loadDashboardData();
    }
    
    // Initialize goals if on goals page
    if (document.getElementById('goalsList')) {
        loadGoalsData();
    }
    
    // Initialize budget if on budget page
    if (document.getElementById('budgetList')) {
        loadBudgetData();
    }
    
    // Initialize transactions if on transactions page
    if (document.getElementById('transactionsList')) {
        loadTransactionsData();
    }
}

// Setup global event listeners
function setupGlobalEventListeners() {
    // Form validation listeners
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this.id)) {
                e.preventDefault();
                showNotification('Please fill in all required fields', 'error');
            }
        });
    });

    // Input validation listeners
    const inputs = document.querySelectorAll('input[required]');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('error');
            }
        });
    });

    // Filter listeners
    const filterInputs = document.querySelectorAll('.filter-input');
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            const page = window.location.pathname.split('/').pop().split('.')[0];
            switch(page) {
                case 'dashboard':
                    loadDashboardData();
                    break;
                case 'transactions':
                    loadTransactionsData();
                    break;
                case 'budget':
                    loadBudgetData();
                    break;
            }
        });
    });
}

// Utility Functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });

    return isValid;
}

// Loading and Notification Functions
function showLoading() {
    const spinner = document.createElement('div');
    spinner.className = 'loading-spinner';
    document.body.appendChild(spinner);
}

function hideLoading() {
    const spinner = document.querySelector('.loading-spinner');
    if (spinner) {
        spinner.remove();
    }
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function handleAjaxError(error) {
    console.error('AJAX Error:', error);
    showNotification('An error occurred. Please try again.', 'error');
}

// Chart Functions
function initializeCharts() {
    // Income vs Expenses Chart
    const incomeExpenseCtx = document.getElementById('incomeExpenseChart');
    if (incomeExpenseCtx) {
        new Chart(incomeExpenseCtx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Income',
                        data: [],
                        backgroundColor: '#4CAF50'
                    },
                    {
                        label: 'Expenses',
                        data: [],
                        backgroundColor: '#F44336'
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Budget Progress Chart
    const budgetProgressCtx = document.getElementById('budgetProgressChart');
    if (budgetProgressCtx) {
        new Chart(budgetProgressCtx, {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        '#4CAF50',
                        '#F44336',
                        '#2196F3',
                        '#FFC107',
                        '#9C27B0'
                    ]
                }]
            },
            options: {
                responsive: true
            }
        });
    }
}

// Data Loading Functions
function loadDashboardData() {
    showLoading();
    const period = document.getElementById('period')?.value || 'month';
    
    fetch(`dashboard_actions.php?action=get_data&period=${period}`)
        .then(response => response.json())
        .then(data => {
            updateDashboardUI(data);
            hideLoading();
        })
        .catch(error => {
            handleAjaxError(error);
            hideLoading();
        });
}

function loadGoalsData() {
    showLoading();
    fetch('goal_actions.php?action=get_goals')
        .then(response => response.json())
        .then(data => {
            updateGoalsUI(data);
            hideLoading();
        })
        .catch(error => {
            handleAjaxError(error);
            hideLoading();
        });
}

function loadBudgetData() {
    showLoading();
    fetch('budget_actions.php?action=get_budget')
        .then(response => response.json())
        .then(data => {
            updateBudgetUI(data);
            hideLoading();
        })
        .catch(error => {
            handleAjaxError(error);
            hideLoading();
        });
}

function loadTransactionsData() {
    showLoading();
    const period = document.getElementById('period')?.value || 'month';
    
    fetch(`transaction_actions.php?action=get_transactions&period=${period}`)
        .then(response => response.json())
        .then(data => {
            updateTransactionsUI(data);
            hideLoading();
        })
        .catch(error => {
            handleAjaxError(error);
            hideLoading();
        });
}

// UI Update Functions
function updateDashboardUI(data) {
    if (data.summary) {
        document.getElementById('totalIncome').textContent = formatCurrency(data.summary.total_income);
        document.getElementById('totalExpenses').textContent = formatCurrency(data.summary.total_expenses);
        document.getElementById('netSavings').textContent = formatCurrency(data.summary.net_savings);
        document.getElementById('budgetProgress').textContent = `${data.summary.budget_progress}%`;
    }
    updateCharts(data);
}

function updateCharts(data) {
    const incomeExpenseChart = Chart.getChart('incomeExpenseChart');
    if (incomeExpenseChart && data.income_expense) {
        incomeExpenseChart.data.labels = data.income_expense.labels;
        incomeExpenseChart.data.datasets[0].data = data.income_expense.income;
        incomeExpenseChart.data.datasets[1].data = data.income_expense.expenses;
        incomeExpenseChart.update();
    }

    const budgetProgressChart = Chart.getChart('budgetProgressChart');
    if (budgetProgressChart && data.budget_progress) {
        budgetProgressChart.data.labels = data.budget_progress.labels;
        budgetProgressChart.data.datasets[0].data = data.budget_progress.data;
        budgetProgressChart.update();
    }
}

// Export functions for use in other files
window.app = {
    formatCurrency,
    formatDate,
    validateForm,
    showLoading,
    hideLoading,
    showNotification,
    handleAjaxError,
    loadDashboardData,
    loadGoalsData,
    loadBudgetData,
    loadTransactionsData
}; 
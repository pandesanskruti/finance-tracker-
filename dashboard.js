// Dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    initializeCharts();
    
    // Load dashboard data
    loadDashboardData();
    
    // Add event listeners for filters
    const filterInputs = document.querySelectorAll('.filter-input');
    filterInputs.forEach(input => {
        input.addEventListener('change', loadDashboardData);
    });
});

// Initialize charts
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

// Load dashboard data
function loadDashboardData() {
    showLoading();
    
    // Get filter values
    const period = document.getElementById('period')?.value || 'month';
    
    // Fetch dashboard data
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

// Update dashboard UI with data
function updateDashboardUI(data) {
    // Update summary cards
    if (data.summary) {
        document.getElementById('totalIncome').textContent = formatCurrency(data.summary.total_income);
        document.getElementById('totalExpenses').textContent = formatCurrency(data.summary.total_expenses);
        document.getElementById('netSavings').textContent = formatCurrency(data.summary.net_savings);
        document.getElementById('budgetProgress').textContent = `${data.summary.budget_progress}%`;
    }

    // Update charts
    updateCharts(data);
}

// Update charts with new data
function updateCharts(data) {
    // Update Income vs Expenses Chart
    const incomeExpenseChart = Chart.getChart('incomeExpenseChart');
    if (incomeExpenseChart && data.income_expense) {
        incomeExpenseChart.data.labels = data.income_expense.labels;
        incomeExpenseChart.data.datasets[0].data = data.income_expense.income;
        incomeExpenseChart.data.datasets[1].data = data.income_expense.expenses;
        incomeExpenseChart.update();
    }

    // Update Budget Progress Chart
    const budgetProgressChart = Chart.getChart('budgetProgressChart');
    if (budgetProgressChart && data.budget_progress) {
        budgetProgressChart.data.labels = data.budget_progress.labels;
        budgetProgressChart.data.datasets[0].data = data.budget_progress.data;
        budgetProgressChart.update();
    }
} 
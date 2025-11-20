// Budget management functionality
document.addEventListener('DOMContentLoaded', function() {
    // Load budget data
    loadBudgetData();
    
    // Add event listeners
    document.getElementById('addBudgetForm')?.addEventListener('submit', handleAddBudget);
    document.getElementById('editBudgetForm')?.addEventListener('submit', handleEditBudget);
});

// Handle add budget
function handleAddBudget(e) {
    e.preventDefault();
    
    if (!app.validateForm('addBudgetForm')) return;
    
    const formData = new FormData(e.target);
    formData.append('action', 'add_budget');
    
    app.showLoading();
    
    fetch('budget_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            app.showNotification('Budget added successfully');
            loadBudgetData();
            e.target.reset();
        } else {
            app.showNotification(data.message || 'Failed to add budget', 'error');
        }
        app.hideLoading();
    })
    .catch(error => {
        app.handleAjaxError(error);
        app.hideLoading();
    });
}

// Handle edit budget
function handleEditBudget(e) {
    e.preventDefault();
    
    if (!app.validateForm('editBudgetForm')) return;
    
    const formData = new FormData(e.target);
    formData.append('action', 'edit_budget');
    
    app.showLoading();
    
    fetch('budget_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            app.showNotification('Budget updated successfully');
            loadBudgetData();
            closeEditModal();
        } else {
            app.showNotification(data.message || 'Failed to update budget', 'error');
        }
        app.hideLoading();
    })
    .catch(error => {
        app.handleAjaxError(error);
        app.hideLoading();
    });
}

// Update budget UI
function updateBudgetUI(data) {
    const budgetList = document.getElementById('budgetList');
    if (!budgetList) return;

    budgetList.innerHTML = '';
    
    data.forEach(budget => {
        const spentPercentage = (budget.spent / budget.amount) * 100;
        const status = spentPercentage >= 100 ? 'exceeded' : spentPercentage >= 80 ? 'warning' : 'good';
        
        const budgetElement = document.createElement('div');
        budgetElement.className = `budget-item ${status}`;
        budgetElement.innerHTML = `
            <div class="budget-info">
                <h3>${budget.category}</h3>
                <p>Budget: ${app.formatCurrency(budget.amount)}</p>
                <p>Spent: ${app.formatCurrency(budget.spent)}</p>
                <div class="progress-bar">
                    <div class="progress" style="width: ${Math.min(spentPercentage, 100)}%"></div>
                </div>
                <p class="status">${status.charAt(0).toUpperCase() + status.slice(1)}</p>
            </div>
            <div class="budget-actions">
                <button onclick="editBudget(${budget.id})" class="btn-edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="deleteBudget(${budget.id})" class="btn-delete">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        budgetList.appendChild(budgetElement);
    });
}

// Edit budget
function editBudget(id) {
    app.showLoading();
    
    fetch(`budget_actions.php?action=get_budget&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showEditModal(data.budget);
            } else {
                app.showNotification(data.message || 'Failed to load budget', 'error');
            }
            app.hideLoading();
        })
        .catch(error => {
            app.handleAjaxError(error);
            app.hideLoading();
        });
}

// Delete budget
function deleteBudget(id) {
    if (!confirm('Are you sure you want to delete this budget?')) return;
    
    app.showLoading();
    
    fetch('budget_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=delete_budget&id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            app.showNotification('Budget deleted successfully');
            loadBudgetData();
        } else {
            app.showNotification(data.message || 'Failed to delete budget', 'error');
        }
        app.hideLoading();
    })
    .catch(error => {
        app.handleAjaxError(error);
        app.hideLoading();
    });
}

// Show edit modal
function showEditModal(budget) {
    const modal = document.getElementById('editBudgetModal');
    if (!modal) return;
    
    document.getElementById('editBudgetId').value = budget.id;
    document.getElementById('editCategory').value = budget.category;
    document.getElementById('editAmount').value = budget.amount;
    
    modal.style.display = 'block';
}

// Close edit modal
function closeEditModal() {
    const modal = document.getElementById('editBudgetModal');
    if (modal) {
        modal.style.display = 'none';
    }
} 
function selectAllAssets() {
    const selectAllCheckbox = document.getElementById('selectAllAssets');
    document.querySelectorAll('.asset-checkbox').forEach(cb => {
        cb.checked = selectAllCheckbox.checked;
    });
    updateSelectedAssets();
}

async function bulkDeleteAssets() {
    if (selectedAssets.length === 0) {
        showAlert('Pilih asset untuk dihapus', 'warning');
        return;
    }
    
    if (!confirm(`Hapus ${selectedAssets.length} asset terpilih?`)) return;
    
    try {
        const response = await fetch(`${API_BASE}/admin/assets/bulk`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('adminToken')}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ asset_ids: selectedAssets })
        });
        
        if (!response.ok) throw new Error('Gagal menghapus asset');
        
        showAlert(`${selectedAssets.length} asset dihapus`, 'success');
        selectedAssets = [];
        loadAssets();
    } catch (error) {
        console.error('Error:', error);
        showAlert('Gagal menghapus asset', 'danger');
    }
}

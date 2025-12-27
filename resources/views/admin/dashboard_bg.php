                <!-- Backgrounds Tab -->
                <div class="tab-pane fade" id="backgrounds">
                    <div class="admin-content p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h3 class="mb-1"><i class="fas fa-image me-2"></i>Background Management</h3>
                                <p class="text-muted mb-0">Upload and manage background images</p>
                            </div>
                            <button class="btn btn-primary" onclick="uploadBackground()">
                                <i class="fas fa-plus me-1"></i>Add Background
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="backgroundsTable">
                                <thead>
                                    <tr>
                                        <th>Preview</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Size</th>
                                        <th>Uploaded By</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td colspan="7" class="text-center">Loading backgrounds...</td></tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <input type="file" id="backgroundFileInput" accept=".jpg,.jpeg,.png" style="display: none;">
                    </div>
                </div>

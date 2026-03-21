<?php include('approval_count.php'); ?>
<?php include('reject_count.php'); ?>
<?php include('reorderpoint_count.php'); ?>

                <?php if (in_array($type, ['Admin', 'Head of Purchase', 'Purchase', 'Sale'])): ?>
                <div class="col-6 col-md-3">
                    <a href ="index.php" class="btn btn-outline-primary w-100
                    <?= ($current_page == 'index.php') ? 'active' : '' ?>">
                    📦 รายการสินค้าทั้งหมด</a>
                </div>
                <?php endif; ?>

                <?php if (in_array($type, ['Admin', 'Head of Purchase', 'Purchase'])): ?>
                <div class="col-6 col-md-3">
                    <a href ="create_gr.php" class="btn btn-outline-primary w-100
                    <?= ($current_page == 'create_gr.php') ? 'active' : '' ?>">
                    🚚 รับสินค้าเข้า</a>
                </div>
                <?php endif; ?>

                <?php if (in_array($type, ['Admin', 'Head of Purchase'])): ?>
                <div class="col-6 col-md-3">
                    <a href="approval_requests.php"
                    class="btn btn-outline-primary w-100 position-relative
                    <?= ($current_page == 'approval_requests.php') ? 'active' : '' ?>">
                        📝 รายการคำขออนุมัติ

                        <?php if ($total_approval_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $total_approval_count ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
                <?php endif; ?>
        
                <?php if (in_array($type, ['Admin', 'Head of Purchase', 'Purchase'])): ?>
                <div class="col-6 col-md-3">
                    <a href ="create_gi.php" class="btn btn-outline-primary w-100
                    <?= ($current_page == 'create_gi.php') ? 'active' : '' ?>">
                    📤 เบิกสินค้าออก</a>
                </div>
                <?php endif; ?>
                
                <?php if (in_array($type, ['Admin'])): ?>
                <div class="col-6 col-md-3">
                    <a href ="manage_users.php" class="btn btn-outline-primary w-100
                    <?= ($current_page == 'manage_users.php') ? 'active' : '' ?>">
                    👤 จัดการผู้ใช้</a>
                </div>
                <?php endif; ?>

                <?php if (in_array($type, ['Admin', 'Head of Purchase', 'Purchase'])): ?>
                <div class="col-6 col-md-3">
                    <a href ="manage_products.php" class="btn btn-outline-primary w-100
                    <?= ($current_page == 'manage_products.php') ? 'active' : '' ?>
                    ">
                    🛠 จัดการสินค้า</a>
                </div>
                <?php endif; ?>
                
                <?php if (in_array($type, ['Admin', 'Head of Purchase', 'Purchase', 'Sale'])): ?>
                <div class="col-6 col-md-3">
                    <a href ="transaction.php" class="btn btn-outline-primary w-100
                    <?= ($current_page == 'transaction.php') ? 'active' : '' ?>">
                    📜 ประวัติการเคลื่อนไหว</a>
                </div>
                <?php endif; ?>

                <?php if (in_array($type, ['Admin', 'Head of Purchase', 'Purchase'])): ?>
                <div class="col-6 col-md-3">
                    <a href ="approval_status.php" 
                    class="btn btn-outline-primary w-100 position-relative
                    <?= ($current_page == 'approval_status.php') ? 'active' : '' ?>">
                    ⌛ สถานะรายการขออนุมัติ

                        <?php if ($total_approval_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $total_approval_count ?>
                            </span>
                        <?php endif; ?>

                    </a>
                </div>
                <?php endif; ?>

                <?php if (in_array($type, ['Admin', 'Head of Purchase', 'Purchase'])): ?>
                <div class="col-6 col-md-3">
                    <a href ="reject_list.php" class="btn btn-outline-primary w-100
                    <?= ($current_page == 'reject_list.php') ? 'active' : '' ?>">
                    ❌ รายการที่ไม่ได้รับการอนุมัติ
                        <?php if ($total_reject_count > 0): ?>
                                <span class="badge bg-danger ms-2"><?= $total_reject_count ?></span>
                        <?php endif; ?>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (in_array($type, ['Admin', 'Head of Purchase', 'Purchase'])): ?>    
                <div class="col-6 col-md-3">
                    <a href ="approve_list.php" class="btn btn-outline-primary w-100
                    <?= ($current_page == 'approve_list.php') ? 'active' : '' ?>">
                    ✅ รายการที่ได้รับการอนุมัติแล้ว</a>
                </div>
                <?php endif; ?>

                <?php if (in_array($type, ['Admin', 'Head of Purchase', 'Purchase'])): ?>
                <div class="col-6 col-md-3">
                    <a href ="product_reorder.php" class="btn btn-outline-primary w-100 position-relative
                    <?= ($current_page == 'product_reorder.php') ? 'active' : '' ?>"> 
                    ⚠️ รายการสินค้าถึงจุดสั่งซื้อ
                        <?php if ($total_reorder_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $total_reorder_count ?></span>
                        <?php endif; ?>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (in_array($type, ['Admin'])): ?>
                <div class="col-6 col-md-3">
                    <a href ="report.php" class="btn btn-outline-primary w-100
                    <?= ($current_page == 'report.php') ? 'active' : '' ?>"> 
                    📊 รายงานผล</a>
                </div>
                <?php endif; ?>
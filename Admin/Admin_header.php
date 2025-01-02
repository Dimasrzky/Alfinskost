<header class="navbar navbar-dark bg-dark navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="Admin_Dashboard.php">
            <img src="../Image/Logo Alfins Kost.png" alt="Logo" width="45" height="45" class="d-inline-block me-2">
            <span>Alfins Kost - Admin</span>
        </a>
        
        <div class="ms-auto">
            <a href="Admin_logout.php" class="btn btn-danger rounded-pill px-4">
                Logout <i class="bi bi-box-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</header>

<style>
    .navbar {
        padding: 15px 30px;  /* Menambah padding atas-bawah dan kiri-kanan */
    }
    
    .navbar-brand {
        font-size: 1.3rem;  /* Memperbesar ukuran font */
        padding: 8px 0;     /* Menambah padding atas-bawah */
    }
    
    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
        transition: all 0.3s ease;
        padding: 8px 25px;  /* Menyesuaikan padding button */
    }
    
    .btn-danger:hover {
        background-color: #bb2d3b;
        border-color: #bb2d3b;
        transform: translateY(-2px);
    }

    .container-fluid {
        padding: 0 25px;   /* Menambah padding container */
    }
</style>
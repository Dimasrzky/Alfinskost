<?php
if (session_status() === PHP_SESSION_NONE) {
   session_start();
}
?>

<!DOCTYPE html>
<html>
<head>
   <style>
       .header-container {
           background-color: #333;
           padding: 15px 30px;
           color: white;
           display: flex;
           justify-content: space-between;
           align-items: center;
       }

       .header-logo {
           display: flex;
           align-items: center;
           gap: 10px;
       }

       .header-logo img {
           width: 30px;
           height: 30px;
       }

       .header-logo h1 {
           margin: 0;
           font-size: 20px;
           color: white;
       }

       nav ul {
           list-style: none;
           display: flex;
           margin: 0;
           padding: 0;
           gap: 20px;
       }

       nav ul li a {
           color: white;
           text-decoration: none;
           font-size: 14px;
           transition: color 0.3s;
       }

       nav ul li a:hover {
           color: #ddd;
       }

       .logout-btn {
           background-color: #dc3545;
           color: white;
           padding: 8px 20px;
           border-radius: 20px;
           text-decoration: none;
           transition: background-color 0.3s;
       }

       .logout-btn:hover {
           background-color: #bb2d3b;
           color: white;
       }
   </style>
</head>
<body>
   <header>
       <div class="header-container">
           <div class="header-logo">
               <img src="../Image/Logo Alfins Kost.png" alt="Logo" class="site-logo">
               <h1>Alfins Kost</h1>
           </div>
           <nav>
               <ul>
                   <li><a href="Dashboard.php#beranda">Beranda</a></li>
                   <li><a href="Dashboard.php#lokasi">Lokasi</a></li>
                   <li><a href="Dashboard.php#tentang">Tentang</a></li>
                   <li><a href="Rooms.php">Kamar</a></li>
                   <li><a href="Dashboard.php#ulasan">Ulasan</a></li>
                   <li><a href="Logout.php" class="logout-btn">Logout</a></li>
               </ul>
           </nav>
       </div>
   </header>
</body>
</html>
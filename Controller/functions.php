<?php
require_once '../Config/db_connect.php';

function registerUser($data, $file = null) {
    global $pdo;
    
    try {
        // Check if NIK or email exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE nik = ? OR email = ?");
        $stmt->execute([$data['nik'], $data['email']]);
        
        if ($stmt->rowCount() > 0) {
            return [
                'status' => 'error',
                'message' => 'NIK atau email sudah terdaftar'
            ];
        }
        
        // Handle profile photo
        $profile_photo = null;
        if ($file && $file['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $file['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                // Perbaiki path untuk folder upload
                $base_dir = dirname(dirname(__FILE__)); // Naik satu level dari Controller
                $upload_dir = $base_dir . '/Uploads/';
                
                // Buat folder jika belum ada
                if (!file_exists($upload_dir)) {
                    if (!mkdir($upload_dir, 0777, true)) {
                        throw new Exception('Gagal membuat direktori upload');
                    }
                }
                
                // Generate nama file unik
                $profile_photo = 'Uploads/' . uniqid() . '.' . $ext;
                $upload_path = $base_dir . '/' . $profile_photo;
                
                // Upload file
                if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                    throw new Exception('Gagal mengupload file');
                }
            }
        }
        
        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (nik, full_name, email, password, phone_number, gender, address, emergency_contact, occupation, profile_photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $data['nik'],
            $data['full_name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['phone_number'],
            $data['gender'],
            $data['address'],
            $data['emergency_contact'],
            $data['occupation'],
            $profile_photo
        ]);
        
        return [
            'status' => 'success',
            'message' => 'Registrasi berhasil!'
        ];
        
    } catch(PDOException $e) {
        return [
            'status' => 'error',
            'message' => 'Registrasi gagal: ' . $e->getMessage()
        ];
    }
}

function loginUser($email, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['profile_photo'] = $user['profile_photo'];
            return [
                'status' => 'success',
                'message' => 'Login berhasil'
            ];
        }
        
        return [
            'status' => 'error',
            'message' => 'Email atau password salah'
        ];
        
    } catch(PDOException $e) {
        return [
            'status' => 'error',
            'message' => 'Login gagal: ' . $e->getMessage()
        ];
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function logout() {
    session_destroy();
}
?>
<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : 'medicines';
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$input = json_decode(file_get_contents('php://input'), true);

$userRole = isset($_SERVER['HTTP_X_USER_ROLE']) ? $_SERVER['HTTP_X_USER_ROLE'] : 'personnel';

try {
    // ---------------------------------------------
    // AUTHENTICATION (LOGIN)
    // ---------------------------------------------
    if ($action === 'login') {
        if ($method !== 'POST') { http_response_code(405); echo json_encode(["status" => "error", "message" => "Sadece POST metodu desteklenir."]); exit(); }
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            http_response_code(400); echo json_encode(["status" => "error", "message" => "Kullanıcı adı ve şifre gereklidir."]); exit;
        }
        $stmt = $db->prepare("SELECT id, name, username, role FROM users WHERE username = :username AND password = :password");
        $stmt->execute(['username' => $username, 'password' => md5($password)]);
        $user = $stmt->fetch();
        if ($user) {
            $token = base64_encode(json_encode(["id" => $user['id'], "role" => $user['role'], "time" => time()]));
            echo json_encode(["status" => "success", "data" => ["token" => $token, "name" => $user['name'], "role" => $user['role']]]);
        } else {
            http_response_code(401); echo json_encode(["status" => "error", "message" => "Hatalı giriş."]);
        }
        exit();
    }

    // ---------------------------------------------
    // SETTINGS (CHANGE PASSWORD)
    // ---------------------------------------------
    if ($action === 'settings') {
        if ($method !== 'PUT') { http_response_code(405); echo json_encode(["status" => "error", "message" => "Sadece PUT desteklenir."]); exit(); }
        if (!empty($input['user_id']) && !empty($input['current_password']) && !empty($input['new_password'])) {
            $user_id = intval($input['user_id']);
            $stmt = $db->prepare("SELECT id FROM users WHERE id = :id AND password = :password");
            $stmt->execute(['id' => $user_id, 'password' => md5($input['current_password'])]);
            if ($stmt->fetch()) {
                $update = $db->prepare("UPDATE users SET password = :new_password WHERE id = :id");
                $update->execute(['new_password' => md5($input['new_password']), 'id' => $user_id]);
                echo json_encode(["status" => "success", "message" => "Şifre güncellendi."]);
            } else {
                http_response_code(400); echo json_encode(["status" => "error", "message" => "Mevcut şifre yanlış."]);
            }
        } else {
            http_response_code(400); echo json_encode(["status" => "error", "message" => "Eksik veri."]);
        }
        exit();
    }

    // ---------------------------------------------
    // MEDICINES (İLAÇLAR)
    // ---------------------------------------------
    if ($action === 'medicines') {
        if (in_array($method, ['POST', 'PUT', 'DELETE']) && $userRole !== 'manager') {
            http_response_code(403); echo json_encode(["status" => "error", "message" => "Yetki reddedildi!"]); exit();
        }

        if ($method === 'GET') {
            if ($id) {
                $stmt = $db->prepare("SELECT * FROM medicines WHERE id = :id"); $stmt->execute(['id' => $id]);
                $med = $stmt->fetch();
                if ($med) echo json_encode(["status" => "success", "data" => $med]);
                else { http_response_code(404); echo json_encode(["status" => "error", "message" => "Bulunamadı."]); }
            } else {
                $stmt = $db->query("SELECT * FROM medicines ORDER BY id DESC");
                echo json_encode(["status" => "success", "data" => $stmt->fetchAll()]);
            }
        } elseif ($method === 'POST') {
            $stmt = $db->prepare("INSERT INTO medicines (barcode, name, category, price, stock, expiry_date) VALUES (:barcode, :name, :category, :price, :stock, :expiry_date)");
            if($stmt->execute([
                'barcode' => $input['barcode'], 'name' => $input['name'], 'category' => $input['category'] ?? 'Genel',
                'price' => $input['price'], 'stock' => $input['stock'], 'expiry_date' => $input['expiry_date']
            ])) {
                http_response_code(201); echo json_encode(["status" => "success", "message" => "İlaç eklendi."]);
            }
        } elseif ($method === 'PUT') {
            $stmt = $db->prepare("UPDATE medicines SET barcode = :barcode, name = :name, category = :category, price = :price, stock = :stock, expiry_date = :expiry_date WHERE id = :id");
            $stmt->execute([
                'id' => $id, 'barcode' => $input['barcode'], 'name' => $input['name'], 'category' => $input['category'] ?? 'Genel',
                'price' => $input['price'], 'stock' => $input['stock'], 'expiry_date' => $input['expiry_date']
            ]);
            echo json_encode(["status" => "success", "message" => "İlaç güncellendi."]);
        } elseif ($method === 'DELETE') {
            $stmt = $db->prepare("DELETE FROM medicines WHERE id = :id"); $stmt->execute(['id' => $id]);
            echo json_encode(["status" => "success", "message" => "İlaç silindi."]);
        }
        exit();
    }

    // ---------------------------------------------
    // PATIENTS (HASTALAR)
    // ---------------------------------------------
    if ($action === 'patients') {
        if ($method === 'DELETE' && $userRole !== 'manager') {
            http_response_code(403); echo json_encode(["status" => "error", "message" => "Yetki reddedildi!"]); exit();
        }

        if ($method === 'GET') {
            if ($id) {
                $stmt = $db->prepare("SELECT * FROM patients WHERE id = :id"); $stmt->execute(['id' => $id]);
                $pat = $stmt->fetch();
                if ($pat) echo json_encode(["status" => "success", "data" => $pat]);
                else { http_response_code(404); echo json_encode(["status" => "error", "message" => "Bulunamadı."]); }
            } else {
                $stmt = $db->query("SELECT * FROM patients ORDER BY id DESC");
                echo json_encode(["status" => "success", "data" => $stmt->fetchAll()]);
            }
        } elseif ($method === 'POST') {
            $stmt = $db->prepare("INSERT INTO patients (tc_no, name, phone, blood_group) VALUES (:tc_no, :name, :phone, :blood_group)");
            $stmt->execute(['tc_no' => $input['tc_no'], 'name' => $input['name'], 'phone' => $input['phone'] ?? null, 'blood_group' => $input['blood_group'] ?? null]);
            http_response_code(201); echo json_encode(["status" => "success", "message" => "Hasta eklendi."]);
        } elseif ($method === 'PUT') {
            $stmt = $db->prepare("UPDATE patients SET tc_no = :tc_no, name = :name, phone = :phone, blood_group = :blood_group WHERE id = :id");
            $stmt->execute(['id' => $id, 'tc_no' => $input['tc_no'], 'name' => $input['name'], 'phone' => $input['phone'] ?? null, 'blood_group' => $input['blood_group'] ?? null]);
            echo json_encode(["status" => "success", "message" => "Hasta güncellendi."]);
        } elseif ($method === 'DELETE') {
            $stmt = $db->prepare("DELETE FROM patients WHERE id = :id"); $stmt->execute(['id' => $id]);
            echo json_encode(["status" => "success", "message" => "Hasta silindi."]);
        }
        exit();
    }

    // ---------------------------------------------
    // SALES (SATIŞLAR)
    // ---------------------------------------------
    if ($action === 'sales') {
        if ($method === 'GET') {
            $query = "SELECT s.id, s.quantity, s.total_price, s.sale_date, m.name as medicine_name, p.name as patient_name 
                      FROM sales s JOIN medicines m ON s.medicine_id = m.id JOIN patients p ON s.patient_id = p.id ORDER BY s.id DESC";
            $stmt = $db->query($query);
            echo json_encode(["status" => "success", "data" => $stmt->fetchAll()]);
        } elseif ($method === 'POST') {
            $med_id = $input['medicine_id']; $pat_id = $input['patient_id']; $qty = intval($input['quantity']);
            $stmt = $db->prepare("SELECT price, stock FROM medicines WHERE id = :id"); $stmt->execute(['id' => $med_id]);
            $medicine = $stmt->fetch();
            if (!$medicine) { http_response_code(404); echo json_encode(["status" => "error", "message" => "İlaç bulunamadı."]); exit; }
            if ($medicine['stock'] < $qty) { http_response_code(400); echo json_encode(["status" => "error", "message" => "Yetersiz stok!"]); exit; }
            
            $total_price = $qty * $medicine['price'];
            $db->beginTransaction();
            $stmtSale = $db->prepare("INSERT INTO sales (medicine_id, patient_id, quantity, total_price) VALUES (:m_id, :p_id, :qty, :total)");
            $stmtSale->execute(['m_id' => $med_id, 'p_id' => $pat_id, 'qty' => $qty, 'total' => $total_price]);
            $stmtUpdate = $db->prepare("UPDATE medicines SET stock = stock - :qty WHERE id = :id");
            $stmtUpdate->execute(['qty' => $qty, 'id' => $med_id]);
            $db->commit();
            http_response_code(201); echo json_encode(["status" => "success", "message" => "Satış tamamlandı."]);
        }
        exit();
    }

} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) { $db->rollBack(); }
    http_response_code(500); echo json_encode(["status" => "error", "message" => "Veritabanı hatası: " . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500); echo json_encode(["status" => "error", "message" => "Hata: " . $e->getMessage()]);
}
?>


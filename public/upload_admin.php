<?php

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($username) || empty($password)) {
        $message = 'اسم المستخدم وكلمة المرور مطلوبان!';
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل!';
        $messageType = 'error';
    } else {
        try {
            $db = Database::getInstance()->getConnection();

            $stmt = $db->prepare("SELECT id FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetch()) {
                $message = 'اسم المستخدم موجود بالفعل!';
                $messageType = 'error';
            } else {

                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $db->prepare("
                    INSERT INTO admins (username, password, email, created_at) 
                    VALUES (?, ?, ?, NOW())
                ");
                
                if ($stmt->execute([$username, $hashedPassword, $email])) {
                    $message = 'تم إضافة المستخدم بنجاح!';
                    $messageType = 'success';

                    $newId = $db->lastInsertId();
                    $message .= "<br><strong>ID:</strong> $newId<br>";
                    $message .= "<strong>Username:</strong> " . htmlspecialchars($username) . "<br>";
                    $message .= "<strong>Email:</strong> " . htmlspecialchars($email);
                } else {
                    $message = 'فشل إضافة المستخدم!';
                    $messageType = 'error';
                }
            }
        } catch (Exception $e) {
            $message = 'خطأ: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

$existingUsers = [];
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT id, username, email, created_at, last_login FROM admins ORDER BY id");
    $existingUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $existingUsers = [];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رفع بيانات مسؤول جديد</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .warning {
            background: #ff6b6b;
            color: white;
            padding: 15px;
            text-align: center;
            font-weight: bold;
        }
        
        .content {
            padding: 30px;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .users-table {
            margin-top: 30px;
        }
        
        .users-table h2 {
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        table th,
        table td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #ddd;
        }
        
        table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        table tr:hover {
            background: #f8f9fa;
        }
        
        .small-text {
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 رفع بيانات مسؤول جديد</h1>
            <p>إضافة اسم مستخدم وكلمة مرور جديدة</p>
        </div>
        
        <div class="warning">
            ⚠️ تحذير: احذف هذا الملف فوراً بعد الاستخدام لأسباب أمنية!
        </div>
        
        <div class="content">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">اسم المستخدم *</label>
                    <input type="text" id="username" name="username" required 
                           placeholder="أدخل اسم المستخدم" autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label for="password">كلمة المرور * (6 أحرف على الأقل)</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="أدخل كلمة المرور" minlength="6" autocomplete="new-password">
                </div>
                
                <div class="form-group">
                    <label for="email">البريد الإلكتروني (اختياري)</label>
                    <input type="email" id="email" name="email" 
                           placeholder="admin@example.com" autocomplete="off">
                </div>
                
                <button type="submit" class="btn">➕ إضافة المستخدم</button>
            </form>
            
            <?php if (!empty($existingUsers)): ?>
                <div class="users-table">
                    <h2>📋 المستخدمون الحاليون</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>اسم المستخدم</th>
                                <th>البريد الإلكتروني</th>
                                <th>تاريخ الإنشاء</th>
                                <th>آخر دخول</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($existingUsers as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['email'] ?: '-'); ?></td>
                                    <td class="small-text"><?php echo htmlspecialchars($user['created_at']); ?></td>
                                    <td class="small-text"><?php echo htmlspecialchars($user['last_login'] ?: 'لم يسجل دخول'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
require_once 'config/config.php';
$page_title='Đăng nhập';
if(isLoggedIn()){redirect(SITE_URL.'/');}
$error='';$success='';
if($_SERVER['REQUEST_METHOD']=='POST'&&isset($_POST['login'])){
$email=trim($_POST['email']);
$password=$_POST['password'];
if(empty($email)||empty($password)){$error='Vui lòng nhập đầy đủ thông tin!';}else{
$sql="SELECT * FROM nguoi_dung WHERE email=?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("s",$email);
$stmt->execute();
$result=$stmt->get_result();
if($result->num_rows>0){
$user=$result->fetch_assoc();
if(md5($password)==$user['mat_khau']){
$_SESSION['user_id']=$user['id'];
$_SESSION['ho_ten']=$user['ho_ten'];
$_SESSION['email']=$user['email'];
$_SESSION['vai_tro']=$user['vai_tro'];
if($user['vai_tro']=='quantri'){redirect(SITE_URL.'/admin/');}else{redirect(SITE_URL.'/');}
}else{$error='Mật khẩu không chính xác!';}
}else{$error='Email không tồn tại!';}
}}
if($_SERVER['REQUEST_METHOD']=='POST'&&isset($_POST['register'])){
$ho_ten=trim($_POST['ho_ten']);
$email=trim($_POST['email_register']);
$password=$_POST['password_register'];
$confirm_password=$_POST['confirm_password'];
if(empty($ho_ten)||empty($email)||empty($password)||empty($confirm_password)){$error='Vui lòng nhập đầy đủ thông tin!';}
elseif($password!=$confirm_password){$error='Mật khẩu xác nhận không khớp!';}
elseif(strlen($password)<6){$error='Mật khẩu phải có ít nhất 6 ký tự!';}else{
$sql="SELECT id FROM nguoi_dung WHERE email=?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("s",$email);
$stmt->execute();
$result=$stmt->get_result();
if($result->num_rows>0){$error='Email đã được sử dụng!';}else{
$password_hash=md5($password);
$sql="INSERT INTO nguoi_dung(ho_ten,email,mat_khau,vai_tro)VALUES(?,?,?,'khachhang')";
$stmt=$conn->prepare($sql);
$stmt->bind_param("sss",$ho_ten,$email,$password_hash);
if($stmt->execute()){$success='Đăng ký thành công! Vui lòng đăng nhập.';}else{$error='Có lỗi xảy ra, vui lòng thử lại!';}}}}

?>
<style>.auth-page{padding:60px 0;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:calc(100vh - 200px);}.auth-container{max-width:450px;margin:0 auto;background:white;border-radius:20px;padding:40px;box-shadow:0 20px 60px rgba(0,0,0,0.3);}.auth-tabs{display:flex;margin-bottom:30px;border-bottom:2px solid #eee;}.auth-tab{flex:1;padding:15px;text-align:center;cursor:pointer;font-weight:600;color:#666;border-bottom:3px solid transparent;margin-bottom:-2px;transition:all 0.3s;}.auth-tab.active{color:var(--primary-color);border-bottom-color:var(--primary-color);}.auth-form{display:none;}.auth-form.active{display:block;}.form-group{margin-bottom:20px;}.form-group label{display:block;margin-bottom:8px;font-weight:600;color:#333;}.form-group input{width:100%;padding:12px 15px;border:2px solid #eee;border-radius:8px;font-size:14px;transition:all 0.3s;}.form-group input:focus{border-color:var(--primary-color);outline:none;}.btn-submit{width:100%;padding:15px;background:var(--primary-color);color:white;border:none;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer;transition:all 0.3s;}.btn-submit:hover{background:var(--primary-dark);transform:translateY(-2px);}.divider{text-align:center;margin:30px 0;position:relative;}.divider::before{content:'';position:absolute;top:50%;left:0;right:0;height:1px;background:#ddd;}.divider span{background:white;padding:0 20px;position:relative;color:#999;font-size:14px;}.social-login{display:flex;gap:15px;}.social-btn{flex:1;padding:12px;border-radius:8px;border:none;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.3s;}.facebook-btn{background:#1877f2;color:white;}.google-btn{background:#fff;color:#333;border:2px solid #ddd;}.social-btn:hover{transform:translateY(-2px);box-shadow:0 5px 15px rgba(0,0,0,0.2);}.alert{padding:12px 15px;border-radius:8px;margin-bottom:20px;font-size:14px;}.alert-error{background:#fee;color:#c33;border:1px solid #fcc;}.alert-success{background:#efe;color:#3c3;border:1px solid #cfc;}</style>
<div class=""><div class="container"><div class="auth-container">
<div class="auth-tabs">
<div> Đăng nhập vào LQT store </div>
<div class="auth-tab active" onclick="switchTab('login')">Đăng nhập</div>
<div class="auth-tab" onclick="switchTab('register')">Đăng ký</div>
</div>
<?php if($error):?><div class="alert alert-error"><?php echo $error;?></div><?php endif;?>
<?php if($success):?><div class="alert alert-success"><?php echo $success;?></div><?php endif;?>
<form class="auth-form active" id="login-form" method="POST">
<div class="form-group"><label>Email</label><input type="email" name="email" required placeholder="Nhập email của bạn"></div>
<div class="form-group"><label>Mật khẩu</label><input type="password" name="password" required placeholder="Nhập mật khẩu"></div>
<button type="submit" name="login" class="btn-submit">Đăng nhập</button>
<div class="divider"><span>Hoặc đăng nhập với</span></div>
<div class="social-login">
<a href="<?php echo SITE_URL;?>/oauth/facebook-login.php" class="social-btn facebook-btn"><i class="fab fa-facebook"></i> Facebook</a>
<a href="<?php echo SITE_URL;?>/oauth/google-login.php" class="social-btn google-btn"><i class="fab fa-google"></i> Google</a>
</div>
<p style="text-align:center;margin-top:20px;color:#666;font-size:13px;"><strong>Tài khoản demo:</strong><br>Admin: admin@gmail.com / admin123<br>Khách: vana@gmail.com / 123456</p>
</form>
<form class="auth-form" id="register-form" method="POST">
<div class="form-group"><label>Họ và tên</label><input type="text" name="ho_ten" required placeholder="Nhập họ và tên"></div>
<div class="form-group"><label>Email</label><input type="email" name="email_register" required placeholder="Nhập email của bạn"></div>
<div class="form-group"><label>Mật khẩu</label><input type="password" name="password_register" required placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)"></div>
<div class="form-group"><label>Xác nhận mật khẩu</label><input type="password" name="confirm_password" required placeholder="Nhập lại mật khẩu"></div>
<button type="submit" name="register" class="btn-submit">Đăng ký</button>
<div class="divider"><span>Hoặc đăng ký với</span></div>
<div class="social-login">
<a href="<?php echo SITE_URL;?>/oauth/facebook-login.php" class="social-btn facebook-btn"><i class="fab fa-facebook"></i> Facebook</a>
<a href="<?php echo SITE_URL;?>/oauth/google-login.php" class="social-btn google-btn"><i class="fab fa-google"></i> Google</a>
</div>
</form>
</div></div></div>
<script>function switchTab(tab){document.querySelectorAll('.auth-tab').forEach(t=>t.classList.remove('active'));document.querySelectorAll('.auth-form').forEach(f=>f.classList.remove('active'));if(tab==='login'){document.querySelectorAll('.auth-tab')[0].classList.add('active');document.getElementById('login-form').classList.add('active');}else{document.querySelectorAll('.auth-tab')[1].classList.add('active');document.getElementById('register-form').classList.add('active');}}</script>


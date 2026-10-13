<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    redirect('/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'blocked') {
                $error = 'Your account has been blocked by the administrator.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                redirect('/index.php');
            }
        } else {
            $error = 'Invalid email or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
require_once 'includes/header.php';
?>
<div class="auth-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4 col-sm-8">
                <div class="card auth-card text-center p-4 bg-white">
                    <h3 class="mb-4 text-dark font-weight-bold">GarageLogin</h3>
                    <?php if ($error): ?>
                        <div class="alert alert-danger px-3 py-2"><?= $error ?></div>
                    <?php endif; ?>
                    <form method="POST" action="login.php" class="text-start">
                        <div class="form-outline mb-4" data-mdb-input-init>
                            <input type="email" id="email" name="email" class="form-control form-control-lg" required />
                            <label class="form-label" for="email">Email address</label>
                        </div>
                        <div class="form-outline mb-4" data-mdb-input-init>
                            <input type="password" id="password" name="password" class="form-control form-control-lg" required />
                            <label class="form-label" for="password">Password</label>
                        </div>
                        <button type="submit" class="btn btn-garage-primary btn-lg btn-block mb-4">Sign In</button>
                    </form>
                    <p class="mb-0 text-muted">Don't have an account? <a href="signup.php" class="text-primary fw-bold">Sign up</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>

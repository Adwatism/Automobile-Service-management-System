<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    redirect('/index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name && $email && $password && $confirm_password) {
        if (!preg_match('/@(gmail\.com|yahoo\.com|outlook\.com|hotmail\.com|live\.com)$/i', $email)) {
            $error = 'Please use a valid email address (e.g., @gmail.com, @yahoo.com).';
        } else if ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email is already registered.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
                if ($stmt->execute([$name, $email, $hashed_password])) {
                    $success = 'Account created successfully. You can now <a href="login.php" class="alert-link">Sign in</a>.';
                } else {
                    $error = 'Something went wrong. Please try again.';
                }
            }
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
            <div class="col-md-6 col-lg-5 col-sm-10">
                <div class="card auth-card text-center p-4 bg-white">
                    <h3 class="mb-3 text-dark font-weight-bold">Create Account</h3>
                    <?php if ($error): ?>
                        <div class="alert alert-danger px-3 py-2 text-start"><?= $error ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success px-3 py-2 text-start"><?= $success ?></div>
                    <?php endif; ?>
                    <form method="POST" action="signup.php" class="text-start">
                        <div class="form-outline mb-4" data-mdb-input-init>
                            <input type="text" id="name" name="name" class="form-control" required />
                            <label class="form-label" for="name">Full Name</label>
                        </div>
                        <div class="form-outline mb-4" data-mdb-input-init>
                            <input type="email" id="email" name="email" class="form-control" pattern=".*@(gmail\.com|yahoo\.com|outlook\.com|hotmail\.com|live\.com|GMAIL\.COM|YAHOO\.COM|OUTLOOK\.COM|HOTMAIL\.COM|LIVE\.COM)$" title="Please enter a valid email address ending in @gmail.com, @yahoo.com, etc." required />
                            <label class="form-label" for="email">Email address</label>
                        </div>
                        <div class="form-outline mb-4" data-mdb-input-init>
                            <input type="password" id="password" name="password" class="form-control" required />
                            <label class="form-label" for="password">Password</label>
                        </div>
                        <div class="form-outline mb-4" data-mdb-input-init>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required />
                            <label class="form-label" for="confirm_password">Confirm Password</label>
                        </div>
                        <button type="submit" class="btn btn-garage-primary btn-block mb-3">Sign Up</button>
                    </form>
                    <p class="mb-0 text-muted">Already have an account? <a href="login.php" class="text-primary fw-bold">Sign in</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>

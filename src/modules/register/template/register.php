<link rel="stylesheet" href="/modules/register/styles/register.css">

<div class="register-container">
    <form>
        <img 
            src="/common/images/logo.png" 
            alt="logo"
        >
        <input
            name="username"
            placeholder="Username"
        >
        <a id="username-error"></a>
        <input
            name="email"
            placeholder="email"
        >
        <a id="email-error"></a>
        <input
            name="password"
            type="password"
            placeholder="Password"
        >
        <a id="password-error"></a>
        <input
            name="password2"
            type="password"
            placeholder="Retype password"
        >
        <a id="repassword-error"></a>
        <button>
            Crear
        </button>
    </form>
</div>
<script src="/modules/register/script/register.js"></script>
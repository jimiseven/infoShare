<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h1 class="h4 mb-3">Iniciar sesion</h1>
        <form method="POST" action="index.php?r=login">
          <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Contrasena</label>
            <input class="form-control" type="password" name="password" required>
          </div>
          <button class="btn btn-dark w-100" type="submit">Entrar</button>
        </form>
      </div>
    </div>
  </div>
</div>

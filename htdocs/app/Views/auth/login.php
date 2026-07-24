<?php echo $this->extend(config('Auth')->views['layout']); ?>

<?php echo $this->section('title'); ?><?php echo lang('Auth.login'); ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('content'); ?>

<div class="container d-flex justify-content-center p-5">
    <div class="card col-12 col-md-5 shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-5"><?php echo lang('Auth.login'); ?></h5>

            <?php if (null !== session('error')) { ?>
            <div class="alert alert-danger" role="alert"><?php echo esc(session('error')); ?></div>
            <?php } elseif (null !== session('errors')) { ?>
            <div class="alert alert-danger" role="alert">
                <?php if (is_array(session('errors'))) { ?>
                <?php foreach (session('errors') as $error) { ?>
                <?php echo esc($error); ?><br>
                <?php } ?>
                <?php } else { ?>
                <?php echo esc(session('errors')); ?>
                <?php } ?>
            </div>
            <?php } ?>

            <form action="<?php echo url_to('login'); ?>" method="post">
                <?php echo csrf_field(); ?>

                <div class="form-floating mb-3">
                    <label for="floatingEmailInput"><?php echo lang('Auth.email'); ?></label>
                    <input type="email" class="form-control" id="floatingEmailInput" name="email" inputmode="email"
                        autocomplete="email" placeholder="<?php echo lang('Auth.email'); ?>"
                        value="<?php echo old('email'); ?>" required>
                </div>

                <div class="form-floating mb-3 password-wrapper">
                    <label for="floatingPasswordInput"><?php echo lang('Auth.password'); ?></label>
                    <input type="password" class="form-control" id="floatingPasswordInput" name="password"
                        inputmode="text" autocomplete="current-password"
                        placeholder="<?php echo lang('Auth.password'); ?>" required>
                    <button type="button" class="password-toggle" aria-label="Afficher le mot de passe">👁️</button>
                </div>

                <?php if (setting('Auth.sessionConfig')['allowRemembering']) { ?>
                <div class="form-check">
                    <label class="form-check-label">
                        <input type="checkbox" name="remember" class="form-check-input"
                            <?php if (old('remember', true)) {
                                echo 'checked';
                            } ?>>
                        <?php echo lang('Auth.rememberMe'); ?>
                    </label>
                </div>
                <?php } ?>

                <div class="d-grid col-12 col-md-8 mx-auto m-3">
                    <button type="submit" class="btn btn-primary btn-block"><?php echo lang('Auth.login'); ?></button>
                </div>

                <?php if (setting('Auth.allowRegistration')) { ?>
                <p class="text-center"><?php echo lang('Auth.needAccount'); ?> <a
                        href="<?php echo url_to('register'); ?>"><?php echo lang('Auth.register'); ?></a></p>
                <?php } ?>

            </form>
        </div>
    </div>
</div>

<?php echo $this->endSection(); ?>
<?php echo $this->extend(config('Auth')->views['layout']); ?>

<?php echo $this->section('title'); ?><?php echo lang('Auth.register'); ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('content'); ?>

<div class="container d-flex justify-content-center p-5">
    <div class="card col-12 col-md-5 shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-5"><?php echo lang('Auth.register'); ?></h5>

            <?php if (null !== session('error')) { ?>
            <div class="alert alert-danger" role="alert"><?php echo esc(session('error')); ?></div>
            <?php } elseif (null !== session('errors')) { ?>
            <div class="alert alert-danger" role="alert">
                <?php if (is_array(session('errors'))) { ?>
                <?php foreach (session('errors') as $error) { ?>
                <?php echo esc($error); ?>
                <br>
                <?php } ?>
                <?php } else { ?>
                <?php echo esc(session('errors')); ?>
                <?php } ?>
            </div>
            <?php } ?>

            <form action="<?php echo url_to('register'); ?>" method="post">
                <?php echo csrf_field(); ?>

                <!-- Email -->
                <div class="form-floating mb-2">
                    <label for="floatingEmailInput"><?php echo lang('Auth.email'); ?></label>
                    <input type="email" class="form-control" id="floatingEmailInput" name="email" inputmode="email"
                        autocomplete="email" placeholder="<?php echo lang('Auth.email'); ?>"
                        value="<?php echo old('email'); ?>" required>

                </div>

                <!-- Username -->
                <div class="form-floating mb-4">
                    <label for="floatingUsernameInput"><?php echo lang('Auth.username'); ?></label>
                    <input type="text" class="form-control" id="floatingUsernameInput" name="username" inputmode="text"
                        autocomplete="username" placeholder="<?php echo lang('Auth.username'); ?>"
                        value="<?php echo old('username'); ?>" required>

                </div>

                <!-- Password -->
                <div class="form-floating mb-2">
                    <label for="floatingPasswordInput"><?php echo lang('Auth.password'); ?></label>
                    <input type="password" class="form-control" id="floatingPasswordInput" name="password"
                        inputmode="text" autocomplete="new-password" placeholder="<?php echo lang('Auth.password'); ?>"
                        required>

                </div>

                <!-- Password (Again) -->
                <div class="form-floating mb-5">
                    <label for="floatingPasswordConfirmInput">Confirmer</label>
                    <input type="password" class="form-control" id="floatingPasswordConfirmInput"
                        name="password_confirm" inputmode="text" autocomplete="new-password" placeholder="Confirmer"
                        required>

                </div>

                <div class="d-grid col-12 col-md-8 mx-auto m-3">
                    <button type="submit"
                        class="btn btn-primary btn-block"><?php echo lang('Auth.register'); ?></button>
                </div>

                <p class="text-center" style="text-align: center;"><?php echo lang('Auth.haveAccount'); ?> <a
                        href="<?php echo url_to('login'); ?>"><?php echo lang('Auth.login'); ?></a></p>

            </form>
        </div>
    </div>
</div>

<?php echo $this->endSection(); ?>
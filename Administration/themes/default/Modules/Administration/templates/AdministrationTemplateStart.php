<?php
$_noSearchBar = true;
$_noMap = true;
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <?php include 'head.php' ?>
    <body>
        <!-- Header -->
        <?php include 'header.php' ?>

        <div class="row fullWidth resto-title">

        </div>
        <br/>
        <br/>
        <br/>
        <div class="row" style="text-align: center; padding-top: 25px">
            <ul class="small-block-grid-1 large-block-grid-2" >
                <li>
                    <h1>Administration</h1>
                    <p>
                        <?php echo $self->context->dictionary->translate('_a_start'); ?>
                    </p>
                </li>
                <li>
                    <ul class="small-block-grid-1 large-block-grid-1">
                        <li>

                            <a href="<?php echo $self->context->baseUrl . 'administration/users/'; ?>" class="button expand"><?php echo $self->context->dictionary->translate('_a_users_management'); ?></a>

                        </li>
                        <li>

                            <a href="<?php echo $self->context->baseUrl . 'administration/users/creation'; ?>" class="button expand"><?php echo $self->context->dictionary->translate('_a_user_creation'); ?></a>

                        </li>
                        <li>

                            <a href="<?php echo $self->context->baseUrl . 'administration/collections'; ?>" class="button expand"><?php echo $self->context->dictionary->translate('_a_collections_management'); ?></a>

                        </li>
                        <li>

                            <a href="<?php echo $self->context->baseUrl . 'administration/users/history'; ?>" class="button expand"><?php echo $self->context->dictionary->translate('_a_history'); ?></a>

                        </li>
                    </ul>
                </li>
            </ul>

        </div>
        <!-- Footer -->
        <?php include 'footer.php' ?>
    </body>
</html>

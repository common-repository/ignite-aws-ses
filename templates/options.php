<?php
global $settings;
$defaults = $settings->loadDefaults();
?>
<div class="container wrap">
    <h3>Ignite Online Better Email</h3>

    <form action="" method="post">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <label>Current Environment</label>
                <td><?= $settings->getEnvironment() ?></td>
                </th>
            </tr>
            <?php if ($settings->getEnvironment() == 'Production') : ?>
                <tr>
                    <th scope="row">
                        <label for="">AWS Access Key ID</label>
                    </th>
                    <td>
                        <input type="text" name="aws_access_key_id" class="regular-text"
                               value="<?= $defaults['aws_access_key_id'] ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="">AWS Secret Access Key</label>
                    </th>
                    <td>
                        <input type="text" name="aws_secret_access_key" class="regular-text"
                               value="<?= $defaults['aws_secret_access_key'] ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="">Region</label>
                    </th>
                    <td>
                        <select name="aws_region" id="">
                            <option value="">-- Select --</option>
                            <option value="eu-west-1" <?= $defaults['aws_region'] == 'eu-west-1' ? 'selected' : '' ?>>EU
                                (Ireland)
                            </option>
                            <option value="us-east-1" <?= $defaults['aws_region'] == 'us-east-1' ? 'selected' : '' ?>>US
                                East (N. Virginia)
                            </option>
                            <option value="us-west-2" <?= $defaults['aws_region'] == 'us-west-2' ? 'selected' : '' ?>>US
                                West (Oregon)
                            </option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="">Sending Address Name</label>
                    </th>
                    <td>
                        <input type="text" name="aws_address_name" value="<?= $defaults['aws_address_name'] ?>"
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="">Sending Address</label>
                    </th>
                    <td>
                        <input type="text" name="aws_address" value="<?= $defaults['aws_address'] ?>"
                               class="regular-text">
                    </td>
                </tr>
            <?php else : ?>
                <tr>
                    <th scope="row">
                        <label for="">Mailtrap User ID</label>
                    </th>
                    <td>
                        <input type="text" name="mailtrap_user" class="regular-text"
                               value="<?= $defaults['mailtrap_user'] ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="">Mailtrap Password</label>
                    </th>
                    <td>
                        <input type="text" name="mailtrap_password" class="regular-text"
                               value="<?= $defaults['mailtrap_password'] ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="">Sending Address Name</label>
                    </th>
                    <td>
                        <input type="text" name="aws_address_name" value="<?= $defaults['aws_address_name'] ?>"
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="">Email From</label>
                    </th>
                    <td>
                        <input type="text" class="regular-text" name="aws_address" value="<?= $defaults['aws_address'] ?>">
                    </td>
                </tr>
            <?php endif; ?>
            <?php wp_nonce_field('ignite-better-mail-save', 'ignite-better-mail'); ?>
            <tr>
                <td>
                    <?php submit_button('Save Settings'); ?>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
    <hr>
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="">Recipient Address</label>
            </th>
            <td>
                <input type="email" class="test_email regular-text">
            </td>
        </tr>
        <tr>
            <td>
                <input type="button" value="Send Test Email" class="ignite-test-email button-secondary"
                       data-url="<?= admin_url('admin-ajax.php') ?>">
            </td>
            <td>
                <span class="response"></span>
            </td>
        </tr>
    </table>
</div>
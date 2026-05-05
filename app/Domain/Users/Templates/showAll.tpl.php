<?php
defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$roles = $tpl->get('roles');
?>

<div class="pageheader">

    <div class="pageicon"><span class="fa <?php echo $tpl->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php echo $tpl->__('label.administration') ?></h5>
        <h1><?php echo $tpl->__('headlines.users'); ?></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $tpl->displayNotification() ?>

        <div class="row">
            <div class="col-md-6">
                <a href="<?= BASE_URL ?>/users/newUser" class="btn btn-primary userEditModal"><i class='fa fa-plus'></i> <?= $tpl->__('buttons.add_user') ?> </a>
            </div>
            <div class="col-md-6 align-right">

            </div>
        </div>

        <table class="table table-bordered" id="allUsersTable">
            <colgroup>
                <col class="con1">
                <col class="con0">
                <col class="con1">
                <col class="con0">
                <col class="con1">
                <col class="con0">
                <col class="con1">
                <col class="con0">
            </colgroup>
            <thead>
                <tr>
                    <th class='head1'><?php echo $tpl->__('label.name'); ?></th>
                    <th class='head0'><?php echo $tpl->__('label.email'); ?></th>
                    <th class='head1'><?php echo $tpl->__('label.client'); ?></th>
                    <th class='head1'><?php echo $tpl->__('label.role'); ?></th>
                    <th class='head1'><?php echo $tpl->__('label.status'); ?></th>
                    <th class='head1'><?php echo $tpl->__('headlines.twoFA'); ?></th>
                    <th class='head0 no-sort'><?php echo $tpl->__('label.inviteLink'); ?></th>
                    <th class='head0 no-sort'></th>
                </tr>
            </thead>
            <tbody>
            <?php $now = now(); ?>
            <?php foreach ($tpl->get('allUsers') as $row) {
                $isInvited = strtolower((string) $row['status']) === 'i';
                $isExpired = $isInvited && ! empty($row['pwResetExpiration']) && $now->greaterThan($row['pwResetExpiration']);
            ?>
                    <tr>
                        <td style="padding:6px 10px;">
                             <a href="<?= BASE_URL ?>/users/editUser/<?= $row['id']?>"><?= sprintf($tpl->__('text.full_name'), $tpl->escape($row['firstname']), $tpl->escape($row['lastname'])); ?></a>
                        </td>
                        <td><a href="<?= BASE_URL ?>/users/editUser/<?= $row['id']?>"><?= $tpl->escape($row['username']); ?></a></td>
                        <td><?= $tpl->escape($row['clientName']); ?></td>
                        <td><?= $tpl->__('label.roles.'.$roles[$row['role']]); ?></td>
                        <td>
                            <?php if (strtolower((string) $row['status']) === 'a') {
                                echo $tpl->__('label.active');
                            } elseif ($isExpired) {
                                echo '<span class="tw-text-red-500"><i class="fa fa-clock"></i> '.$tpl->__('label.invited').' ('.$tpl->__('label.expired').')</span>';
                            } elseif ($isInvited) {
                                echo '<span class="tw-text-yellow-600"><i class="fa fa-envelope"></i> '.$tpl->__('label.invited').'</span>';
                            } else {
                                echo $tpl->__('label.deactivated');
                            } ?>
                        </td>
                        <td><?php if ($row['twoFAEnabled']) {
                            echo $tpl->__('label.yes');
                        } else {
                            echo $tpl->__('label.no');
                        } ?></td>
                        <td style="min-width:200px;">
                            <?php if ($isInvited && ! empty($row['pwReset'])) { ?>
                                <div id="invite-link-container-<?= $row['id'] ?>">
                                    <button
                                        type="button"
                                        class="btn btn-link btn-xs"
                                        hx-get="<?= BASE_URL ?>/hx/users/inviteActions/getLink/<?= $row['id'] ?>"
                                        hx-target="#invite-link-container-<?= $row['id'] ?>"
                                        hx-swap="innerHTML"
                                        hx-indicator="#invite-link-container-<?= $row['id'] ?>"
                                        title="<?= $tpl->__('label.copyinviteLink') ?>"
                                    >
                                        <i class="fa fa-link"></i> <?= $tpl->__('label.copyinviteLink') ?>
                                    </button>
                                </div>
                            <?php } ?>
                        </td>
                        <td style="white-space:nowrap;">
                            <?php if ($isInvited) { ?>
                                <button
                                    type="button"
                                    class="btn btn-secondary btn-xs"
                                    hx-post="<?= BASE_URL ?>/hx/users/inviteActions/resend/<?= $row['id'] ?>"
                                    hx-swap="none"
                                    title="<?= $tpl->__('buttons.resend_invite') ?>"
                                >
                                    <i class="fa fa-paper-plane"></i> <?= $tpl->__('buttons.resend_invite') ?>
                                </button>
                            <?php } ?>
                            <a href="<?= BASE_URL ?>/users/delUser/<?php echo $row['id']?>" class="delete"><i class="fa fa-trash"></i> <?= $tpl->__('links.delete'); ?></a>
                        </td>
                    </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {
            leantime.usersController.initUserTable();
            leantime.usersController._initModals();
            leantime.usersController.initUserEditModal();

        }
    );

</script>

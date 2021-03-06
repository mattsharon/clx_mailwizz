<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.4
 */

 ?>
 <div class="col-lg-12 row-group-category">
    <div class="box box-primary borderless">
        <div class="box-body">
            <div class="row">
                <div class="col-lg-2">
                    <div class="form-group">
                        <?php echo $form->labelEx($model, 'max_campaigns');?>
                        <?php echo $form->numberField($model, 'max_campaigns', $model->getHtmlOptions('max_campaigns')); ?>
                        <?php echo $form->error($model, 'max_campaigns');?>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">
                        <?php echo $form->labelEx($model, 'can_delete_own_campaigns');?>
                        <?php echo $form->dropDownList($model, 'can_delete_own_campaigns', $model->getYesNoOptions(), $model->getHtmlOptions('can_delete_own_campaigns')); ?>
                        <?php echo $form->error($model, 'can_delete_own_campaigns');?>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">
                        <?php echo $form->labelEx($model, 'send_to_multiple_lists');?>
                        <?php echo $form->dropDownList($model, 'send_to_multiple_lists', $model->getYesNoOptions(), $model->getHtmlOptions('send_to_multiple_lists')); ?>
                        <?php echo $form->error($model, 'send_to_multiple_lists');?>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <?php echo $form->labelEx($model, 'must_verify_sending_domain');?>
                        <?php echo $form->dropDownList($model, 'must_verify_sending_domain', $model->getYesNoOptions(), $model->getHtmlOptions('must_verify_sending_domain')); ?>
                        <?php echo $form->error($model, 'must_verify_sending_domain');?>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <?php echo $form->labelEx($model, 'can_export_stats');?>
                        <?php echo $form->dropDownList($model, 'can_export_stats', $model->getYesNoOptions(), $model->getHtmlOptions('can_export_stats')); ?>
                        <?php echo $form->error($model, 'can_export_stats');?>
                    </div>
                </div>
            </div>
            <hr />
            <div class="row">
                <div class="col-lg-2">
                    <div class="form-group">
                        <?php echo $form->labelEx($model, 'subscribers_at_once');?>
                        <?php echo $form->numberField($model, 'subscribers_at_once', $model->getHtmlOptions('subscribers_at_once')); ?>
                        <?php echo $form->error($model, 'subscribers_at_once');?>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">
                        <?php echo $form->labelEx($model, 'send_at_once');?>
                        <?php echo $form->numberField($model, 'send_at_once', $model->getHtmlOptions('send_at_once')); ?>
                        <?php echo $form->error($model, 'send_at_once');?>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">
                        <?php echo $form->labelEx($model, 'pause');?>
                        <?php echo $form->numberField($model, 'pause', $model->getHtmlOptions('pause')); ?>
                        <?php echo $form->error($model, 'pause');?>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">
                        <?php echo $form->labelEx($model, 'emails_per_minute');?>
                        <?php echo $form->numberField($model, 'emails_per_minute', $model->getHtmlOptions('emails_per_minute')); ?>
                        <?php echo $form->error($model, 'emails_per_minute');?>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">
                        <?php echo $form->labelEx($model, 'change_server_at');?>
                        <?php echo $form->numberField($model, 'change_server_at', $model->getHtmlOptions('change_server_at')); ?>
                        <?php echo $form->error($model, 'change_server_at');?>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">
                        <?php echo $form->labelEx($model, 'max_bounce_rate');?>
                        <?php echo $form->numberField($model, 'max_bounce_rate', $model->getHtmlOptions('max_bounce_rate')); ?>
                        <?php echo $form->error($model, 'max_bounce_rate');?>
                    </div>
                </div>
            </div>
            <hr />
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <?php echo CHtml::link(IconHelper::make('info'), '#page-info-feedback-header', array('class' => 'btn btn-primary btn-flat btn-xs', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
                        <?php echo $form->labelEx($model, 'feedback_id_header_format');?>
                        <?php echo $form->textField($model, 'feedback_id_header_format', $model->getHtmlOptions('feedback_id_header_format')); ?>
                        <?php echo $form->error($model, 'feedback_id_header_format');?>
                    </div>
                </div>
            </div>
            <hr />
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <?php echo CHtml::link(IconHelper::make('info'), '#page-info-email-footer', array('class' => 'btn btn-primary btn-flat btn-xs', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
                        <?php echo $form->labelEx($model, 'email_footer');?>
                        <?php echo $form->textArea($model, 'email_footer', $model->getHtmlOptions('email_footer')); ?>
                        <?php echo $form->error($model, 'email_footer');?>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
</div>
<!-- modals -->
<div class="modal modal-info fade" id="page-info-feedback-header" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
            </div>
            <div class="modal-body">
                <?php echo Yii::t('settings', 'Following placeholders are available:');?>
                <div style="width:100%; max-height: 100px; overflow:scroll">
                    <?php echo implode("<br />", $model->getFeedbackIdFormatTagsInfoHtml());?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal modal-info fade" id="page-info-email-footer" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
            </div>
            <div class="modal-body">
                <?php echo $model->getAttributeHelpText('email_footer');?>
            </div>
        </div>
    </div>
</div>

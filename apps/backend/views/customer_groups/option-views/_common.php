<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.4.6
 */
 
 ?>
<div class="box box-primary borderless">
    <div class="box-body">
        <div class="row">
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'show_articles_menu');?>
                    <?php echo $form->dropDownList($model, 'show_articles_menu', $model->getYesNoOptions(), $model->getHtmlOptions('show_articles_menu')); ?>
                    <?php echo $form->error($model, 'show_articles_menu');?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'notification_message');?>
                    <?php echo $form->textArea($model, 'notification_message', $model->getHtmlOptions('notification_message')); ?>
                    <?php echo $form->error($model, 'notification_message');?>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"><!-- --></div>
</div>
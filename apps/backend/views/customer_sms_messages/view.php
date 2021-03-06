<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.5.9
 */

/**
 * This hook gives a chance to prepend content or to replace the default view content with a custom content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * In case the content is replaced, make sure to set {@CAttributeCollection $collection->renderContent} to false
 * in order to stop rendering the default content.
 * @since 1.3.3.1
 */
$hooks->doAction('before_view_file_content', $viewCollection = new CAttributeCollection(array(
    'controller'    => $this,
    'renderContent' => true,
)));

// and render if allowed
if ($viewCollection->renderContent) { ?>
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <h3 class="box-title">
                    <?php echo IconHelper::make('glyphicon-comment') .  $pageHeading;?>
                </h3>
            </div>
            <div class="pull-right">
                <?php echo HtmlHelper::accessLink(IconHelper::make('back') . Yii::t('app', 'Back'), array('customer_sms_messages/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Back')));?>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body">
            <div class="table-responsive">
            <?php
            $this->widget('zii.widgets.CDetailView', array(
                'data'      => $message,
                'cssFile'   => false,
                'htmlOptions' => array(
                    'class' => 'table table-striped table-bordered table-hover table-condensed'
                ),
                'attributes' => array(
                    array(
                        'label' => $message->getAttributeLabel('customer_phone'),
                        'value' => $message->customer_phone,
                    ),
                    array(
                        'label' => $message->getAttributeLabel('sms_message'),
                        'value' => $message->sms_message,
                        'type'  => 'raw',
                    ),
                    array(
                        'label' => $message->getAttributeLabel('status'),
                        'value' => $message->status,
                    ),
                    array(
                        'label' => $message->getAttributeLabel('created_at'),
                        'value' => $message->dateAdded,
                    ),
                ),
            ));
            ?>
            </div>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
<?php
}
/**
 * This hook gives a chance to append content after the view file default content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * @since 1.3.3.1
 */
$hooks->doAction('after_view_file_content', new CAttributeCollection(array(
    'controller'        => $this,
    'renderedContent'   => $viewCollection->renderContent,
)));

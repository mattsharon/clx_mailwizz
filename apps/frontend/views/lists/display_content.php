<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.0
 */

// since 1.3.5.6
$htmlOptions = array();
if (!empty($attributes) && !empty($attributes['target']) && in_array($attributes['target'], array('_blank'))) {
    $htmlOptions['target'] = $attributes['target'];
} 
?>

<div class="row">
    <div class="<?php echo $this->layout != 'embed' ? 'col-lg-6 col-lg-push-3 col-md-6 col-md-push-3 col-sm-12' : '';?>">
        <?php echo CHtml::form('', 'post', $htmlOptions);?>
        <?php echo $content;?>
        <?php echo CHtml::endForm();?>
    </div>
</div>
<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ClearCacheCommand
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.6.6
 *
 */

class ClearCacheCommand extends ConsoleCommand
{
    // enable verbose mode
    public $verbose = 1;
    
    /**
     * @return int
     */
    public function actionIndex()
    {
        Yii::app()->hooks->doAction('console_command_clear_cache_before_process', $this);

        $result = $this->process();

        Yii::app()->hooks->doAction('console_command_clear_cache_after_process', $this);

        return $result;
    }

    /**
     * @return int
     */
    protected function process()
    {
        $this->stdout(FileSystemHelper::clearCache());
        return 0;
    }
}

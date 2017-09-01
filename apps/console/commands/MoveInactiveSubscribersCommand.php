<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * MoveInactiveSubscribersCommand
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.7.1
 */

class MoveInactiveSubscribersCommand extends ConsoleCommand
{
    public function actionIndex($src, $dst, $time, $limit = 1000)
    {
        if (empty($src)) {
            $this->stdout('Please set the source list UID by using the --src flag!');
            return 0;
        }

        if (empty($dst)) {
            $this->stdout('Please set the destination list UID by using the --dst flag!');
            return 0;
        }

        if (empty($time)) {
            $this->stdout('Please set the time using the --time flag!');
            return 0;
        }
        
        $srcList = Lists::model()->findByAttributes(array(
            'list_uid' => $src,
        ));
        
        if (empty($srcList)) {
            $this->stdout('We cannot find the source list by it\'s UID!');
            return 0;
        }

        $dstList = Lists::model()->findByAttributes(array(
            'list_uid' => $dst,
        ));

        if (empty($dstList)) {
            $this->stdout('We cannot find the destination list by it\'s UID!');
            return 0;
        }

        $count = $inactive = $success = $error = 0;
        $criteria = new CDbCriteria();
        $criteria->compare('t.list_id', $srcList->list_id);
        $criteria->compare('t.status', ListSubscriber::STATUS_CONFIRMED);
        $criteria->compare('t.date_added', '<' . date('Y-m-d', strtotime($time)));
        $criteria->limit  = (int)$limit;
        
        $subscribersNotIn = array();
        $subscribers = ListSubscriber::model()->findAll($criteria);
        while (!empty($subscribers)) {
            
            foreach ($subscribers as $subscriber) {
                
                $count++;
                
                $this->stdout(sprintf('Checking: "%s"...', $subscriber->email));
                
                // did the subscriber received any campaign at all?
                $sql = 'SELECT subscriber_id FROM {{campaign_delivery_log}} WHERE subscriber_id = :sid LIMIT 1';
                $row = Yii::app()->getDb()->createCommand($sql)->queryRow(true, array(
                    ':sid' => $subscriber->subscriber_id,
                ));
                $campaignDeliveryLog        = !empty($row['subscriber_id']);
                $campaignDeliveryLogArchive = false;
                
                if (!$campaignDeliveryLog) {
                    $sql = 'SELECT subscriber_id FROM {{campaign_delivery_log_archive}} WHERE subscriber_id = :sid LIMIT 1';
                    $row = Yii::app()->getDb()->createCommand($sql)->queryRow(true, array(
                        ':sid' => $subscriber->subscriber_id,
                    ));
                    $campaignDeliveryLogArchive = !empty($row['subscriber_id']);
                }
                
                if (!$campaignDeliveryLog && !$campaignDeliveryLogArchive) {
                    $subscribersNotIn[] = $subscriber->subscriber_id;
                    $this->stdout(sprintf('No campaign was ever sent to "%s".', $subscriber->email));
                    continue;
                }
                //
                
                // did the subscriber ever opened a campaign?
                $sql = 'SELECT subscriber_id FROM {{campaign_track_open}} WHERE subscriber_id = :sid AND DATE(date_added) > :da';
                $row = Yii::app()->getDb()->createCommand($sql)->queryRow(true, array(
                    ':sid' => $subscriber->subscriber_id,
                    ':da'  => date('Y-m-d', strtotime($time)),
                ));
                $hasOpened = !empty($row['subscriber_id']);

                // did the subscriber ever clicked a campaign?
                $sql = 'SELECT subscriber_id FROM {{campaign_track_url}} WHERE subscriber_id = :sid AND DATE(date_added) > :da';
                $row = Yii::app()->getDb()->createCommand($sql)->queryRow(true, array(
                    ':sid' => $subscriber->subscriber_id,
                    ':da'  => date('Y-m-d', strtotime($time)),
                ));
                $hasClicked = !empty($row['subscriber_id']);
                
                if ($hasOpened || $hasClicked) {
                    $subscribersNotIn[] = $subscriber->subscriber_id;
                    $this->stdout(sprintf('"%s" has opened/clicked at least one campaign in the given period of time.', $subscriber->email));
                    continue;
                }

                $inactive++;
                
                if ($subscriber->moveToList($dstList->list_id, false)) {
                    $success++;
                    $this->stdout(sprintf('[SUCCESS] "%s" has been moved to the destination list!', $subscriber->email));
                } else {
                    $error++;
                    $this->stdout(sprintf('[FAIL] "%s" could not be moved to the destination list!', $subscriber->email));
                }
            }
            
            $_criteria = clone $criteria;
            if (!empty($subscribersNotIn)) {
                $_criteria->addNotInCondition('subscriber_id', $subscribersNotIn);
            }
            $subscribers = ListSubscriber::model()->findAll($_criteria);
        }
        
        $this->stdout(sprintf('Done processing %d subscribers out of which %d were inactive from which %d were moved successfully and %d had errors!', $count, $inactive, $success, $error));
        return 0;
    }
    
    public function getHelp()
    {
        $cmd = $this->getCommandRunner()->getScriptName() .' '. $this->getName();
        
        $help  = sprintf('command: %s --src=LIST_UID --dst=LIST_UID --time=EXPRESSION --limit=1000', $cmd) . "\n";
        $help .= '--src=UID where UID is the source list unique 13 chars id.' . "\n";
        $help .= '--dst=UID where UID is the destination list unique 13 chars id.' . "\n";
        $help .= '--time=EXPRESSION where EXPRESSION can be any expression parsable by php\'s strtotime function. ie: --time="-6 months".' . "\n";
        $help .= '--limit=1000 where 1000 is the number of subscribers to process at once.' . "\n";
        
        return $help;
    }
}
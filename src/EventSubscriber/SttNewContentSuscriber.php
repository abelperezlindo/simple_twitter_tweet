<?php
namespace Drupal\simple_twitter_tweet\EventSubscriber;

use Drupal\entity_events\Event\EntityEvent;
use Drupal\entity_events\EventSubscriber\EntityEventInsertSubscriber;


class SttNewContentSuscriber extends EntityEventInsertSubscriber {

  public function onEntityInsert(EntityEvent $event) {
    
    $entity = $event->getEntity();

    if ($entity instanceof \Drupal\node\NodeInterface) {

      /** Se trata de una entidad Node */

      $content_type = \Drupal::config('simple_twitter_tweet.settings')->get('content');

      if($entity->bundle() == $content_type){
        
        /*
        $publish = $entity->{$social_publish}->value;
        if($publish->value === ''){

        }
        */

        /** @var Drupal\simple_twitter_tweet\Utils\TwitterWrapper $twitter */
        $twitter    = \Drupal::service('simple_twitter_tweet.twitter_wrapper');
        /** @var Drupal\simple_twitter_tweet\Utils\TwitterTextFromEntity $textGetter (utils) */
        $textGetter = \Drupal::service('simple_twitter_tweet.get_text_entity');


        $status = $textGetter::getMessage($entity);
        $twitter::tweet($status);
        
      }
    }
  }
}
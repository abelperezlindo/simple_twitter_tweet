<?php
namespace Drupal\simple_twitter_tweet\EventSubscriber;

use Drupal\entity_events\Event\EntityEvent;
use Drupal\entity_events\EventSubscriber\EntityEventInsertSubscriber;


class SttNewContentSuscriber extends EntityEventInsertSubscriber {

  public function onEntityInsert(EntityEvent $event) {
    

    $entity = $event->getEntity();

    if ($entity instanceof \Drupal\node\NodeInterface) {

      /**
       * @var \Drupal\node\NodeInterface $entity  
       * Se trata de una entidad Node
       */
      $config_manager = \Drupal::service('simple_twitter_tweet.config_manager');
      $content_type   = $config_manager::get('content');
      $social_publish = $config_manager::get('publish_field');
      
      if($entity->bundle() == $content_type && isset($entity->{$social_publish})){
        
        $publish = $entity->{$social_publish}->value;
        if($publish->value === ''){
          /** @var Drupal\simple_twitter_tweet\Utils\TwitterWrapper $twitter */
          $twitter = \Drupal::service('simple_twitter_tweet.twitter_wrapper');
          $twitter::tweetEntity($entity);
        }
      }
    }
  }
}
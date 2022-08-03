<?php 

namespace Drupal\simple_twitter_tweet\Utils;

use \Drupal\node\NodeInterface;
use \Drupal\Component\Utility\Html;
use \Drupal\Core\Config\ImmutableConfig;
use \Abraham\TwitterOAuth\TwitterOAuth;
use Drupal\Core\Routing\NullMatcherDumper;
use stdClass;
use Symfony\Component\Validator\Constraints\Length;

class TwitterTextFromEntity
{


  public static function getMessage(NodeInterface $entity){
 
    $config = \Drupal::config('simple_twitter_tweet.settings');
 
    if($entity->getType() !== $config->get('content')){
      return null;
    }

    return self::getTextValue($entity, $config);
    
  }

  private static function getTextValue(NodeInterface $entity, ImmutableConfig $config){
    $text = '';
    
    $text_field  =  $entity->{$config->get('body') };
    $field_type = $text_field->getFieldDefinition()->getType();

    if(in_array($field_type, ['body' ])){
      
      $text = Html::decodeEntities($text_field->value);
      if(!empty($config->get('body_use_summary')) && !empty($text_field->summary)) {
         $text = Html::decodeEntities($text_field->sumary);
      }
      return $text;
    }
    
    if($config->get('body') === 'title'){
      $text = Html::decodeEntities($entity->getTitle());
    } 

    if($config->get('body_concat_url')){
      $link = self::getEntityLink($entity);
      $overflow = self::calculateCharNumberTW($text, $link);
      if($overflow){
        //calculate
      }
      return $text . ' ' . $link;
    }
  }

  private static function calculateCharNumberTW($text, $link){
    return false;
  }
  private static function getEntityLink($entity){
    return $entity->toUrl()->setAbsolute(TRUE)->toString();
  }
}
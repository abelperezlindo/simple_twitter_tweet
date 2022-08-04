<?php 

namespace Drupal\simple_twitter_tweet\Utils;

use \Drupal\node\NodeInterface;
use \Drupal\Component\Utility\Html;
use \Drupal\Core\Config\ImmutableConfig;


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
    $status = '';
    
    $text_field  =  $entity->{$config->get('body') };
    $field_type = $text_field->getFieldDefinition()->getType();

    if(in_array($field_type, ['string', 'text', 'text_with_summary', 'string_long'])){
      
      $status = Html::decodeEntities($text_field->value);
      if(!empty($config->get('body_use_summary')) && !empty($text_field->summary)) {
         $text = Html::decodeEntities($text_field->sumary);
      }
      return $text;
    }
    
    if($config->get('body') === 'title'){
      $status = Html::decodeEntities($entity->getTitle());
    } 

    if($config->get('body_concat_url')){
      $link = self::getEntityLink($entity);
      $status = self::calculateCharNumberTW($status, $link);
    }

    return $status;
  }
  /**
   * Retorna texto para nuevo status que no supera los max de caracteres
   * @param string $text El texto que se obtuvo de la entidad
   * @param string $link El enlace a la entidad
   * @return string texto para la actualizacion de estado 
   *  que no supera el nro de caracteres permitiido
   * 
   * URL: todas las URL están envueltas en enlaces t.co. 
   * Esto significa que la longitud de una URL está 
   * definida por el parámetro transformURLLength 
   * en el archivo de configuración de texto de Twitter. 
   * La longitud actual de una URL en un Tweet es de 23 caracteres, 
   * incluso si la longitud de la URL normalmente fuera más corta.
   */
  private static function calculateCharNumberTW($text, $link){
    if(empty($text) && empty($link)){
      return false;
    }

    $tweet_max    = 280; // Maximo de caracteres en un tweet
    $https_len    = 24; // Los caracteres que se pierden por incluir un link en el tweet + espacio
    $text         = Html::decodeEntities($text); //Clear

    if(empty($link)){
      // tweet solo texto
      return substr($text, 0, $tweet_max); //recorta de ser necesario
    } 
    else if(empty($text)){
      return $link;
    }

    return substr($text, 0, $tweet_max - $https_len) . ' ' . $link;
  }


  private static function getEntityLink($entity){
    return $entity->toUrl()->setAbsolute(TRUE)->toString();
  }
}
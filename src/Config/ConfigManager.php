<?php
namespace Drupal\simple_twitter_tweet\Config;

class ConfigManager{

  public const TITLE_ALLOWED_FIELDS_TYPE = ['string', 'text'];
  public const BODY_ALLOWED_FIELDS_TYPE  = ['string', 'text', 'text_with_summary', 'string_long'];
  public const IMAGE_ALLOWED_FIELDS_TYPE = ['image'];
  /**
   * Set a key in state
   */
  public static function set($key, $value){
    if(empty($key)){
      return;
    }

    \Drupal::state()->set('simple_twitter_tweet.' . $key, $value);
  }
   /**
   * get a variable from state
   */
  public static function get(string $key){
    if(empty($key)){
      return null;
    }

    return \Drupal::state()->get('simple_twitter_tweet.' . $key);
  }
  /**
   * get values of multiples variables from state
   */
  public static function getMultiple(array $keys){
    $result = [];
    foreach($keys as $key => $value){
      $result[$value] = self::get($value);
    }
    return $result;
  }
  /**
   * get all state vars used by this module
   */
  public static function getAll(){
    $allKeys = [
      'content',
      'body',
      'body_use_summary',
      'body_concat_url',
      'image',
      'image_style',
      'preview_markup',
      'twitter_consumer_key',
      'twitter_consumer_secret',
      'twitter_access_token',
      'twitter_access_token_secret',
    ];
    return self::getMultiple($allKeys);
  }

  /** 
   * set values of multiples variables from state
   */
  public static function setMultiple(array $keys){
    
    foreach($keys as $key => $value){
      self::set($key, $value);
    }
  }


  public static function getFieldsOptions(string $content_type = 'nothing'){

    $options = ['title' => [], 'body' => [], 'image' => []];
    if($content_type == 'nothing'){
      return $options;
    }

    /**
     * @var \Drupal\Core\Field\FieldDefinitionInterface[] $fields_def 
     */
    
    $fields_def =  \Drupal::service('entity_field.manager')
      ->getFieldDefinitions('node', $content_type );
    foreach($fields_def as $key => $value){
      if(in_array($value->getType(), self::TITLE_ALLOWED_FIELDS_TYPE)){
        $options['title'][$key] = $key;
      }

      if(in_array($value->getType(), self::BODY_ALLOWED_FIELDS_TYPE)){
        $options['title'][$key] = $key;
        $options['body'][$key] = $key;
      }
      if(in_array($value->getType(), self::IMAGE_ALLOWED_FIELDS_TYPE)){
        $options['image'][$key] = $key;
      }
    }
    return $options;
  }

  public static function getNodeTypesIds(){
    $types = [];
    $contentTypes = \Drupal::service('entity_type.manager')->getStorage('node_type')->loadMultiple();
    foreach ($contentTypes as $contentType) {
        $types[$contentType->id()] = $contentType->label();
    }
    return $types;
  }

  public static function getImageStylesOptions(){
    return \Drupal::entityQuery('image_style')->execute();
  }
  
}

<?php 

namespace Drupal\simple_twitter_tweet\Utils;

use \Drupal\node\NodeInterface;

use \Abraham\TwitterOAuth\TwitterOAuth;
use stdClass;

class TwitterWrapper
{


  /**
   * tweet a drupal node type entity
   */
  public static function tweetEntity($entity){
    
    $tweetContent = new stdClass();
    if(gettype($entity) === 'string'){
      $tweetContent->text = $entity;
    } else {
      $tweetContent = self::getFieldsFromEntity($entity);
    }

    self::tweet($tweetContent); 
  }

  /**
   * Tweet from stdObject
   * The stdObject have:
   *  text: the tweet status
   *  images[]: Array of images urls 
   *  url: Content url, if is set is concat tu text, and tweeter transform this in t.co link 
   */
  public static function tweet(stdClass $tweetContent){
    $config_manager = \Drupal::service('simple_twitter_tweet.config_manager');
    $config = $config_manager::getMultiple([
      'twitter_consumer_key',
      'twitter_consumer_secret',
      'twitter_access_token',
      'twitter_access_token_secret',
    ]);
    $twitter = new TwitterOAuth(
      $config['twitter_consumer_key'],
      $config['twitter_consumer_secret'],
      $config['twitter_access_token'],
      $config['twitter_access_token_secret'],
    );
    $twitter->setTimeouts(25, 15);
    //$twitter->setApiVersion('2');
    $status = [];
    if(!empty($tweetContent->url)){
      // calculate chars an concat to $tweetContent->text
      
    }

    try {
      $statues = $twitter->post(
        "statuses/update", 
        [
          "status" => $tweetContent->text
        ]
      );
      if ($twitter->getLastHttpCode() == 200) {
          // Tweet posted successfully
          \Drupal::logger('simple_twitter_tweet')->error('New tweet created');
      } else if(isset($statues->errors)) {
          // Handle error case
          \Drupal::logger('simple_twitter_tweet')->error('an error has occurred: ' . $statues->errors[0]->message );
      } else {
        \Drupal::logger('simple_twitter_tweet')->error('an error has occurred' );
      }
    } catch(\Exception $e) {
      \Drupal::logger('simple_twitter_tweet')->error($e->getMessage());
    }
   

  }
  

  private static function getFieldsFromEntity(NodeInterface $entity){
    /**
     * @var \Drupal\simple_twitter_tweet\Config\ConfigManager $config_manager 
     */
    $config_manager = \Drupal::service('simple_twitter_tweet.config_manager');
    $config = $config_manager::getAll();
    $tweetContent = new  stdClass();

    if(!empty($config['body']) && $entity->hasField($config['body'])){

      $body =  $entity->{$config['body']};
      $field_type = $body->getFieldDefinition()->getType();
      if(in_array($field_type, $config_manager::BODY_ALLOWED_FIELDS_TYPE)){
        
        if(!empty($config['body_use_summary']) && !empty($body->summary)) {
         
          $tweetContent->text = $body->sumary;
          //Html::decodeEntities($body);
        }
        else {
          $tweetContent->text = $body->value;
        }
      }
    }

    if(!empty($config['image']) && $entity->hasField($config['image'])){
      $message[] = '<div class="post-preview-img" title="Post image">';

      /** @var \Drupal\file\Plugin\Field\FieldType\FileFieldItemList $ref_list */
      $ref_list = $entity->{$config['body']}->referencedEntities(); 
      if(isset($ref_list[0])){
        /** @var \Drupal\file\Entity\File $file */
        $file_uri   = $ref_list[0]->getFileUri();
        $image_uri  = $file_uri;

        if(!empty($config['image_style'])){
          $image_uri = \Drupal\image\Entity\ImageStyle::load($config['image_style'])
            ->buildUrl($file_uri);
        }

        if(\Drupal::hasService('file_url_generator')) {
          $generator = \Drupal::service('file_url_generator');
          $tweetContent->image_url  = $generator->generateAbsoluteString($image_uri);
        }
      } 
    }

    /**
     * @var \Drupal\Core\Url $url 
     */
    $node_url  = $entity->toUrl();
    $node_url->setAbsolute(TRUE);
    $tweetContent->content_url = $node_url->toString();

    return $tweetContent;
  }

  public static function testApiAccess($config = []){
    $config_manager = \Drupal::service('simple_twitter_tweet.config_manager');
    if(empty($config)){
      $config = [
        'twitter_consumer_key'        => $config_manager::get('twitter_consumer_key'),
        'twitter_consumer_secret'     => $config_manager::get('twitter_consumer_secret'),
        'twitter_access_token'        => $config_manager::get('twitter_access_token'),
        'twitter_access_token_secret' => $config_manager::get('twitter_access_token_secret'),
      ];
    }


    $tw = new TwitterOAuth(
      $config['twitter_consumer_key'],
      $config['twitter_consumer_secret'], 
      $config['twitter_access_token'], 
      $config['twitter_access_token_secret']
    );
    $tw->setTimeouts(10, 15);
    $tw->setApiVersion('2');

    $uid = explode('-', $config['twitter_access_token'])[0];
    $content = $tw->get('users', ['ids' => $uid]);
    if(isset($content->errors)){
      foreach($content->errors as $error){
      
        return  t(
            'Account verification failed, Twitter returned the following Error code @error_code: "@error_msg".',
            ['@error_code' => $error->code, '@error_msg' => $error->message]
        );
      }
    } elseif(isset($content->data)){
      foreach($content->data as $data){
        return t('Ok, @user.', ['@user' => $data->username]);
      }

    } else {
      return isset($content->title) ? t($content->title) : 'Ocurrio un problema';
    }
  }
  
}
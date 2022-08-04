<?php 

namespace Drupal\simple_twitter_tweet\Utils;

use \Drupal\node\NodeInterface;
use \Drupal\Component\Utility\Html;

use \Abraham\TwitterOAuth\TwitterOAuth;
use stdClass;
use Symfony\Component\Validator\Constraints\Length;

class TwitterWrapper
{
  public static function tweet(string $text){
    
    $m_name = 'simple_twitter_tweet';
    $twitter = new TwitterOAuth(
      \Drupal::state()->get($m_name . 'twitter_consumer_key', ''),
      \Drupal::state()->get($m_name . 'twitter_consumer_secret', ''),
      \Drupal::state()->get($m_name . 'twitter_access_token', ''),
      \Drupal::state()->get($m_name . 'twitter_access_token_secret', '')
    );
    $twitter->setTimeouts(25, 15);

    /**
     * @var array $parameters:
     *  $parameters['status']
     *  $parameters['media_ids']
     */
    $parameters = [];
    $parameters['status'] = $text;

    try {
      $statues = $twitter->post(
        "statuses/update", 
        $parameters
      );
      if ($twitter->getLastHttpCode() == 200) {
          // Tweet posted successfully
          \Drupal::logger('simple_twitter_tweet')->notice('New tweet created');
          return true;
      } else if(isset($statues->errors)) {
          // Handle error case
          \Drupal::logger('simple_twitter_tweet')->error('an error has occurred: ' . $statues->errors[0]->message );
      } else {
        \Drupal::logger('simple_twitter_tweet')->error('an error has occurred' );
      }
    } catch(\Exception $e) {
      \Drupal::logger('simple_twitter_tweet')->error($e->getMessage());
      return false;
    }
   
    return false;
  }
  

  public static function testApiAccess($consumer_key, $consumer_secret, $token, $token_secret){
   
    $tw = new TwitterOAuth(
      $consumer_key,
      $consumer_secret, 
      $token, 
      $token_secret
    );
    $tw->setTimeouts(10, 15);
    $tw->setApiVersion('2');

    $uid = explode('-', $token)[0];
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
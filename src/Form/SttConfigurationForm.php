<?php
namespace Drupal\simple_twitter_tweet\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;


class SttConfigurationForm extends ConfigFormBase {

    public function getFormId()
    {
        return 'simple_twitter_tweet_form';
        
    }

    public function getEditableConfigNames(){
      return [
        'simple_twitter_tweet.settings'  
      ];
    }

    public function buildForm(array $form, FormStateInterface $form_state ){

      // Default settings.
      $config = $this->config('simple_twitter_tweet.settings');
      // Nombre del modulo en una variable > xq es muy largo
      $module_name      = 'simple_twitter_tweet';
      $consumer_key     = \Drupal::state()->get($module_name . '.twitter_consumer_key', '');
      $consumer_secret  = \Drupal::state()->get($module_name . '.twitter_consumer_secret', '');
      $token            = \Drupal::state()->get($module_name . '.twitter_access_token', '');
      $token_secret     = \Drupal::state()->get($module_name . '.twitter_access_token_secret', '');
      // Form constructor.
      $message = '';
      if(!empty($consumer_key) && !empty($consumer_key)){
        if(!empty($token) && !empty($token_secret)){
          $twitter = \Drupal::service('simple_twitter_tweet.twitter_wrapper');
          $message = $twitter::testApiAccess($consumer_key, $consumer_secret, $token, $token_secret);
        }
      }
      $form = parent::buildForm($form, $form_state);

      $form['sections'] = [
        '#type'         => 'vertical_tabs',
        '#title'        => t('Settings'),
        '#default_tab'  =>'edit-content-box'
      ];
      $form['content_box'] = [
        '#type'         => 'details',
        '#title'        => t('Content Settings'),
        '#group'        => 'sections',
        '#description'  => t('
          Select the type of content and the 
          fields that will be used for sharing on 
          social networks when the content is created.'
        ),
      ];
      $form['content_box']['content'] = [
        '#type'           => 'textfield',
        '#title'          => t('Content bundle'),
        '#description'    => t('Enter the content bundle'),
        '#default_value'  => $config->get('content')
      ];
      $form['content_box']['body'] = [
        '#type'           => 'textfield',
        '#title'          => t('Text field'),
        '#description'    => t('Enter the text field to use. It is only allowed to use the title of the content or a field of typestring, text, text_long or text_with_summary'),
        '#default_value'  => $config->get('body')
      ];
      $form['content_box']['body_use_summary'] = [
        '#type'           => 'checkbox',
        '#title'          => t('Use summary if available for selected field in tweet text.'),
        '#default_value'  => $config->get('body_use_summary'),
        '#states'         => [
          'invisible' => [':input[name="body"]' => ['value' => '']],
        ],
      ];

      $form['content_box']['body_concat_url'] = [
        '#type'   => 'checkbox',
        '#title'  => t('Concat content url to tweet text.'),
        '#default_value' => $config->get('body_concat_url'),
      ];
      $publishing_options = \Drupal::service('publishing_options.content'); // servicio provisto por pub_options
      $po                 = $publishing_options->getPublishingOptions();
      $options            = [];

      foreach($po as $item){
        $options[$item->pubid] = t($item->title);
      }
      $form['content_box']['pubid'] = [
        '#type'           => 'select',
        '#title'          => t('Publishing option'),
        '#options'        =>  $options,
        '#description'    => t('Publication option that indicates if the content should be published on twitter or not.'),
        '#default_value'  => $config->get('pub_option_id'),
      ];

      $form['twitter'] = [
        '#type'   => 'details',
        '#title'  => t('Twitter API Access Settings'),
        '#group' => 'sections',
      ];
      if(!empty($message)){
        $form['twitter']['message'] = [
          '#type'   => 'markup',
          '#markup' => t('<div><p><strong>Respuesta de la API:</strong></p><pre>@message</pre></div>', ['@message' => $message]),
        ];
      }

      $form['twitter']['twitter_consumer_key'] = [
        '#type'           => 'textfield',
        '#title'          => t('Consumer Key'),
        '#description'    => t('Enter the consumer key'),
        '#default_value'  => $consumer_key,
      ];
      $form['twitter']['twitter_consumer_secret'] = [
        '#type'           => 'textfield',
        '#title'          => t('Consumer Secret'),
        '#description'    => t('Enter the consumer secret'),
        '#default_value'  => $consumer_secret,
      ];
      $form['twitter']['twitter_access_token'] = [
        '#type'           => 'textfield',
        '#title'          => t('Access Token'),
        '#description'    => t(
          'Enter the access token. This access 
          token allows access to the twitter account 
          in which the content of the site will be published.'
        ),
        '#default_value'  => $token,
      ];
      $form['twitter']['twitter_access_token_secret'] = [
        '#type'           => 'textfield',
        '#title'          => t('Access Token Secret'),
        '#default_value'  => $token_secret,
        '#description'    => t(
          'Enter the access token secret. This access 
          token secret allows access to the twitter account 
          in which the content of the site will be published.'
        ),
      ];
      $form['doc'] = [
        '#type'         => 'details',
        '#title'        => t('Docs'),
        '#group'        => 'sections',
        '#description'  => t(''),
      ];

      $form['doc']['link'] = [
        '#type'         => 'markup',
        '#markup'       => t('<a target="_blank" href="https://github.com/abelperezlindo/simple_twitter_tweet/blob/main/README.md">Ver documentación</a>')
      ];
      
      return $form;
    }
  
    /**
     * { @inheritDoc }
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
      $trigger  = $form_state->getTriggeringElement();
      // Nombre del modulo en una variable 0> xq es muy largo
      $module_name = 'simple_twitter_tweet';

      $config = $this->config($module_name . '.settings');
      $config->set(
        'content', 
        $form_state->getValue('content')
      );
      $config->set(
        'body', 
        $form_state->getValue('body')
      );
      $config->set(
        'body_use_summary', 
        $form_state->getValue('body_use_summary')
      );
      $config->set(
        'body_concat_url', 
        $form_state->getValue('body_concat_url')
      );
      $config->set(
        'pub_option_id', 
        $form_state->getValue('pubid')
      );
      $config->save();

      // configuracion del acceso a la api
      \Drupal::state()->set(
        $module_name . '.twitter_consumer_key', 
        $form_state->getValue('twitter_consumer_key')
      );
      \Drupal::state()->set(
        $module_name . '.twitter_consumer_secret', 
        $form_state->getValue('twitter_consumer_secret')
      );
      \Drupal::state()->set(
        $module_name . '.twitter_access_token', 
        $form_state->getValue('twitter_access_token')
      );
      \Drupal::state()->set(
        $module_name . '.twitter_access_token_secret', 
        $form_state->getValue('twitter_access_token_secret')
      );
  
      return parent::submitForm($form, $form_state);
    }

    /**
     * { @inheritDoc }
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {

    }
    
}

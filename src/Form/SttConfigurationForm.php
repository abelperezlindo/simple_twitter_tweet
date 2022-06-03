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

      // Form constructor.
      $form = parent::buildForm($form, $form_state);
      /** @var \Drupal\Core\Utility\Token $token */
      $tokens = \Drupal::token();
      $config_manager   = \Drupal::service('simple_twitter_tweet.config_manager');
      $types            = $config_manager::getNodeTypesIds();
      $saved_content_id = $config_manager::get('content');
      $image_styles     = $config_manager::getImageStylesOptions();

      $form['sections'] = [
        '#type'         => 'vertical_tabs',
        '#title'        => t('Settings'),
        '#default_tab'  =>'edit-content-box'
      ];

      if(empty($saved_content_id)){
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
          '#type'         => 'select',
          '#title'        => t('Content type'),
          '#options'      => $types,
          '#empty_option' => t('Select'),
          '#description'  => t('
            Select the type of content you want to use to be published 
            on social networks at the time of its creation.
          '),
        ];
        $form['content_box']['save_content_id'] = [
          '#type'   => 'submit',
          '#name'   => 'save_content_id',
          '#value'  => t('save content config')
        ]; 
      }
      else {
        $options = $config_manager::getFieldsOptions($saved_content_id);
        $form['content_box'] = [
          '#type'         => 'details',
          '#title'        => t('Content Settings'),
          '#group'        => 'sections',
          '#description'  => t( 
            'The content type used is :@content_type, you can select the fields from which the social post will be created.',
            [':@content_type' => $types[$saved_content_id]]
          ),
          
        ];

        $form['content_box']['body'] = [
          '#type'           => 'select',
          '#title'          => t('Field to use to generate the body of the post'),
          '#options'        => $options['body'],
          '#default_value'  => $config_manager::get('body') ?? '',
          '#empty_option'   => t('Select'),
          '#description'    => t(
            'Select the field you want to use
            as a font for the body of the tweet. If the text is too long, it will be cut off.'
          ),
        ];
        $form['content_box']['body_use_summary'] = [
          '#type'           => 'checkbox',
          '#title'          => t('Use summary if available for selected field in tweet text.'),
          '#default_value'  => $config_manager::get('body_use_summary') ?? '',
          '#states'         => [
            'invisible' => [':input[name="body"]' => ['value' => '']],
          ],
        ];

        $form['content_box']['body_concat_url'] = [
          '#type'   => 'checkbox',
          '#title'  => t('Concat content url to tweet text.'),
          '#default_value' => $config_manager::get('body_concat_url') ?? '',
        ];

        $form['content_box']['image'] = [
          '#type'           => 'select',
          '#title'          => t('Field to use to generate the image of the post'),
          '#options'        => $options['image'],
          '#description'    => t('Select the field you want to use for the post image.'),
          '#default_value'  => $config_manager::get('image') ?? '',
          '#empty_option'   => t('Select')
        ];
        $form['content_box']['image_style'] = [
          '#type'           => 'select',
          '#title'          => t('Post Image Style'),
          '#options'        => $image_styles,
          '#default_value'  => $config_manager::get('image_style') ?? '',
          '#empty_option'   => t('Select'),
          '#description'    => t(
            'Enter the image style you want to use, 
            if you do not select any option the original image will be used.'
          ),
          '#states'         => [
            'invisible' => [':input[name="image"]' => ['value' => '']],
          ],
        ];
        $form['content_box']['options']['delete'] = [
          '#type'   => 'submit',
          '#name'   => 'delete_content_config',
          '#value'  => t('Delete this setting')
        ]; 
        $form['content_box']['options']['perview'] = [
          '#type'   => 'submit',
          '#name'   => 'content_config_preview',
          '#value'  => t('Save these settings and preview')
        ];
        $preview = $config_manager::get('preview_markup') ?? '';
        if(!empty($preview)){
          $form['content_box']['preview_markup'] = [
            '#markup' => $preview,
          ];
          $config_manager::set('preview_markup', ''); 
        }      
      }

      $form['twitter'] = [
        '#type'   => 'details',
        '#title'  => t('Twitter API Access Settings'),
        '#group' => 'sections',
      ];
      $form['twitter']['twitter_consumer_key'] = [
        '#type'           => 'textfield',
        '#title'          => t('Consumer Key'),
        '#description'    => t('Enter the consumer key'),
        '#default_value'  => $config_manager::get('twitter_consumer_key'),
      ];
      $form['twitter']['twitter_consumer_secret'] = [
        '#type'           => 'textfield',
        '#title'          => t('Consumer Secret'),
        '#description'    => t('Enter the consumer secret'),
        '#default_value'  => $config_manager::get('twitter_consumer_secret'),
      ];
      $form['twitter']['twitter_access_token'] = [
        '#type'           => 'textfield',
        '#title'          => t('Access Token'),
        '#description'    => t(
          'Enter the access token. This access 
          token allows access to the twitter account 
          in which the content of the site will be published.'
        ),
        '#default_value'  => $config_manager::get('twitter_access_token'),
      ];
      $form['twitter']['twitter_access_token_secret'] = [
        '#type'           => 'textfield',
        '#title'          => t('Access Token Secret'),
        '#default_value'  => $config_manager::get('twitter_access_token_secret'),
        '#description'    => t(
          'Enter the access token secret. This access 
          token secret allows access to the twitter account 
          in which the content of the site will be published.'
        ),
        
      ];
      $form['twitter']['twitter_test_connection'] = [
        '#type'  => 'submit',
        '#name'  => 'action_twitter_test',
        '#value' => t('Test api access '),
      ];

      return $form;
    }
  
    /**
     * { @inheritDoc }
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
      $trigger = $form_state->getTriggeringElement();
      $config_manager = \Drupal::service('simple_twitter_tweet.config_manager');


      if($trigger['#type'] === 'submit' && $trigger['#name'] =='save_content_id'){
        $config_manager::set('content', $form_state->getValue('content'));
        return;
      }

      if($trigger['#type'] === 'submit' && $trigger['#name'] =='delete_content_config'){
        $config_manager::setMultiple([
          'content'           => '',
          'body'              => '',
          'image'             => '',
          'image_style'       => '',
          'body_use_summary'  => '',
          'body_concat_url'   => '',
        ]);
      
        return;
      }

      if($trigger['#type'] === 'submit' && $trigger['#name'] =='content_config_preview'){

        //$config_manager::setContentConfig($form_state->getValues());
        $config_manager::setMultiple([
          'body'              => $form_state->getValue('body'),
          'body_use_summary'  => $form_state->getValue('body_use_summary'),
          'body_concat_url'   => $form_state->getValue('body_concat_url'),
          'image'             => $form_state->getValue('image'),
          'image_style'       => $form_state->getValue('image_style'),
        ]);

        $saved_content_id = $config_manager::get('content');
        $query = \Drupal::entityQuery('node');
        $query
          ->condition('type', $saved_content_id)
          ->sort('changed', 'DESC')
          ->range(0, 1);

        $nid      = $query->execute();
        if(empty($nid)){
          $config_manager::set('preview_markup', 'There is no content of the selected type.');
          return;
        }

        $node     = \Drupal::entityTypeManager()->getStorage('node')->load(array_pop($nid));
        $message  = [];
        $message[] = '<div class="post-preview-wrapper"><p>Previewing ' . $saved_content_id . ' type node as an example</p>'; 
        $message[] = '<div class="post-preview-card">';
        if($node->hasField($form_state->getValue('image'))){
          $message[] = '<div class="post-preview-img" title="Post image">';

          /** @var \Drupal\file\Plugin\Field\FieldType\FileFieldItemList $ref_list */
          $ref_list = $node->{$form_state->getValue('image')}->referencedEntities(); 
          if(isset($ref_list[0])){
            /** @var \Drupal\file\Entity\File $file */
            $file_uri = $ref_list[0]->getFileUri();
            if(!empty($form_state->getValue('image_style'))){
              
              $image_uri = \Drupal\image\Entity\ImageStyle::load($form_state->getValue('image_style'))
                ->buildUrl($file_uri);

            }
            else {
              $image_uri = $file_uri;
            }

            // Remove the if-else when core_version_requirement >= 9.3 for this module.
            if(\Drupal::hasService('file_url_generator')) {
              $generator = \Drupal::service('file_url_generator');
             
              $img_url = $generator->generateAbsoluteString($image_uri);
            }
          } 
          if(!empty($img_url)){
            $message[] = '<img src="' . $img_url . '">';
          }
          $message[] = '</div>';
        }


        if($node->hasField($form_state->getValue('body'))){

          $message[] = '<div class="post-preview-body title="Post Body"">';
          $body =  $node->{$form_state->getValue('body')};
          $field_type = $body->getFieldDefinition()->getType();
          if(in_array($field_type, ['string', 'text', 'text_long', 'text_with_summary'])){
            $tweet_text = '';
            if($form_state->getValue('body_use_summary') && !empty($body->summary)) {
              
              $tweet_text = $body->summary;
            }
            else {
              $tweet_text = $body->value;

            }

            if($form_state->getValue('body_concat_url')){
              /**
               * @var \Drupal\Core\Url $url 
               */
              $node_url  = $node->toUrl();
              $node_url->setAbsolute(TRUE);
              $tweet_text = $body->summary . ' ' .  $node_url->toString();
            }
            
            $message[] = '<p>' . $tweet_text . '</p>';
          }
          $message[] = '</div>';
        }
        $message[] = '</div></div>';
  
        $config_manager::set('preview_markup', implode($message));

      }


      if($trigger['#type'] === 'submit' && $trigger['#name'] =='action_twitter_test'){

        $config_manager::setMultiple([
          'twitter_consumer_key'        => $form_state->getValue('twitter_consumer_key'),
          'twitter_consumer_secret'     => $form_state->getValue('twitter_consumer_secret'),
          'twitter_access_token'        => $form_state->getValue('twitter_access_token'),
          'twitter_access_token_secret' => $form_state->getValue('twitter_access_token_secret')
        ]);
        $twitter = \Drupal::service('simple_twitter_tweet.twitter_wrapper');
        \Drupal::messenger()->addMessage($twitter::testApiAccess());
        
      }

      if($trigger['#type'] === 'submit' && $trigger['#id'] == 'edit-submit'){
        $config_manager::setMultiple($form_state->getValues());
        return parent::submitForm($form, $form_state);
      }   
    }

    /**
     * { @inheritDoc }
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
      

    }
    
}

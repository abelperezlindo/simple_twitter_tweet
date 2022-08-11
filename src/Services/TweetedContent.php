<?php

namespace Drupal\simple_twitter_tweet\Services;

use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 *
 */
class TweetedContent {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Construct a repository object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }
  public function set($entity_id, $tweet_content, $tweet_id){
    $now = new DrupalDateTime('now');
  
    $fields = [
      'entity_id'     => (int) $entity_id,
      'tweet_content' => (string) $tweet_content,
      'tweet_id'      => (string) $tweet_id,
      'created'       => $now->getTimestamp()
    ];

    $insert = $this->connection->insert('stt_tweet');
    $insert->fields(
      [
        'entity_id', 
        'tweet_content', 
        'tweet_id',
        'created'
      ], 
      $fields
    );

    return $insert->execute();
  }


  public function get(int $entity_id){
    $query = $this->connection->select('stt_tweet', 'tw')
      ->fields(
        'tw',
        [
          'entity_id', 
          'tweet_content', 
          'tweet_id',
          'created'
        ]
      )
      ->condition('entity_id', $entity_id)
      ->execute();

    return $query->fetchObject();
  }

  public function isTweeted(int $entity_id){
    $query = $this->connection->select('stt_tweet', 'tw')
      ->fields(
        'tw',
        ['tweet_id']
      )
      ->condition('entity_id', $entity_id)
      ->execute();
    $result = $query->fetch()->tweet_id;
    return isset($result) ?  true : false;
  }
}
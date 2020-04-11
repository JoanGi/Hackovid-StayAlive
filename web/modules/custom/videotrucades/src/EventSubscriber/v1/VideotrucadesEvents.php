<?php

namespace Drupal\videotrucades\EventSubscriber\v1;

use Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\content_moderation\Entity\ContentModerationStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\videotrucades\Services\v1\JitsiManager;

/**
 * WanNotificationEvents.
 */
class VideotrucadesEvents implements EventSubscriberInterface {

  /**
   * Entity Manager Service.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;
  /**
   * NotificationsManager.
   *
   * @var \Drupal\wan_notifications\Services\v1\NotificationsManager
   */
  protected $JitsiManager;

  /**
   * Constructor.
   *
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   * @param \Drupal\videotrucades\Services\v1\JitsiManager $JitsiManager
   *   Manager class.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    AccountProxyInterface $current_user,
    JitsiManager $JitsiManager
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $current_user;
    $this->JitsiManager = $JitsiManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    // Set dependecy injection.
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('jitsi_manager')
    );
  }

  /**
   * Dispatch notification when node is inserted.
   *
   * If node is form specific type, is published and have the boolean activated
   * then call the FirebaseManager class.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event.
   */
  public function JitsiInsert(EntityInsertEvent $event)
  {
    $entity = $event->getEntity();
    // When instance is inserted
    //kint($entity);
    //die();
    if ($entity instanceof Node) {
      $entity_type = $entity->getEntityTypeId();
      if ($entity_type == 'node') {
        // Get Node ID, load entity and get bundle.
        $nid = $entity->id();
        $node_created = $this->entityTypeManager->getStorage($entity_type)->load($nid);
        $content_type = $node_created->bundle();
        if ($content_type == 'jitsi_instance') {
          $values = [
            'title' => $node_created->getTitle(),
            'subdomain' => $node_created->get('field_subdomini')->value,
            'description' => $node_created->get('field_description')->value,
            'size' => $node_created->get('field_user')->value
          ];
          $instance_id = $this->JitsiManager->createInstance($values);
          //if (empty($node_created->get('field_id_instancia')->value)) {
            //$node_created->set('field_id_instance',$instance_id);
            //$node_created->save();
          //}
	   // kint($instance_id);
        }
      }
    }
  }

  /**
   * Entity update.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event.
   */
  public function JitsiUpdate(EntityUpdateEvent $event)
  {
    $entity = $event->getEntity();
    // Engagement & High Impact Notification. When entity is updated.
    if ($entity instanceof Node) {
      $entity_type = $entity->getEntityTypeId();
      if ($entity_type == 'node') {
        // Get Node ID, load entity and get bundle.
        $nid = $entity->id();
        $node_updated = $this->entityTypeManager->getStorage($entity_type)->load($nid);
        $content_type = $node_updated->bundle();
        if ($content_type == 'jitsi_instance') {
		kint($node_updated);
            $this->JitsiManager->updateInstance(['ola']);
        }
      }
    }
  }

  /**
   * Entity update.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityDeleteEvent $event
   *   The event.
   */
  public function JitsiDelete(EntityDeleteEvent $event) {
    $entity = $event->getEntity();
    if ($entity instanceof Node) {
      $entity_type = $entity->getEntityTypeId();
      if ($entity_type == 'node') {
        // Get Node ID. Load entity. Get Node bundle.
        $content_type = $entity->bundle();
        if ($content_type == 'jitsi_instance') {
          $this->JitsiManager->deleteInstance(['ola']);
        }
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents()
  {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'JitsiInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'JitsiUpdate',
      HookEventDispatcherInterface::ENTITY_DELETE => 'JitsiDelete',
    ];
  }
}
